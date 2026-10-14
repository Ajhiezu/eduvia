<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentStatusHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'previous_status',
        'new_status',
        'effective_date',
        'reason',
        'changed_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'effective_date' => 'date',
    ];

    /**
     * Student (User) relationship.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * User who changed the status.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Previous status Indonesian label.
     */
    public function previousStatusLabel(): string
    {
        return match ($this->previous_status) {
            'active' => 'Aktif',
            'transferred' => 'Pindah / Mutasi',
            'graduated' => 'Lulus',
            'dropped' => 'Keluar / Dikeluarkan',
            'inactive' => 'Non-Aktif',
            default => ucfirst($this->previous_status),
        };
    }

    /**
     * New status Indonesian label.
     */
    public function newStatusLabel(): string
    {
        return match ($this->new_status) {
            'active' => 'Aktif',
            'transferred' => 'Pindah / Mutasi',
            'graduated' => 'Lulus',
            'dropped' => 'Keluar / Dikeluarkan',
            'inactive' => 'Non-Aktif',
            default => ucfirst($this->new_status),
        };
    }
}
