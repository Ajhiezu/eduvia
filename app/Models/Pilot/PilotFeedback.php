<?php

namespace App\Models\Pilot;

use App\Models\User;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PilotFeedback extends Model
{
    use BelongsToSchool, HasFactory;

    protected $table = 'pilot_feedbacks';

    protected $fillable = [
        'school_id',
        'user_id',
        'role',
        'category',
        'severity',
        'title',
        'description',
        'screenshot_path',
        'app_version',
        'device_info',
        'status',
        'triage_notes',
        'response_template_sent',
        'assigned_to',
        'resolved_at',
    ];

    protected $casts = [
        'device_info' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
