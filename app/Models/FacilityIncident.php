<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_room_id',
        'reported_by',
        'title',
        'description',
        'priority',
        'status',
        'resolution_notes',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
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

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function priorityLabel(): string
    {
        return match ($this->priority) {
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            'urgent' => 'Darurat',
            default => ucfirst($this->priority),
        };
    }

    public function priorityVariant(): string
    {
        return match ($this->priority) {
            'low' => 'neutral',
            'medium' => 'info',
            'high' => 'warning',
            'urgent' => 'danger',
            default => 'neutral',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'reported' => 'Dilaporkan',
            'in_progress' => 'Dalam Penanganan',
            'resolved' => 'Terselesaikan',
            'closed' => 'Ditutup',
            default => ucfirst($this->status),
        };
    }

    public function statusVariant(): string
    {
        return match ($this->status) {
            'reported' => 'warning',
            'in_progress' => 'info',
            'resolved' => 'success',
            'closed' => 'neutral',
            default => 'neutral',
        };
    }
}
