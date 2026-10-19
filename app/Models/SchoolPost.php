<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'school_id',
    'type',
    'title',
    'slug',
    'excerpt',
    'content',
    'cover_image_path',
    'category',
    'event_date',
    'event_end_date',
    'event_location',
    'competition_name',
    'competition_level',
    'awardee_name',
    'is_published',
    'published_at',
    'order_index',
    'view_count',
])]
class SchoolPost extends Model
{
    use BelongsToSchool;
    use HasFactory;

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'event_end_date' => 'datetime',
            'published_at' => 'datetime',
            'is_published' => 'boolean',
            'order_index' => 'integer',
            'view_count' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Scope to published posts.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope to specific type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function coverImageUrl(): ?string
    {
        if (! $this->cover_image_path || ! Storage::disk('public')->exists($this->cover_image_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->cover_image_path);
    }

    public function isEventPast(): bool
    {
        if (! $this->event_date) {
            return false;
        }

        return ($this->event_end_date ?? $this->event_date)->isPast();
    }
}
