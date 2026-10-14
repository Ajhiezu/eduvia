<?php

namespace App\Models;

use App\Enums\StaffPosition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'school_id',
        'nip',
        'nuptk',
        'nik',
        'gender',
        'birth_place',
        'birth_date',
        'position',
        'work_unit',
        'employment_status',
        'join_date',
        'last_education',
        'address',
        'phone',
        'status',
        'invitation_token',
        'invitation_sent_at',
        'activated_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
        'join_date' => 'date',
        'invitation_sent_at' => 'datetime',
        'activated_at' => 'datetime',
        'position' => StaffPosition::class,
    ];

    /**
     * User (Account) relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * School relationship.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Indonesian gender label.
     */
    public function genderLabel(): string
    {
        return match ($this->gender) {
            'male', 'L' => 'Laki-laki',
            'female', 'P' => 'Perempuan',
            default => '-',
        };
    }

    /**
     * Indonesian status label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'active' => 'Aktif',
            'inactive' => 'Non-Aktif',
            'on_leave' => 'Cuti',
            'resigned' => 'Berhenti / Mutasi',
            default => ucfirst($this->status),
        };
    }

    /**
     * Badge variant for status display.
     */
    public function statusBadgeVariant(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'inactive' => 'danger',
            'on_leave' => 'warning',
            'resigned' => 'neutral',
            default => 'neutral',
        };
    }

    /**
     * Indonesian employment status label.
     */
    public function employmentStatusLabel(): string
    {
        return match (strtolower($this->employment_status ?? '')) {
            'pns' => 'Pegawai Negeri Sipil (PNS)',
            'p3k' => 'PPPK (P3K)',
            'gty' => 'Guru Tetap Yayasan (GTY)',
            'gtt' => 'Guru Tidak Tetap (GTT)',
            'pty' => 'Pegawai Tetap Yayasan (PTY)',
            'ptt' => 'Pegawai Tidak Tetap (PTT)',
            'honor' => 'Tenaga Honorer',
            default => strtoupper($this->employment_status ?? '-'),
        };
    }

    /**
     * Check if staff has pending invitation.
     */
    public function hasPendingInvitation(): bool
    {
        return ! empty($this->invitation_token) && is_null($this->activated_at);
    }
}
