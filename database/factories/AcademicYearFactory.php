<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = fake()->unique()->numberBetween(2020, 2030);
        $endYear = $startYear + 1;

        return [
            'school_id' => School::factory(),
            'name' => "{$startYear}/{$endYear}",
            'start_date' => "{$startYear}-07-15",
            'end_date' => "{$endYear}-06-20",
            'is_active' => false,
            'description' => "Tahun Ajaran {$startYear}/{$endYear}",
        ];
    }
}
