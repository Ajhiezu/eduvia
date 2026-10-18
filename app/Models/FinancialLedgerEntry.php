<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Database\Factories\FinancialLedgerEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'school_id',
    'entry_number',
    'entry_type',
    'account_code',
    'account_name',
    'amount',
    'reference_type',
    'reference_id',
    'description',
    'posted_at',
    'posted_by',
])]
class FinancialLedgerEntry extends Model
{
    /** @use HasFactory<FinancialLedgerEntryFactory> */
    use BelongsToSchool, HasFactory;

    public const TYPE_DEBIT = 'debit';

    public const TYPE_CREDIT = 'credit';

    // Standard School Chart of Accounts (COA)
    public const ACC_CASH_BANK = '1101'; // Kas & Bank (Midtrans Settlement / Kasir)

    public const ACC_TUITION_RECEIVABLE = '1103'; // Piutang SPP / Biaya Sekolah

    public const ACC_TUITION_REVENUE = '4101'; // Pendapatan SPP / Biaya Pendidikan

    public const ACC_UNEARNED_REVENUE = '2101'; // Pendapatan Diterima di Muka

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'posted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Enforce append-only integrity: prohibit in-place updates or deletions
        static::updating(function () {
            throw new \LogicException('Financial ledger entries are strictly immutable (append-only). Use an offsetting journal entry to make corrections.');
        });

        static::deleting(function () {
            throw new \LogicException('Financial ledger entries cannot be deleted. Use a reversal entry for audit compliance.');
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function isDebit(): bool
    {
        return $this->entry_type === self::TYPE_DEBIT;
    }

    public function isCredit(): bool
    {
        return $this->entry_type === self::TYPE_CREDIT;
    }
}
