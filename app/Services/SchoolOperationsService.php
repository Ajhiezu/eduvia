<?php

namespace App\Services;

use App\Models\AssetLog;
use App\Models\ConsumableItem;
use App\Models\ConsumableStockLog;
use App\Models\FacilityReservation;
use App\Models\InventoryCategory;
use App\Models\LibraryBook;
use App\Models\LibraryBookCopy;
use App\Models\LibraryLoan;
use App\Models\School;
use App\Models\SchoolAsset;
use App\Models\SchoolLetter;
use App\Models\SchoolRoom;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SchoolOperationsService
{
    /**
     * Generate unique loan code (e.g. LOAN/202610/0001)
     */
    public function generateLoanCode(School $school): string
    {
        $prefix = 'LOAN/'.date('Ym').'/';

        $lastLoan = LibraryLoan::where('school_id', $school->id)
            ->where('loan_code', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSequence = 1;
        if ($lastLoan) {
            $parts = explode('/', $lastLoan->loan_code);
            $lastSeq = (int) end($parts);
            $nextSequence = $lastSeq + 1;
        }

        return $prefix.str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique copy barcode (e.g. BOK-0001-01)
     */
    public function generateCopyBarcode(LibraryBook $book, int $copyNumber): string
    {
        return 'BOK-'.str_pad((string) $book->id, 4, '0', STR_PAD_LEFT).'-'.str_pad((string) $copyNumber, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique asset code (e.g. AST/2026/0001)
     */
    public function generateAssetCode(School $school, ?InventoryCategory $category = null): string
    {
        $catCode = $category?->code ?? 'AST';
        $prefix = "{$catCode}/".date('Y').'/';

        $lastAsset = SchoolAsset::where('school_id', $school->id)
            ->where('asset_code', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSequence = 1;
        if ($lastAsset) {
            $parts = explode('/', $lastAsset->asset_code);
            $lastSeq = (int) end($parts);
            $nextSequence = $lastSeq + 1;
        }

        return $prefix.str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique official letter reference number (e.g. 421/001/SMA-EDU/X/2026)
     */
    public function generateLetterReference(School $school, string $type, string $category): string
    {
        $year = date('Y');
        $monthRoman = match ((int) date('n')) {
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        };

        $schoolSlug = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $school->name), 0, 8));
        $prefixCategory = match ($category) {
            'Dinas' => '421',
            'Surat Tugas' => '800',
            'Keterangan' => '422',
            'Undangan' => '005',
            default => '400',
        };

        $countThisYear = SchoolLetter::where('school_id', $school->id)
            ->whereYear('letter_date', $year)
            ->where('type', $type)
            ->count() + 1;

        $sequence = str_pad((string) $countThisYear, 3, '0', STR_PAD_LEFT);

        return "{$prefixCategory}/{$sequence}/{$schoolSlug}/{$monthRoman}/{$year}";
    }

    // =========================================================================
    // LIBRARY OPERATIONS
    // =========================================================================

    /**
     * Borrow a book copy.
     */
    public function borrowBook(
        User $borrower,
        LibraryBookCopy $copy,
        string $dueDate,
        ?string $notes,
        User $staff
    ): LibraryLoan {
        return DB::transaction(function () use ($borrower, $copy, $dueDate, $notes, $staff) {
            $lockedCopy = LibraryBookCopy::where('id', $copy->id)->lockForUpdate()->firstOrFail();

            if ($lockedCopy->status !== 'available') {
                throw ValidationException::withMessages([
                    'copy_id' => 'Eksemplar buku ini tidak sedang tersedia untuk dipinjam (Status: '.$lockedCopy->statusLabel().').',
                ]);
            }

            $school = $lockedCopy->school;
            $loanCode = $this->generateLoanCode($school);

            $loan = LibraryLoan::create([
                'school_id' => $school->id,
                'library_book_copy_id' => $lockedCopy->id,
                'user_id' => $borrower->id,
                'loan_code' => $loanCode,
                'borrow_date' => Carbon::today(),
                'due_date' => $dueDate,
                'status' => 'borrowed',
                'fine_amount' => 0,
                'fine_paid' => false,
                'notes' => $notes,
                'issued_by' => $staff->id,
            ]);

            $lockedCopy->update(['status' => 'borrowed']);
            $lockedCopy->book->recalculateCopies();

            return $loan;
        });
    }

    /**
     * Return a borrowed book copy.
     */
    public function returnBook(LibraryLoan $loan, ?string $notes, User $staff): LibraryLoan
    {
        return DB::transaction(function () use ($loan, $notes, $staff) {
            $lockedLoan = LibraryLoan::where('id', $loan->id)->lockForUpdate()->firstOrFail();

            if ($lockedLoan->status === 'returned') {
                throw ValidationException::withMessages([
                    'loan' => 'Peminjaman buku ini sudah tercatat dikembalikan sebelumnya.',
                ]);
            }

            $returnDate = Carbon::today();
            $overdueDays = $lockedLoan->calculateOverdueDays();
            // Default policy: Rp 1.000 per day late if overdue
            $fineAmount = $overdueDays > 0 ? $overdueDays * 1000 : 0;

            $lockedLoan->update([
                'status' => 'returned',
                'return_date' => $returnDate,
                'fine_amount' => $fineAmount,
                'received_by' => $staff->id,
                'notes' => $notes ? ($lockedLoan->notes ? $lockedLoan->notes."\n".$notes : $notes) : $lockedLoan->notes,
            ]);

            $copy = LibraryBookCopy::where('id', $lockedLoan->library_book_copy_id)->lockForUpdate()->firstOrFail();
            $copy->update(['status' => 'available']);
            $copy->book->recalculateCopies();

            return $lockedLoan;
        });
    }

    // =========================================================================
    // FACILITY & LABORATORY OPERATIONS
    // =========================================================================

    /**
     * Reserve a facility with strict interval overlap conflict detection.
     */
    public function reserveFacility(
        School $school,
        User $user,
        int $roomId,
        string $date,
        string $startTime,
        string $endTime,
        string $purpose,
        int $participants,
        ?string $notes = null
    ): FacilityReservation {
        $room = SchoolRoom::where('school_id', $school->id)->findOrFail($roomId);

        if (! $room->is_active) {
            throw ValidationException::withMessages([
                'school_room_id' => 'Fasilitas atau ruangan ini sedang tidak aktif / tidak dapat dipinjam.',
            ]);
        }

        if (FacilityReservation::hasConflict($roomId, $date, $startTime, $endTime)) {
            throw ValidationException::withMessages([
                'start_time' => 'Jadwal bentrok: Ruangan telah terpesan pada rentang waktu tersebut.',
            ]);
        }

        return FacilityReservation::create([
            'school_id' => $school->id,
            'school_room_id' => $room->id,
            'user_id' => $user->id,
            'reservation_date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'purpose' => $purpose,
            'expected_participants' => $participants,
            'status' => 'pending',
            'notes' => $notes,
        ]);
    }

    /**
     * Approve reservation.
     */
    public function approveReservation(FacilityReservation $reservation, User $approver): FacilityReservation
    {
        return DB::transaction(function () use ($reservation, $approver) {
            $locked = FacilityReservation::where('id', $reservation->id)->lockForUpdate()->firstOrFail();

            // Re-check conflict before approving
            if (FacilityReservation::hasConflict(
                $locked->school_room_id,
                $locked->reservation_date->toDateString(),
                $locked->start_time,
                $locked->end_time,
                $locked->id
            )) {
                throw ValidationException::withMessages([
                    'conflict' => 'Tidak dapat menyetujui: Terjadi bentrok dengan reservasi lain yang sudah disetujui.',
                ]);
            }

            $locked->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            return $locked;
        });
    }

    /**
     * Reject reservation.
     */
    public function rejectReservation(FacilityReservation $reservation, string $reason, User $approver): FacilityReservation
    {
        $reservation->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        return $reservation;
    }

    /**
     * Cancel reservation.
     */
    public function cancelReservation(FacilityReservation $reservation, User $user): FacilityReservation
    {
        $reservation->update([
            'status' => 'cancelled',
            'notes' => ($reservation->notes ? $reservation->notes."\n" : '')."Dibatalkan oleh {$user->name} pada ".now()->toDateTimeString(),
        ]);

        return $reservation;
    }

    // =========================================================================
    // ASSET & INVENTORY OPERATIONS
    // =========================================================================

    /**
     * Transfer asset to another room/location.
     */
    public function transferAsset(SchoolAsset $asset, string $newLocation, ?string $notes, User $performer): SchoolAsset
    {
        return DB::transaction(function () use ($asset, $newLocation, $notes, $performer) {
            $oldLocation = $asset->room_location;

            $asset->update([
                'room_location' => $newLocation,
            ]);

            AssetLog::create([
                'school_id' => $asset->school_id,
                'school_asset_id' => $asset->id,
                'action' => 'transferred',
                'from_location' => $oldLocation,
                'to_location' => $newLocation,
                'notes' => $notes ?? "Mutasi ruangan dari {$oldLocation} ke {$newLocation}",
                'performed_by' => $performer->id,
            ]);

            return $asset;
        });
    }

    /**
     * Update asset condition.
     */
    public function updateAssetCondition(SchoolAsset $asset, string $newCondition, ?string $notes, User $performer): SchoolAsset
    {
        return DB::transaction(function () use ($asset, $newCondition, $notes, $performer) {
            $oldCondition = $asset->condition;

            $asset->update([
                'condition' => $newCondition,
                'status' => in_array($newCondition, ['heavy_damage', 'disposed']) ? 'disposed' : $asset->status,
            ]);

            AssetLog::create([
                'school_id' => $asset->school_id,
                'school_asset_id' => $asset->id,
                'action' => 'condition_change',
                'from_condition' => $oldCondition,
                'to_condition' => $newCondition,
                'notes' => $notes ?? "Perubahan kondisi aset dari {$oldCondition} ke {$newCondition}",
                'performed_by' => $performer->id,
            ]);

            return $asset;
        });
    }

    /**
     * Adjust consumable stock (in/out/adjustment).
     */
    public function adjustConsumableStock(
        ConsumableItem $item,
        string $type,
        int $quantity,
        ?string $purpose,
        User $performer
    ): ConsumableStockLog {
        return DB::transaction(function () use ($item, $type, $quantity, $purpose, $performer) {
            $lockedItem = ConsumableItem::where('id', $item->id)->lockForUpdate()->firstOrFail();
            $prevStock = $lockedItem->current_stock;

            $newStock = match ($type) {
                'in' => $prevStock + $quantity,
                'out' => max(0, $prevStock - $quantity),
                'adjustment' => $quantity,
                default => $prevStock,
            };

            $lockedItem->update(['current_stock' => $newStock]);

            return ConsumableStockLog::create([
                'school_id' => $lockedItem->school_id,
                'consumable_item_id' => $lockedItem->id,
                'type' => $type,
                'quantity' => $quantity,
                'previous_stock' => $prevStock,
                'current_stock' => $newStock,
                'purpose' => $purpose,
                'performed_by' => $performer->id,
            ]);
        });
    }
}
