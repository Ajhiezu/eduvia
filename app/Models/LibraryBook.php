<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'title',
        'author',
        'publisher',
        'publication_year',
        'isbn',
        'call_number',
        'category',
        'language',
        'location_shelf',
        'description',
        'cover_image_path',
        'digital_file_path',
        'total_copies',
        'available_copies',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'publication_year' => 'integer',
            'total_copies' => 'integer',
            'available_copies' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function copies(): HasMany
    {
        return $this->hasMany(LibraryBookCopy::class);
    }

    public function recalculateCopies(): void
    {
        $total = $this->copies()->count();
        $available = $this->copies()->where('status', 'available')->count();

        $this->update([
            'total_copies' => $total,
            'available_copies' => $available,
        ]);
    }
}
