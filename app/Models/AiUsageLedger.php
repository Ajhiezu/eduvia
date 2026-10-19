<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLedger extends Model
{
    use BelongsToSchool, HasFactory;

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'user_id',
        'feature_type',
        'provider',
        'model_name',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'estimated_cost_idr',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'total_tokens' => 'integer',
            'estimated_cost_idr' => 'decimal:4',
            'created_at' => 'datetime',
        ];
    }

    /**
     * User who initiated the AI operation.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
