<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'school_id',
    'workflow_id',
    'request_number',
    'title',
    'requester_id',
    'current_step',
    'status',
    'payload',
    'approvable_type',
    'approvable_id',
    'submitted_at',
    'completed_at',
    'cancellation_reason',
])]
class ApprovalRequest extends Model
{
    use Auditable, BelongsToSchool, HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    protected function casts(): array
    {
        return [
            'current_step' => 'integer',
            'payload' => 'array',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ApprovalHistory::class, 'request_id')->orderBy('id');
    }

    public function currentStepDefinition(): ?ApprovalWorkflowStep
    {
        return $this->workflow?->steps()->where('step_order', $this->current_step)->first();
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_CANCELLED], true);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
