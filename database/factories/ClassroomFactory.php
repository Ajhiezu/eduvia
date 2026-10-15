<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Classroom>
 */
class ClassroomFactory extends Factory
{
    protected $model = Classroom::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'grade_level_id' => fn () => GradeLevel::inRandomOrder()->first()?->id ?? GradeLevel::factory(),
            'homeroom_teacher_id' => null,
            'name' => fake()->unique()->randomElement(['VII-A', 'VII-B', 'VIII-A', 'VIII-B', 'IX-A', 'X-MIPA-1', 'XI-IPS-1', 'XII-IPA-2']),
            'code' => 'CLS-'.fake()->unique()->numerify('###'),
            'capacity' => 36,
            'is_active' => true,
            'notes' => fake()->sentence(),
        ];
    }
}
