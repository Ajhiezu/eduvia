<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Database\Factories\AssessmentWeightConfigurationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentWeightConfiguration extends Model
{
    use BelongsToSchool;

    /** @use HasFactory<AssessmentWeightConfigurationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'semester_id',
        'subject_id',
        'name',
        'assignment_weight',
        'quiz_weight',
        'midterm_weight',
        'final_weight',
        'passing_grade',
        'grade_scale',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'assignment_weight' => 'decimal:2',
        'quiz_weight' => 'decimal:2',
        'midterm_weight' => 'decimal:2',
        'final_weight' => 'decimal:2',
        'passing_grade' => 'decimal:2',
        'grade_scale' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Default grade scale intervals if none customized.
     *
     * @return array<string, array{min: float, max: float, label: string}>
     */
    public static function defaultGradeScale(): array
    {
        return [
            'A' => ['min' => 90.0, 'max' => 100.0, 'label' => 'Sangat Baik'],
            'B' => ['min' => 80.0, 'max' => 89.99, 'label' => 'Baik'],
            'C' => ['min' => 70.0, 'max' => 79.99, 'label' => 'Cukup'],
            'D' => ['min' => 0.0, 'max' => 69.99, 'label' => 'Kurang / Perlu Bimbingan'],
        ];
    }

    /**
     * School relationship.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Academic Year relationship.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Semester relationship.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Subject relationship.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Creator relationship.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Subject gradebooks using this configuration.
     */
    public function subjectGradebooks(): HasMany
    {
        return $this->hasMany(SubjectGradebook::class);
    }

    /**
     * Total configured weights sum.
     */
    public function totalWeight(): float
    {
        return (float) ($this->assignment_weight + $this->quiz_weight + $this->midterm_weight + $this->final_weight);
    }

    /**
     * Validate if weights equal 100%.
     */
    public function isValidWeightSum(): bool
    {
        return abs($this->totalWeight() - 100.00) < 0.001;
    }

    /**
     * Scope for active configurations.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Resolve effective configuration for a given context.
     */
    public static function resolveFor(int $schoolId, ?int $academicYearId = null, ?int $semesterId = null, ?int $subjectId = null): ?self
    {
        // 1. Try exact match (school + year + semester + subject)
        if ($subjectId && $academicYearId && $semesterId) {
            $config = static::where('school_id', $schoolId)
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->where('subject_id', $subjectId)
                ->where('is_active', true)
                ->first();
            if ($config) {
                return $config;
            }
        }

        // 2. Try match for year + semester (generic subject)
        if ($academicYearId && $semesterId) {
            $config = static::where('school_id', $schoolId)
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->whereNull('subject_id')
                ->where('is_active', true)
                ->first();
            if ($config) {
                return $config;
            }
        }

        // 3. Fallback to school default
        return static::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->first();
    }
}
