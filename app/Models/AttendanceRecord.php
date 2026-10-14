<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceRecord extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'attendance_session_id',
        'student_id',
        'status',
        'notes',
    ];

    /**
     * Session relationship.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    /**
     * Student relationship.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Audit logs relationship.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AttendanceAuditLog::class);
    }

    /**
     * Indonesian status label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'present' => 'Hadir',
            'sick' => 'Sakit',
            'permit' => 'Izin',
            'absent' => 'Alpa',
            'late' => 'Terlambat',
            default => ucfirst($this->status),
        };
    }

    /**
     * Badge variant for status display.
     */
    public function statusBadgeVariant(): string
    {
        return match ($this->status) {
            'present' => 'success',
            'late' => 'warning',
            'permit' => 'info',
            'sick' => 'brand',
            'absent' => 'danger',
            default => 'neutral',
        };
    }
}
