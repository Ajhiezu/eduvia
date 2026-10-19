<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentLeaveRequest extends Model
{
    use BelongsToSchool, HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const TYPE_SICK = 'sick';

    public const TYPE_PERMIT = 'permit';

    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'school_id',
        'student_id',
        'parent_id',
        'classroom_id',
        'leave_type',
        'start_date',
        'end_date',
        'reason',
        'attachment_path',
        'attachment_name',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function typeLabel(): string
    {
        return match ($this->leave_type) {
            self::TYPE_SICK => 'Sakit (Surat Dokter / Keterangan)',
            self::TYPE_PERMIT => 'Izin Kepentingan Keluarga',
            default => 'Lainnya',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu Persetujuan Wali Kelas',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            default => ucfirst($this->status),
        };
    }
}
