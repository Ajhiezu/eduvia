<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class StudentProfile extends Model
{
    use BelongsToSchool, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'school_id',
        'nis',
        'nisn',
        'gender',
        'birth_place',
        'birth_date',
        'religion',
        'address',
        'parent_name',
        'parent_phone',
        'parent_relationship',
        'student_status',
        'enrollment_date',
        'graduation_date',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
        'enrollment_date' => 'date',
        'graduation_date' => 'date',
    ];

    /**
     * User (Account) relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * School relationship.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Indonesian gender label.
     */
    public function genderLabel(): string
    {
        return match ($this->gender) {
            'male', 'L' => 'Laki-laki',
            'female', 'P' => 'Perempuan',
            default => '-',
        };
    }

    /**
     * Indonesian student status label.
     */
    public function statusLabel(): string
    {
        return match ($this->student_status) {
            'active' => 'Aktif',
            'transferred' => 'Pindah / Mutasi',
            'graduated' => 'Lulus',
            'dropped' => 'Dikeluarkan / Keluar',
            'inactive' => 'Non-Aktif',
            default => ucfirst($this->student_status),
        };
    }

    /**
     * Badge variant for status display.
     */
    public function statusBadgeVariant(): string
    {
        return match ($this->student_status) {
            'active' => 'success',
            'transferred' => 'warning',
            'graduated' => 'info',
            'dropped' => 'danger',
            default => 'neutral',
        };
    }

    /**
     * Safely change student status and record audit history.
     */
    public function changeStatus(string $newStatus, string $effectiveDate, ?string $reason = null, ?User $actor = null): void
    {
        if ($this->student_status === $newStatus) {
            return;
        }

        DB::transaction(function () use ($newStatus, $effectiveDate, $reason, $actor) {
            $previousStatus = $this->student_status;

            $updateData = ['student_status' => $newStatus];
            if ($newStatus === 'graduated') {
                $updateData['graduation_date'] = $effectiveDate;
            }

            $this->update($updateData);

            StudentStatusHistory::create([
                'student_id' => $this->user_id,
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'effective_date' => $effectiveDate,
                'reason' => $reason,
                'changed_by' => $actor?->id,
            ]);
        });
    }
}
