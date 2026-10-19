<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class GenerateRubricRequest extends FormRequest
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
            'task_title' => ['required', 'string', 'min:3', 'max:255'],
            'learning_goals' => ['required', 'string', 'min:5', 'max:1000'],
            'scale' => ['nullable', 'string', 'max:100'],
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
            'task_title.required' => 'Judul tugas / proyek penilaian wajib diisi.',
            'learning_goals.required' => 'Tujuan penilaian pembelajaran wajib diisi.',
        ];
    }
}
