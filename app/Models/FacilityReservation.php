<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_room_id',
        'user_id',
        'reservation_date',
        'start_time',
        'end_time',
        'purpose',
        'expected_participants',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',
            'expected_participants' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(SchoolRoom::class, 'school_room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            'completed' => 'Selesai',
            default => ucfirst($this->status),
        };
    }

    public function statusVariant(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'neutral',
            'completed' => 'info',
            default => 'neutral',
        };
    }

    /**
     * Check if a time slot has conflicts based on:
     * existing_start < requested_end AND existing_end > requested_start
     */
    public static function hasConflict(int $schoolRoomId, string $date, string $startTime, string $endTime, ?int $ignoreId = null): bool
    {
        return static::where('school_room_id', $schoolRoomId)
            ->where('reservation_date', $date)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->exists();
    }
}
