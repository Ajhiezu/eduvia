<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Database\Factories\SchoolAnnouncementFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SchoolAnnouncement extends Model
{
    /** @use HasFactory<SchoolAnnouncementFactory> */
    use BelongsToSchool, HasFactory;

    public const CATEGORY_GENERAL = 'general';

    public const CATEGORY_ACADEMIC = 'academic';

    public const CATEGORY_EVENT = 'event';

    public const CATEGORY_HOLIDAY = 'holiday';

    public const CATEGORY_URGENT = 'urgent';

    public const CATEGORIES = [
        self::CATEGORY_GENERAL => 'Umum',
        self::CATEGORY_ACADEMIC => 'Akademik & Ujian',
        self::CATEGORY_EVENT => 'Kegiatan & Acara',
        self::CATEGORY_HOLIDAY => 'Hari Libur & Kalender',
        self::CATEGORY_URGENT => 'Penting & Mendesak',
    ];

    public const AUDIENCE_ALL = 'all';

    public const AUDIENCE_PARENTS = 'parents';

    public const AUDIENCE_STUDENTS = 'students';

    public const AUDIENCE_TEACHERS = 'teachers';

    public const AUDIENCES = [
        self::AUDIENCE_ALL => 'Semua Pengguna Sekolah',
        self::AUDIENCE_PARENTS => 'Orang Tua / Wali Murid',
        self::AUDIENCE_STUDENTS => 'Siswa / Peserta Didik',
        self::AUDIENCE_TEACHERS => 'Guru & Staf Pendidik',
    ];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'created_by',
        'title',
        'slug',
        'content',
        'category',
        'target_audience',
        'grade_level_id',
        'classroom_id',
        'status',
        'is_pinned',
        'published_at',
        'expires_at',
        'attachment_path',
        'attachment_name',
        'view_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_pinned' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'view_count' => 'integer',
    ];

    /**
     * School relationship.
     *
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Author relationship.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Target Grade Level relationship.
     *
     * @return BelongsTo<GradeLevel, $this>
     */
    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    /**
     * Target Classroom relationship.
     *
     * @return BelongsTo<Classroom, $this>
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Scope published announcements.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->where(function (Builder $sub) {
                $sub->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where(function (Builder $sub) {
                $sub->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope announcements visible to parents of a student.
     */
    public function scopeForParentOfStudent(Builder $query, int $schoolId, ?int $classroomId = null, ?int $gradeLevelId = null): Builder
    {
        return $query->where('school_id', $schoolId)
            ->published()
            ->where(function (Builder $sub) {
                $sub->whereIn('target_audience', [self::AUDIENCE_ALL, self::AUDIENCE_PARENTS]);
            })
            ->where(function (Builder $sub) use ($classroomId, $gradeLevelId) {
                // If neither grade nor classroom specified, matches general
                $sub->where(function (Builder $nested) {
                    $nested->whereNull('classroom_id')
                        ->whereNull('grade_level_id');
                });

                if ($classroomId) {
                    $sub->orWhere('classroom_id', $classroomId);
                }

                if ($gradeLevelId) {
                    $sub->orWhere('grade_level_id', $gradeLevelId);
                }
            });
    }

    /**
     * Category label.
     */
    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    /**
     * Category badge class.
     */
    public function categoryBadgeClasses(): string
    {
        return match ($this->category) {
            self::CATEGORY_URGENT => 'bg-rose-50 text-[#E11D2E] border-rose-200',
            self::CATEGORY_ACADEMIC => 'bg-blue-50 text-blue-800 border-blue-200',
            self::CATEGORY_EVENT => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            self::CATEGORY_HOLIDAY => 'bg-amber-50 text-amber-800 border-amber-200',
            default => 'bg-zinc-100 text-zinc-700 border-zinc-200',
        };
    }

    /**
     * Target audience label.
     */
    public function targetAudienceLabel(): string
    {
        return self::AUDIENCES[$this->target_audience] ?? ucfirst($this->target_audience);
    }

    /**
     * Attachment URL.
     */
    public function attachmentUrl(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        return Storage::url($this->attachment_path);
    }

    /**
     * Boot helper to generate slug automatically.
     */
    protected static function booted(): void
    {
        static::creating(function (SchoolAnnouncement $announcement) {
            if (empty($announcement->slug)) {
                $announcement->slug = Str::slug($announcement->title).'-'.Str::lower(Str::random(6));
            }
        });
    }
}
