<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\ParentTeacherConversation;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParentTeacherConversation>
 */
class ParentTeacherConversationFactory extends Factory
{
    protected $model = ParentTeacherConversation::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'student_id' => User::factory()->create(['role' => UserRole::STUDENT]),
            'parent_id' => User::factory()->create(['role' => UserRole::PARENT]),
            'teacher_id' => User::factory()->create(['role' => UserRole::TEACHER]),
            'subject' => fake()->sentence(4),
            'status' => ParentTeacherConversation::STATUS_ACTIVE,
            'last_message_at' => now(),
        ];
    }
}
