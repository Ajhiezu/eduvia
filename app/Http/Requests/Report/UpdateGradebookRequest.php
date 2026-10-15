<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGradebookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (
            auth()->user()->isSuperAdmin() ||
            auth()->user()->isSchoolAdmin() ||
            auth()->user()->isTeacher()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'submission_notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:subject_gradebook_items,id'],
            'items.*.assignment_avg' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.quiz_avg' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.midterm_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.final_score_raw' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.achievement_description' => ['nullable', 'string', 'max:1000'],
            'items.*.improvement_description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
