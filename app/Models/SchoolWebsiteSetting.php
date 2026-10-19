<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'school_id',
    'hero_headline',
    'hero_subheadline',
    'hero_cta_text',
    'hero_cta_url',
    'hero_image_path',
    'hero_video_url',
    'welcome_message',
    'principal_name',
    'principal_title',
    'principal_photo_path',
    'vision',
    'mission',
    'history',
    'accreditation_grade',
    'accreditation_no',
    'maps_embed_url',
    'social_links',
    'meta_description',
    'meta_keywords',
    'theme_color',
    'is_published',
])]
class SchoolWebsiteSetting extends Model
{
    use BelongsToSchool;
    use HasFactory;

    protected function casts(): array
    {
        return [
            'mission' => 'array',
            'social_links' => 'array',
            'is_published' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function heroImageUrl(): ?string
    {
        if (! $this->hero_image_path || ! Storage::disk('public')->exists($this->hero_image_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->hero_image_path);
    }

    public function principalPhotoUrl(): ?string
    {
        if (! $this->principal_photo_path || ! Storage::disk('public')->exists($this->principal_photo_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->principal_photo_path);
    }
}
