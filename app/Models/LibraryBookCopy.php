<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LibraryBookCopy extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'library_book_id',
        'barcode',
        'copy_number',
        'status',
        'notes',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(LibraryBook::class, 'library_book_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(LibraryLoan::class);
    }

    public function activeLoan(): HasOne
    {
        return $this->hasOne(LibraryLoan::class)->where('status', 'borrowed');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'available' => 'Tersedia',
            'borrowed' => 'Dipinjam',
            'damaged' => 'Rusak',
            'lost' => 'Hilang',
            'maintenance' => 'Perawatan',
            default => ucfirst($this->status),
        };
    }
}
