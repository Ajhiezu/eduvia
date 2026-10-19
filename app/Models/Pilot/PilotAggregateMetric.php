<?php

namespace App\Models\Pilot;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilotAggregateMetric extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = [
        'school_id',
        'metric_date',
        'metric_key',
        'metric_value',
        'dimensions',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'metric_value' => 'decimal:4',
        'dimensions' => 'array',
    ];
}
