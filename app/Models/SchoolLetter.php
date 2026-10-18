<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolLetter extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = [
        'school_id',
        'type',
        'reference_number',
        'letter_date',
        'received_or_sent_date',
        'sender_or_recipient',
        'subject',
        'category',
        'status',
        'action_deadline',
        'file_path',
        'file_original_name',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'letter_date' => 'date',
            'received_or_sent_date' => 'date',
            'action_deadline' => 'date',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'incoming' => 'Surat Masuk',
            'outgoing' => 'Surat Keluar',
            default => ucfirst($this->type),
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'sent' => 'Terkirim',
            'received' => 'Diterima',
            'action_needed' => 'Perlu Tindak Lanjut',
            'archived' => 'Diarsipkan',
            default => ucfirst($this->status),
        };
    }

    public function statusVariant(): string
    {
        return match ($this->status) {
            'draft' => 'neutral',
            'sent' => 'info',
            'received' => 'success',
            'action_needed' => 'warning',
            'archived' => 'neutral',
            default => 'neutral',
        };
    }
}
