<?php

namespace App\Http\Requests\Learning;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GradeSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $assignment = $this->route('assignment');

        if (! $user || ! $assignment) {
            return false;
        }

        if ($user->isSuperAdmin() || $user->isSchoolAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            return $assignment->teacher_id === $user->id;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $assignment = $this->route('assignment');
        $maxScore = $assignment?->max_score ?? 100;

        return [
            'score' => ['required', 'numeric', 'min:0', 'max:'.$maxScore],
            'feedback' => ['nullable', 'string', 'max:2000'],
            'teacher_feedback' => ['nullable', 'string', 'max:2000'],
            'is_grade_published' => ['nullable', 'boolean'],
            'publish_grade' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'score.required' => 'Nilai tugas wajib diisi.',
            'score.numeric' => 'Nilai tugas harus berupa angka.',
            'score.min' => 'Nilai minimal adalah 0.',
            'score.max' => 'Nilai tidak boleh melebihi batas maksimum.',
            'feedback.max' => 'Catatan umpan balik maksimal 2000 karakter.',
        ];
    }
}
