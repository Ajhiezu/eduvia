<?php

namespace Database\Factories;

use App\Models\LessonSchedule;
use App\Models\School;
use App\Models\TeachingAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonSchedule>
 */
class LessonScheduleFactory extends Factory
{
    protected $model = LessonSchedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'teaching_assignment_id' => TeachingAssignment::factory(),
            'day_of_week' => fake()->numberBetween(1, 6),
            'start_time' => '07:30',
            'end_time' => '09:00',
            'room' => fake()->randomElement(['Ruang 101', 'Ruang 102', 'Lab IPA', 'Lab Komputer', 'Ruang Musik']),
            'is_active' => true,
        ];
    }
}
