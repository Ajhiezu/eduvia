<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'school_id',
    'name',
    'slug',
    'category',
    'description',
    'coach_name',
    'schedule_info',
    'location',
    'requirements',
    'cover_image_path',
    'is_published',
    'order_index',
])]
class SchoolExtracurricular extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'order_index' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function coverImageUrl(): ?string
    {
        if (! $this->cover_image_path || ! Storage::disk('public')->exists($this->cover_image_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->cover_image_path);
    }
}
