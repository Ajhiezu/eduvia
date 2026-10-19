<?php

namespace Database\Factories;

use App\Enums\AiDraftStatus;
use App\Enums\AiFeatureType;
use App\Models\AiGenerationDraft;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiGenerationDraft>
 */
class AiGenerationDraftFactory extends Factory
{
    protected $model = AiGenerationDraft::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'teacher_id' => User::factory(),
            'subject_id' => null,
            'grade_level_id' => null,
            'feature_type' => fake()->randomElement(AiFeatureType::cases()),
            'title' => fake()->sentence(3),
            'input_context' => [
                'topic' => fake()->sentence(3),
                'grade_level' => 'Kelas X',
            ],
            'generated_content' => [
                'summary' => fake()->paragraph(),
            ],
            'edited_content' => null,
            'status' => AiDraftStatus::DRAFT,
            'version' => 1,
            'accepted_at' => null,
            'discarded_at' => null,
            'destination_type' => null,
            'destination_id' => null,
            'provider' => 'gemini',
            'model_name' => 'gemini-1.5-flash',
            'tokens_used' => fake()->numberBetween(300, 1500),
            'estimated_cost_idr' => fake()->randomFloat(2, 0.5, 5.0),
            'feedback_rating' => null,
            'feedback_notes' => null,
            'is_flagged_bad_output' => false,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn () => [
            'status' => AiDraftStatus::ACCEPTED,
            'accepted_at' => now(),
        ]);
    }

    public function discarded(): static
    {
        return $this->state(fn () => [
            'status' => AiDraftStatus::DISCARDED,
            'discarded_at' => now(),
        ]);
    }
}
