<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassroomStudent>
 */
class ClassroomStudentFactory extends Factory
{
    protected $model = ClassroomStudent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'classroom_id' => Classroom::factory(),
            'student_id' => User::factory()->create(['role' => UserRole::STUDENT]),
            'academic_year_id' => AcademicYear::factory(),
            'enrollment_status' => 'active',
            'joined_at' => now()->toDateString(),
            'left_at' => null,
            'notes' => null,
        ];
    }
}
