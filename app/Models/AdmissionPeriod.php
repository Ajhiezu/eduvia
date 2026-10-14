<?php

namespace App\Models;

use Database\Factories\AdmissionPeriodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'school_id',
    'academic_year_id',
    'name',
    'code',
    'start_date',
    'end_date',
    'announcement_date',
    're_registration_start_date',
    're_registration_end_date',
    'quota',
    'status',
    'description',
    'requirements',
    'is_active',
])]
class AdmissionPeriod extends Model
{
    /** @use HasFactory<AdmissionPeriodFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'announcement_date' => 'date',
            're_registration_start_date' => 'date',
            're_registration_end_date' => 'date',
            'quota' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * School context.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Target academic year.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Tracks in this period.
     */
    public function tracks(): HasMany
    {
        return $this->hasMany(AdmissionTrack::class);
    }

    /**
     * Applicants in this period.
     */
    public function applicants(): HasMany
    {
        return $this->hasMany(AdmissionApplicant::class);
    }

    /**
     * Scope active periods.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope periods currently open for registration.
     */
    public function scopeOpen(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)
            ->where('status', 'open')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today);
    }

    /**
     * Check if currently open for public application.
     */
    public function isOpen(): bool
    {
        $today = now()->toDateString();

        return $this->is_active
            && $this->status === 'open'
            && $this->start_date?->toDateString() <= $today
            && $this->end_date?->toDateString() >= $today;
    }

    /**
     * Indonesian status label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft / Persiapan',
            'open' => 'Pendaftaran Dibuka',
            'closed' => 'Pendaftaran Ditutup',
            'completed' => 'Selesai / Diarsipkan',
            default => ucfirst($this->status),
        };
    }

    /**
     * Badge visual variant.
     */
    public function statusBadgeVariant(): string
    {
        return match ($this->status) {
            'draft' => 'neutral',
            'open' => 'success',
            'closed' => 'warning',
            'completed' => 'primary',
            default => 'neutral',
        };
    }
}
