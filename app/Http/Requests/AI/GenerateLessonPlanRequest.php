<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class GenerateLessonPlanRequest extends FormRequest
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
            'learning_objectives' => ['required', 'string', 'min:5', 'max:1000'],
            'grade_level' => ['required', 'string', 'max:100'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:360'],
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
            'topic.required' => 'Materi / Topik pembelajaran wajib diisi.',
            'learning_objectives.required' => 'Tujuan / Capaian Pembelajaran (CP/TP) wajib diisi.',
            'grade_level.required' => 'Tingkat kelas / jenjang wajib ditentukan.',
        ];
    }
}
