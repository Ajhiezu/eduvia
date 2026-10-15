<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\ParentLinkCode;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParentLinkCode>
 */
class ParentLinkCodeFactory extends Factory
{
    protected $model = ParentLinkCode::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'student_id' => User::factory()->create(['role' => UserRole::STUDENT]),
            'code' => ParentLinkCode::generateCode(),
            'relationship_type' => 'guardian',
            'expires_at' => now()->addDays(7),
            'max_uses' => 1,
            'used_count' => 0,
            'used_by' => null,
            'used_at' => null,
            'created_by' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'used_count' => 1,
            'used_at' => now(),
        ]);
    }
}
