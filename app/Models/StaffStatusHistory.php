<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffStatusHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'changed_by',
        'action',
        'previous_value',
        'new_value',
        'reason',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * User (staff member) relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Actor who made the change.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Action description label.
     */
    public function actionLabel(): string
    {
        return match ($this->action) {
            'created' => 'Akun Dibuat',
            'status_changed' => 'Perubahan Status Keaktifan',
            'role_changed' => 'Perubahan Role Akses',
            'deactivated' => 'Penonaktifan Akun',
            'activated' => 'Aktivasi Akun Mandiri',
            'invitation_sent' => 'Undangan Aktivasi Terkirim',
            'reassigned' => 'Perubahan Jabatan / Unit Kerja',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}
