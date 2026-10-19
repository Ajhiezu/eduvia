<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = [
        'school_id',
        'user_id',
        'channel_push',
        'channel_whatsapp',
        'channel_email',
        'quiet_hours_enabled',
        'quiet_hours_start',
        'quiet_hours_end',
        'whatsapp_opt_in',
        'whatsapp_opt_in_at',
        'whatsapp_number',
    ];

    protected function casts(): array
    {
        return [
            'channel_push' => 'boolean',
            'channel_whatsapp' => 'boolean',
            'channel_email' => 'boolean',
            'quiet_hours_enabled' => 'boolean',
            'whatsapp_opt_in' => 'boolean',
            'whatsapp_opt_in_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Check if current time is within quiet hours.
     */
    public function isInQuietHours(?Carbon $now = null): bool
    {
        if (! $this->quiet_hours_enabled || empty($this->quiet_hours_start) || empty($this->quiet_hours_end)) {
            return false;
        }

        $now = $now ?: Carbon::now();
        $currentTime = $now->format('H:i');

        $start = $this->quiet_hours_start;
        $end = $this->quiet_hours_end;

        if ($start <= $end) {
            return $currentTime >= $start && $currentTime <= $end;
        }

        // Overnight window (e.g. 21:00 - 06:00)
        return $currentTime >= $start || $currentTime <= $end;
    }
}
