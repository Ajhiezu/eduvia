<?php

namespace App\Models\Pilot;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilotSurvey extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = [
        'school_id',
        'wave_number',
        'respondent_role',
        'satisfaction_score',
        'ease_of_use_score',
        'main_obstacle',
        'constructive_feedback',
    ];

    protected $casts = [
        'wave_number' => 'integer',
        'satisfaction_score' => 'integer',
        'ease_of_use_score' => 'integer',
    ];
}
