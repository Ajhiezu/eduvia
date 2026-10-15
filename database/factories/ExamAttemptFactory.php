<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamAttempt>
 */
class ExamAttemptFactory extends Factory
{
    protected $model = ExamAttempt::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'student_id' => User::factory(),
            'attempt_number' => 1,
            'started_at' => now(),
            'expires_at' => now()->addMinutes(60),
            'submitted_at' => null,
            'questions_snapshot' => [],
            'total_score' => 0.0,
            'final_percentage' => 0.0,
            'status' => ExamAttempt::STATUS_IN_PROGRESS,
        ];
    }
}
