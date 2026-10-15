<?php

namespace Database\Factories;

use App\Models\ExamQuestion;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamQuestion>
 */
class ExamQuestionFactory extends Factory
{
    protected $model = ExamQuestion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $questionContent = fake()->sentence(10).'?';

        return [
            'school_id' => School::factory(),
            'created_by' => User::factory(),
            'subject_id' => Subject::factory(),
            'content' => $questionContent,
            'question_type' => ExamQuestion::TYPE_MC_SINGLE,
            'options' => [
                ['key' => 'A', 'text' => 'Opsi Jawaban Pertama'],
                ['key' => 'B', 'text' => 'Opsi Jawaban Kedua'],
                ['key' => 'C', 'text' => 'Opsi Jawaban Ketiga'],
                ['key' => 'D', 'text' => 'Opsi Jawaban Keempat'],
            ],
            'correct_answer' => 'A',
            'default_score' => 10.0,
            'explanation' => 'Pembahasan jawaban benar untuk soal ini.',
            'is_active' => true,
        ];
    }

    /**
     * True or false question state.
     */
    public function trueFalse(bool $isTrue = true): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => ExamQuestion::TYPE_TRUE_FALSE,
            'options' => null,
            'correct_answer' => $isTrue ? 'true' : 'false',
        ]);
    }

    /**
     * Essay question state.
     */
    public function essay(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => ExamQuestion::TYPE_ESSAY,
            'options' => null,
            'correct_answer' => null,
            'default_score' => 20.0,
        ]);
    }
}
