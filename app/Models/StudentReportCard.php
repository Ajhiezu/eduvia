<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Database\Factories\StudentReportCardFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentReportCard extends Model
{
    /** @use HasFactory<StudentReportCardFactory> */
    use BelongsToSchool, HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_REVISION_REQUESTED = 'revision_requested';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'student_id',
        'classroom_id',
        'academic_year_id',
        'semester_id',
        'homeroom_teacher_id',
        'status',
        'attendance_summary',
        'extracurricular_evaluations',
        'student_achievements',
        'homeroom_notes',
        'revision_notes',
        'approved_by',
        'approved_at',
        'published_by',
        'published_at',
        'snapshot',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attendance_summary' => 'array',
        'extracurricular_evaluations' => 'array',
        'student_achievements' => 'array',
        'snapshot' => 'array',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    /**
     * School relationship.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Student relationship.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Classroom relationship.
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
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
     * Homeroom teacher relationship.
     */
    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    /**
     * Approved by user relationship.
     */
    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Published by user relationship.
     */
    public function publishedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * Subject items in this report card.
     */
    public function items(): HasMany
    {
        return $this->hasMany(StudentReportCardItem::class)->orderBy('order_index')->orderBy('id');
    }

    /**
     * Human-readable Indonesian status label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draf Wali Kelas',
            self::STATUS_PENDING_REVIEW => 'Menunggu Verifikasi',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_PUBLISHED => 'Diterbitkan (Resmi)',
            self::STATUS_REVISION_REQUESTED => 'Perlu Revisi',
            default => ucfirst((string) $this->status),
        };
    }

    /**
     * Badge visual variant.
     */
    public function statusBadgeVariant(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'neutral',
            self::STATUS_PENDING_REVIEW => 'warning',
            self::STATUS_APPROVED => 'info',
            self::STATUS_PUBLISHED => 'success',
            self::STATUS_REVISION_REQUESTED => 'danger',
            default => 'neutral',
        };
    }

    /**
     * Check if report card is published.
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Check if report card is editable by homeroom teacher.
     */
    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REVISION_REQUESTED], true);
    }

    /**
     * Average semester score across all subjects.
     */
    public function calculateAverageScore(): float
    {
        if ($this->isPublished() && ! empty($this->snapshot['summary']['average_score'])) {
            return (float) $this->snapshot['summary']['average_score'];
        }

        $avg = $this->items()->avg('final_score');

        return $avg ? round((float) $avg, 2) : 0.0;
    }

    /**
     * Build an immutable snapshot of all report card data.
     *
     * @return array<string, mixed>
     */
    public function buildSnapshot(): array
    {
        $this->loadMissing([
            'school',
            'student.studentProfile',
            'classroom.gradeLevel',
            'academicYear',
            'semester',
            'homeroomTeacher.staffProfile',
            'items',
        ]);

        $student = $this->student;
        $studentProfile = $student?->studentProfile;
        $school = $this->school;
        $homeroom = $this->homeroomTeacher;
        $homeroomProfile = $homeroom?->staffProfile;

        $itemsData = $this->items->map(function (StudentReportCardItem $item): array {
            return [
                'subject_id' => $item->subject_id,
                'subject_name' => $item->subject_name,
                'subject_code' => $item->subject_code,
                'subject_group' => $item->subject_group,
                'teacher_name' => $item->teacher_name,
                'passing_grade' => (float) $item->passing_grade,
                'final_score' => (float) $item->final_score,
                'predicate' => $item->predicate,
                'is_passed' => (bool) $item->is_passed,
                'achievement_description' => $item->achievement_description,
                'order_index' => $item->order_index,
            ];
        })->toArray();

        $avgScore = count($itemsData) > 0
            ? round(array_sum(array_column($itemsData, 'final_score')) / count($itemsData), 2)
            : 0.0;

        return [
            'version' => '1.0',
            'generated_at' => now()->toISOString(),
            'school' => [
                'id' => $school?->id,
                'name' => $school?->name ?? 'Sekolah',
                'npsn' => $school?->npsn ?? '-',
                'address' => $school?->address ?? '-',
                'city' => $school?->city ?? 'Indonesia',
                'phone' => $school?->phone ?? '-',
                'email' => $school?->email ?? '-',
                'website' => $school?->website ?? '-',
                'principal_name' => $school?->principal_name ?? 'Kepala Sekolah',
                'principal_nip' => $school?->principal_nip ?? '-',
                'logo_url' => $school?->logoUrl(),
            ],
            'student' => [
                'id' => $student?->id,
                'name' => $student?->name ?? 'Siswa',
                'nis' => $studentProfile?->nis ?? '-',
                'nisn' => $studentProfile?->nisn ?? '-',
                'gender' => $studentProfile?->genderLabel() ?? '-',
            ],
            'academic' => [
                'academic_year_name' => $this->academicYear?->name ?? '-',
                'semester_name' => $this->semester?->name ?? '-',
                'classroom_name' => $this->classroom?->name ?? '-',
                'grade_level' => $this->classroom?->gradeLevel?->name ?? '-',
            ],
            'homeroom_teacher' => [
                'id' => $homeroom?->id,
                'name' => $homeroom?->name ?? 'Wali Kelas',
                'nip' => $homeroomProfile?->nip ?? '-',
            ],
            'items' => $itemsData,
            'summary' => [
                'total_subjects' => count($itemsData),
                'average_score' => $avgScore,
                'passed_count' => count(array_filter($itemsData, fn ($i) => $i['is_passed'])),
                'failed_count' => count(array_filter($itemsData, fn ($i) => ! $i['is_passed'])),
            ],
            'attendance' => $this->attendance_summary ?? [
                'present' => 0,
                'sick' => 0,
                'permit' => 0,
                'absent' => 0,
                'late' => 0,
            ],
            'extracurriculars' => $this->extracurricular_evaluations ?? [],
            'achievements' => $this->student_achievements ?? [],
            'homeroom_notes' => $this->homeroom_notes,
            'published_info' => [
                'published_at' => now()->translatedFormat('d F Y'),
                'published_by' => auth()->user()?->name ?? 'Administrator',
            ],
        ];
    }

    /**
     * Scope for published report cards.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope for student.
     */
    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }
}
