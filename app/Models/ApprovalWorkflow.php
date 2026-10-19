<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'school_id',
    'code',
    'name',
    'description',
    'is_active',
])]
class ApprovalWorkflow extends Model
{
    use BelongsToSchool, HasFactory;

    public const CODE_FEE_RELIEF = 'fee_relief';

    public const CODE_SCHOOL_LETTER = 'school_letter';

    public const CODE_TEACHER_LEAVE = 'teacher_leave';

    public const CODE_GRADE_CORRECTION = 'grade_correction';

    public const CODE_BUDGET_REQUEST = 'budget_request';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalWorkflowStep::class, 'workflow_id')->orderBy('step_order');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class, 'workflow_id');
    }
}
