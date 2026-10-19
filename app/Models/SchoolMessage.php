<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SchoolMessage extends Model
{
    use BelongsToSchool;
    use HasFactory;

    public const CATEGORIES = [
        'informasi_sekolah' => 'Informasi Sekolah',
        'ppdb' => 'Pendaftaran Siswa Baru (PPDB)',
        'akademik' => 'Akademik & Kurikulum',
        'ekstrakurikuler' => 'Ekstrakurikuler & Kegiatan',
        'kerjasama' => 'Kerja Sama & Kemitraan',
        'kritik_saran' => 'Kritik & Saran',
        'lainnya' => 'Lainnya',
    ];

    public const STATUS_NEW = 'new';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_NEW => 'Baru',
        self::STATUS_IN_PROGRESS => 'Sedang Ditindaklanjuti',
        self::STATUS_RESOLVED => 'Selesai',
        self::STATUS_ARCHIVED => 'Diarsipkan',
    ];

    protected $fillable = [
        'school_id',
        'reference_number',
        'name',
        'email',
        'phone',
        'category',
        'subject',
        'message',
        'status',
        'is_read',
        'read_at',
        'internal_notes',
        'resolved_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

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
     * Human readable category name.
     */
    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    /**
     * Human readable status name.
     */
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Tailwind classes for status badge.
     */
    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'bg-rose-50 text-[#E11D2E] border-rose-200',
            self::STATUS_IN_PROGRESS => 'bg-amber-50 text-amber-800 border-amber-200',
            self::STATUS_RESOLVED => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            self::STATUS_ARCHIVED => 'bg-zinc-100 text-zinc-600 border-zinc-200',
            default => 'bg-zinc-100 text-zinc-700 border-zinc-200',
        };
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Mark message as resolved.
     */
    public function markAsResolved(): void
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Generate an unambiguous, unique reference number.
     * Format: MSG-YYYYMMDD-XXXXXX
     */
    public static function generateReferenceNumber(): string
    {
        do {
            $code = 'MSG-'.date('Ymd').'-'.strtoupper(Str::random(6));
        } while (static::where('reference_number', $code)->exists());

        return $code;
    }

    /**
     * Scope query by school ID.
     */
    public function scopeForSchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope unread messages.
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }
}
