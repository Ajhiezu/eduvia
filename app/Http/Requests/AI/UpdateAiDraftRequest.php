<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAiDraftRequest extends FormRequest
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
            'action' => ['required', 'string', 'in:save_edit,accept,discard,feedback'],
            'edited_content' => ['nullable', 'array'],
            'feedback_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'feedback_notes' => ['nullable', 'string', 'max:1000'],
            'is_flagged_bad_output' => ['nullable', 'boolean'],
        ];
    }
}
