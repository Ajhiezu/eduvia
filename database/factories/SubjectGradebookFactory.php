<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\School;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\SubjectGradebook;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubjectGradebook>
 */
class SubjectGradebookFactory extends Factory
{
    protected $model = SubjectGradebook::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'classroom_id' => Classroom::factory(),
            'subject_id' => Subject::factory(),
            'teacher_id' => User::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'semester_id' => Semester::factory(),
            'weight_snapshot' => [
                'assignment_weight' => 30.00,
                'quiz_weight' => 20.00,
                'midterm_weight' => 25.00,
                'final_weight' => 25.00,
                'passing_grade' => 75.00,
            ],
            'status' => SubjectGradebook::STATUS_DRAFT,
        ];
    }
}
