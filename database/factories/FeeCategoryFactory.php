<?php

namespace Database\Factories;

use App\Models\FeeCategory;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeeCategory>
 */
class FeeCategoryFactory extends Factory
{
    protected $model = FeeCategory::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'code' => 'FEE-'.strtoupper(fake()->unique()->lexify('???')),
            'name' => 'SPP Bulanan '.fake()->word(),
            'description' => fake()->sentence(),
            'type' => FeeCategory::TYPE_RECURRING,
            'billing_period' => FeeCategory::PERIOD_MONTHLY,
            'amount' => fake()->numberBetween(250000, 1000000),
            'is_active' => true,
        ];
    }
}
