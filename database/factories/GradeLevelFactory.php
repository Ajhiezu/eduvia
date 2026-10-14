<?php

namespace Database\Factories;

use App\Models\GradeLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradeLevel>
 */
class GradeLevelFactory extends Factory
{
    protected $model = GradeLevel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $levelNum = fake()->numberBetween(1, 12);
        $level = match (true) {
            $levelNum <= 6 => 'sd',
            $levelNum <= 9 => 'smp',
            default => 'sma',
        };

        return [
            'educational_level' => $level,
            'level_number' => $levelNum,
            'name' => "Kelas {$levelNum}",
            'order' => $levelNum,
            'is_active' => true,
        ];
    }
}
