<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'workflow_id',
    'step_order',
    'name',
    'approver_role',
    'specific_user_id',
    'timeout_hours',
    'condition_rules',
])]
class ApprovalWorkflowStep extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'step_order' => 'integer',
            'timeout_hours' => 'integer',
            'condition_rules' => 'array',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    public function specificUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'specific_user_id');
    }
}
