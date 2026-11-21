<?php

namespace App\Http\Requests\AI;

use App\Services\AI\AiAgentSettings;
use Illuminate\Foundation\Http\FormRequest;

class GenerateAgentDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && AiAgentSettings::canUse($this->user());
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'min:3', 'max:3000'],
            'form_fields' => ['sometimes', 'array', 'max:30'],
            'form_fields.*' => ['array:name,label,type,options'],
            'form_fields.*.name' => ['required', 'string', 'max:128', 'regex:/^[a-zA-Z][a-zA-Z0-9_.\[\]-]*$/'],
            'form_fields.*.label' => ['required', 'string', 'max:120'],
            'form_fields.*.type' => ['required', 'in:text,textarea,number,date,datetime-local,time,select,radio'],
            'form_fields.*.options' => ['sometimes', 'array', 'max:50'],
            'form_fields.*.options.*' => ['array:value,label'],
            'form_fields.*.options.*.value' => ['required', 'string', 'max:200'],
            'form_fields.*.options.*.label' => ['required', 'string', 'max:200'],
            'school_id' => ['prohibited'],
            'user_id' => ['prohibited'],
            'model' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'prompt.required' => 'Tuliskan permintaan Anda terlebih dahulu.',
            'prompt.min' => 'Permintaan minimal 3 karakter.',
            'prompt.max' => 'Permintaan maksimal 3.000 karakter.',
            'form_fields.max' => 'Gunakan satu formulir dengan maksimal 30 kolom.',
        ];
    }
}
