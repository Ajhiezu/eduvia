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
    'album_name',
    'title',
    'caption',
    'media_path',
    'media_type',
    'video_url',
    'alt_text',
    'is_published',
    'order_index',
])]
class SchoolGallery extends Model
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

    public function mediaUrl(): ?string
    {
        if (! $this->media_path || ! Storage::disk('public')->exists($this->media_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->media_path);
    }
}
