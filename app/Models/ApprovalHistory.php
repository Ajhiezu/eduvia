<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'request_id',
    'step_number',
    'actor_id',
    'delegated_from_id',
    'action',
    'comment',
    'acted_at',
])]
class ApprovalHistory extends Model
{
    use HasFactory;

    public const ACTION_SUBMITTED = 'submitted';

    public const ACTION_APPROVED = 'approved';

    public const ACTION_REJECTED = 'rejected';

    public const ACTION_ESCALATED = 'escalated';

    public const ACTION_CANCELLED = 'cancelled';

    protected function casts(): array
    {
        return [
            'step_number' => 'integer',
            'acted_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class, 'request_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function delegatedFrom(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegated_from_id');
    }
}
