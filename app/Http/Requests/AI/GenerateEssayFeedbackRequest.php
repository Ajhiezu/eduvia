<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class GenerateEssayFeedbackRequest extends FormRequest
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
            'assignment_title' => ['required', 'string', 'min:3', 'max:255'],
            'essay_text' => ['required', 'string', 'min:20', 'max:10000'],
            'student_pseudo_id' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'assignment_title.required' => 'Judul tugas esai wajib diisi.',
            'essay_text.required' => 'Isi teks esai siswa wajib disertakan (min. 20 karakter).',
        ];
    }
}
