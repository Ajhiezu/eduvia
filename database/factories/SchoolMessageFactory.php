<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\SchoolMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolMessage>
 */
class SchoolMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'reference_number' => SchoolMessage::generateReferenceNumber(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'category' => fake()->randomElement(array_keys(SchoolMessage::CATEGORIES)),
            'subject' => fake()->sentence(5),
            'message' => fake()->paragraphs(2, true),
            'status' => SchoolMessage::STATUS_NEW,
            'is_read' => false,
            'read_at' => null,
            'internal_notes' => null,
            'resolved_at' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }

    /**
     * Indicate that the message is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Indicate that the message is resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SchoolMessage::STATUS_RESOLVED,
            'is_read' => true,
            'read_at' => now(),
            'resolved_at' => now(),
        ]);
    }
}
