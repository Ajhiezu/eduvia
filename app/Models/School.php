<?php

namespace App\Models;

use Database\Factories\SchoolFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name',
    'slug',
    'short_name',
    'npsn',
    'educational_levels',
    'address',
    'city',
    'province',
    'postal_code',
    'phone',
    'email',
    'website',
    'logo_path',
    'is_active',
    'public_website_enabled',
])]
class School extends Model
{
    /** @use HasFactory<SchoolFactory> */
    use HasFactory;

    /**
     * Get attributes cast types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'educational_levels' => 'array',
            'is_active' => 'boolean',
            'public_website_enabled' => 'boolean',
        ];
    }

    /**
     * Get all academic years for this school.
     *
     * @return HasMany<AcademicYear, $this>
     */
    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
    }

    /**
     * Get active academic year for this school.
     *
     * @return HasOne<AcademicYear, $this>
     */
    public function activeAcademicYear(): HasOne
    {
        return $this->hasOne(AcademicYear::class)->where('is_active', true);
    }

    /**
     * Get all custom system settings for this school.
     *
     * @return HasMany<SystemSetting, $this>
     */
    public function settings(): HasMany
    {
        return $this->hasMany(SystemSetting::class);
    }

    /**
     * Get classrooms belonging to this school.
     *
     * @return HasMany<Classroom, $this>
     */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    /**
     * Get all staff profiles in this school.
     *
     * @return HasMany<StaffProfile, $this>
     */
    public function staffProfiles(): HasMany
    {
        return $this->hasMany(StaffProfile::class);
    }

    /**
     * Get all users belonging to this school.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get current default/active school instance.
     */
    public static function current(): ?self
    {
        return static::where('is_active', true)->first() ?? static::first();
    }

    /**
     * Get website settings for this school.
     *
     * @return HasOne<SchoolWebsiteSetting, $this>
     */
    public function websiteSetting(): HasOne
    {
        return $this->hasOne(SchoolWebsiteSetting::class);
    }

    /**
     * Get all posts (news, events, achievements) for this school.
     *
     * @return HasMany<SchoolPost, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(SchoolPost::class);
    }

    /**
     * Get published posts.
     *
     * @return HasMany<SchoolPost, $this>
     */
    public function publishedPosts(): HasMany
    {
        return $this->hasMany(SchoolPost::class)->where('is_published', true);
    }

    /**
     * Get extracurriculars for this school.
     *
     * @return HasMany<SchoolExtracurricular, $this>
     */
    public function extracurriculars(): HasMany
    {
        return $this->hasMany(SchoolExtracurricular::class);
    }

    /**
     * Get facilities and featured programs for this school.
     *
     * @return HasMany<SchoolFacility, $this>
     */
    public function facilities(): HasMany
    {
        return $this->hasMany(SchoolFacility::class);
    }

    /**
     * Get media galleries for this school.
     *
     * @return HasMany<SchoolGallery, $this>
     */
    public function galleries(): HasMany
    {
        return $this->hasMany(SchoolGallery::class);
    }

    /**
     * Get public URL for school logo.
     */
    public function logoUrl(): ?string
    {
        if (! $this->logo_path || ! Storage::disk('public')->exists($this->logo_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }
}
