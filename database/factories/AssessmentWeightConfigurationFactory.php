<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\AssessmentWeightConfiguration;
use App\Models\School;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssessmentWeightConfiguration>
 */
class AssessmentWeightConfigurationFactory extends Factory
{
    protected $model = AssessmentWeightConfiguration::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'semester_id' => Semester::factory(),
            'subject_id' => null,
            'name' => 'Standar Kurikulum Merdeka',
            'assignment_weight' => 30.00,
            'quiz_weight' => 20.00,
            'midterm_weight' => 25.00,
            'final_weight' => 25.00,
            'passing_grade' => 75.00,
            'grade_scale' => AssessmentWeightConfiguration::defaultGradeScale(),
            'is_active' => true,
        ];
    }
}
