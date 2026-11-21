<?php

namespace App\Http\Requests\AI;

use App\Services\AI\AiAgentSettings;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAgentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && AiAgentSettings::canManage($this->user());
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        return [
            'api_key' => ['nullable', 'string', 'min:20', 'max:256', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'enabled' => ['required', 'boolean'],
            'school_id' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['api_key.regex' => 'Format API key Gemini tidak valid.', 'api_key.min' => 'Periksa kembali API key Gemini Anda.'];
    }
}
