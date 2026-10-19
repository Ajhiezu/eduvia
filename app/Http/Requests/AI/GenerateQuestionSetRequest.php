<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class GenerateQuestionSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role->value, ['teacher', 'school_admin', 'super_admin']);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'topic' => ['required', 'string', 'min:3', 'max:255'],
            'grade_level' => ['required', 'string', 'max:100'],
            'count' => ['required', 'integer', 'min:1', 'max:20'],
            'difficulty_distribution' => ['nullable', 'string', 'max:255'],
            'question_types' => ['nullable', 'array'],
            'question_types.*' => ['string', 'in:multiple_choice_single,true_false,essay'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'grade_level_id' => ['nullable', 'exists:grade_levels,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'topic.required' => 'Materi / Topik soal ujian wajib diisi.',
            'grade_level.required' => 'Tingkat kelas wajib ditentukan.',
            'count.required' => 'Jumlah butir soal wajib ditentukan (1 - 20 butir).',
        ];
    }
}
