<?php

namespace App\Http\Requests\AI;

use App\Services\AI\AiAgentSettings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RunAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && AiAgentSettings::canUse($this->user());
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        return [
            'messages' => ['required', 'array', 'min:1', 'max:60'],
            'messages.*' => ['array:role,text'],
            'messages.*.role' => ['required', 'in:user,assistant,tool'],
            'messages.*.text' => ['required', 'string', 'max:24000'],
            'page' => ['nullable', 'string', 'max:300'],
            'school_id' => ['prohibited'], 'model' => ['prohibited'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $validator->errors()->isEmpty()) {
                return;
            }
            $messages = $this->input('messages', []);
            if (collect($messages)->sum(fn (array $message): int => mb_strlen($message['text'])) > 100000) {
                $validator->errors()->add('messages', 'Percakapan terlalu panjang. Mulai percakapan baru.');
            }
            if (! collect($messages)->contains(fn (array $message): bool => $message['role'] === 'user')) {
                $validator->errors()->add('messages', 'Tuliskan perintah Anda terlebih dahulu.');
            }
        });
    }
}
