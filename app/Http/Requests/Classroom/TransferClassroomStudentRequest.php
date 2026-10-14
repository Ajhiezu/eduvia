<?php

namespace App\Http\Requests\Classroom;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferClassroomStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'target_classroom_id' => [
                'required',
                'integer',
                Rule::exists('classrooms', 'id')->where('is_active', true),
            ],
            'effective_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom validation messages in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'target_classroom_id.required' => 'Pilih rombongan belajar tujuan perpindahan.',
            'target_classroom_id.exists' => 'Rombongan belajar tujuan tidak valid atau berstatus non-aktif.',
            'effective_date.date' => 'Format tanggal efektif pindah tidak valid.',
        ];
    }
}
