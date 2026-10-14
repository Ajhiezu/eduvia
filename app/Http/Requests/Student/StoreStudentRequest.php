<?php

namespace App\Http\Requests\Student;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:30'],
            'nis' => ['nullable', 'string', 'max:30', 'unique:student_profiles,nis'],
            'nisn' => ['nullable', 'string', 'max:30', 'unique:student_profiles,nisn'],
            'gender' => ['nullable', 'in:male,female,L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'religion' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'parent_name' => ['nullable', 'string', 'max:150'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
            'parent_relationship' => ['nullable', 'string', 'max:50'],
            'student_status' => ['required', 'in:active,transferred,graduated,dropped,inactive'],
            'enrollment_date' => ['nullable', 'date'],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama Siswa',
            'email' => 'Email Siswa',
            'nisn' => 'NISN',
            'nis' => 'NIS',
            'student_status' => 'Status Kesiswaan',
            'classroom_id' => 'Rombongan Belajar',
        ];
    }
}
