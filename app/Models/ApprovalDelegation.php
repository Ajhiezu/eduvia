<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'school_id',
    'delegator_id',
    'delegatee_id',
    'start_date',
    'end_date',
    'reason',
    'is_active',
])]
class ApprovalDelegation extends Model
{
    use BelongsToSchool, HasFactory;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function delegator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegator_id');
    }

    public function delegatee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegatee_id');
    }

    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $today = Carbon::today();

        return $today->betweenIncluded($this->start_date, $this->end_date);
    }
}
