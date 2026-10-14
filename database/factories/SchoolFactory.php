<?php

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' School',
            'slug' => fake()->unique()->slug(),
            'short_name' => fake()->lexify('SMP ???'),
            'npsn' => (string) fake()->unique()->numerify('########'),
            'educational_levels' => ['smp', 'sma'],
            'address' => fake()->address(),
            'city' => fake()->city(),
            'province' => 'DKI Jakarta',
            'postal_code' => fake()->postcode(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'website' => 'https://'.fake()->domainName(),
            'logo_path' => null,
            'is_active' => true,
            'public_website_enabled' => true,
        ];
    }
}
