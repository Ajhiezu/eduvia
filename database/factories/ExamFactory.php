<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\School;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    protected $model = Exam::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'teacher_id' => User::factory(),
            'subject_id' => Subject::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'semester_id' => Semester::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'duration_minutes' => 60,
            'start_time' => now()->subHour(),
            'end_time' => now()->addDays(2),
            'max_attempts' => 1,
            'passing_grade' => 75.0,
            'status' => Exam::STATUS_PUBLISHED,
            'shuffle_questions' => true,
            'shuffle_options' => true,
            'show_result_immediately' => true,
            'show_explanation_policy' => Exam::EXPLANATION_POLICY_AFTER_EXAM_END,
            'total_score' => 100.0,
        ];
    }
}
