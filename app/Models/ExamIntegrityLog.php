<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamIntegrityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_attempt_id',
        'exam_id',
        'student_id',
        'event_type',
        'details',
        'client_timestamp',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'client_timestamp' => 'datetime',
            'recorded_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
