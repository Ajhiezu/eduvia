<?php

namespace Database\Factories;

use App\Models\ConversationMessage;
use App\Models\ParentTeacherConversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConversationMessage>
 */
class ConversationMessageFactory extends Factory
{
    protected $model = ConversationMessage::class;

    public function definition(): array
    {
        return [
            'conversation_id' => ParentTeacherConversation::factory(),
            'sender_id' => User::factory(),
            'body' => fake()->paragraph(),
            'attachment_path' => null,
            'attachment_name' => null,
            'is_read' => false,
            'read_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
