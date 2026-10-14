<?php

namespace App\Http\Requests\Classroom;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddStudentToClassroomRequest extends FormRequest
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
            'student_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', UserRole::STUDENT->value)
                        ->where('is_active', true);
                }),
            ],
            'joined_at' => ['nullable', 'date'],
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
            'student_id.required' => 'Pilih siswa yang akan ditempatkan ke kelas.',
            'student_id.exists' => 'Siswa yang dipilih tidak valid atau berstatus non-aktif.',
            'joined_at.date' => 'Format tanggal penempatan tidak valid.',
        ];
    }
}
