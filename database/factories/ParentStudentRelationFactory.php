<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\ParentStudentRelation;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParentStudentRelation>
 */
class ParentStudentRelationFactory extends Factory
{
    protected $model = ParentStudentRelation::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'parent_id' => User::factory()->create(['role' => UserRole::PARENT]),
            'student_id' => User::factory()->create(['role' => UserRole::STUDENT]),
            'relationship_type' => fake()->randomElement(['father', 'mother', 'guardian']),
            'status' => ParentStudentRelation::STATUS_ACTIVE,
            'is_primary_contact' => false,
            'verified_at' => now(),
            'verified_by' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ParentStudentRelation::STATUS_PENDING,
            'verified_at' => null,
            'verified_by' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ParentStudentRelation::STATUS_INACTIVE,
        ]);
    }
}
