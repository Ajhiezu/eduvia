<?php

namespace App\Http\Requests\Student;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User $student */
        $student = $this->route('student');
        $profileId = $student->studentProfile?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$student->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'nis' => ['nullable', 'string', 'max:30', 'unique:student_profiles,nis,'.$profileId],
            'nisn' => ['nullable', 'string', 'max:30', 'unique:student_profiles,nisn,'.$profileId],
            'gender' => ['nullable', 'in:male,female,L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'religion' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'parent_name' => ['nullable', 'string', 'max:150'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
            'parent_relationship' => ['nullable', 'string', 'max:50'],
            'enrollment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
