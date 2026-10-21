<?php

namespace App\Models\Pilot;

use App\Models\User;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PilotImportBatch extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = [
        'school_id',
        'batch_code',
        'entity_type',
        'file_name',
        'total_rows',
        'imported_rows',
        'failed_rows',
        'reconciliation_report',
        'created_ids',
        'status',
        'imported_by',
        'rolled_back_at',
        'rolled_back_by',
    ];

    protected $casts = [
        'reconciliation_report' => 'array',
        'created_ids' => 'array',
        'rolled_back_at' => 'datetime',
    ];

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function rollbacker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rolled_back_by');
    }

    public function isRolledBack(): bool
    {
        return $this->status === 'rolled_back';
    }
}
