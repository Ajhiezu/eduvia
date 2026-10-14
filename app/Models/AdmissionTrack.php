<?php

namespace App\Models;

use Database\Factories\AdmissionTrackFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'admission_period_id',
    'name',
    'code',
    'quota',
    'description',
    'is_active',
])]
class AdmissionTrack extends Model
{
    /** @use HasFactory<AdmissionTrackFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quota' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Associated admission period.
     */
    public function admissionPeriod(): BelongsTo
    {
        return $this->belongsTo(AdmissionPeriod::class);
    }

    /**
     * Applicants under this track.
     */
    public function applicants(): HasMany
    {
        return $this->hasMany(AdmissionApplicant::class);
    }

    /**
     * Scope active tracks.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
