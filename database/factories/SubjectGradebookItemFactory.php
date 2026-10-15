<?php

namespace Database\Factories;

use App\Models\SubjectGradebook;
use App\Models\SubjectGradebookItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubjectGradebookItem>
 */
class SubjectGradebookItemFactory extends Factory
{
    protected $model = SubjectGradebookItem::class;

    public function definition(): array
    {
        return [
            'subject_gradebook_id' => SubjectGradebook::factory(),
            'student_id' => User::factory(),
            'assignment_avg' => 85.00,
            'quiz_avg' => 80.00,
            'midterm_score' => 88.00,
            'final_score_raw' => 90.00,
            'final_score' => 86.50,
            'predicate' => 'B',
            'is_passed' => true,
            'is_complete' => true,
            'achievement_description' => 'Menunjukkan penguasaan materi yang baik dan tuntas.',
        ];
    }
}
