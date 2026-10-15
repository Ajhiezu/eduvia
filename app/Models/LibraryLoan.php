<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'library_book_copy_id',
        'user_id',
        'loan_code',
        'borrow_date',
        'due_date',
        'return_date',
        'status',
        'fine_amount',
        'fine_paid',
        'notes',
        'issued_by',
        'received_by',
    ];

    protected function casts(): array
    {
        return [
            'borrow_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
            'fine_amount' => 'integer',
            'fine_paid' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function copy(): BelongsTo
    {
        return $this->belongsTo(LibraryBookCopy::class, 'library_book_copy_id');
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function isOverdue(): bool
    {
        if ($this->status === 'returned') {
            return false;
        }

        return $this->due_date->isPast();
    }

    public function calculateOverdueDays(): int
    {
        $targetDate = $this->return_date ?? Carbon::today();
        if ($targetDate->gt($this->due_date)) {
            return $this->due_date->diffInDays($targetDate);
        }

        return 0;
    }

    public function statusLabel(): string
    {
        if ($this->status === 'borrowed' && $this->isOverdue()) {
            return 'Terlambat';
        }

        return match ($this->status) {
            'borrowed' => 'Dipinjam',
            'returned' => 'Dikembalikan',
            'overdue' => 'Terlambat',
            'lost' => 'Hilang',
            default => ucfirst($this->status),
        };
    }

    public function statusVariant(): string
    {
        if ($this->status === 'borrowed' && $this->isOverdue()) {
            return 'danger';
        }

        return match ($this->status) {
            'borrowed' => 'info',
            'returned' => 'success',
            'overdue' => 'danger',
            'lost' => 'neutral',
            default => 'neutral',
        };
    }
}
