<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'code' => 'SBJ-'.fake()->unique()->numerify('###'),
            'name' => fake()->randomElement(['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Ilmu Pengetahuan Alam', 'Ilmu Pengetahuan Sosial', 'Pendidikan Pancasila', 'Informatika', 'Seni Budaya']),
            'group' => 'Umum',
            'order' => fake()->numberBetween(1, 20),
            'is_active' => true,
            'description' => fake()->sentence(),
        ];
    }
}
