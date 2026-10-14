<?php

namespace App\Http\Requests\Classroom;

use App\Enums\UserRole;
use App\Models\School;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassroomRequest extends FormRequest
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
        $school = School::current();
        $schoolId = $school?->id;
        $classroomId = $this->route('classroom')?->id ?? $this->route('classroom');

        return [
            'academic_year_id' => [
                'required',
                'integer',
                Rule::exists('academic_years', 'id')->where('school_id', $schoolId),
            ],
            'grade_level_id' => [
                'required',
                'integer',
                Rule::exists('grade_levels', 'id')->where('is_active', true),
            ],
            'homeroom_teacher_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', UserRole::TEACHER->value)
                        ->where('is_active', true);
                }),
            ],
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('classrooms', 'name')->where(function ($query) {
                    $query->where('academic_year_id', $this->input('academic_year_id'));
                })->ignore($classroomId),
            ],
            'code' => ['nullable', 'string', 'max:30'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
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
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists' => 'Tahun ajaran yang dipilih tidak valid atau tidak sesuai sekolah.',
            'grade_level_id.required' => 'Tingkat kelas wajib dipilih.',
            'grade_level_id.exists' => 'Tingkat kelas yang dipilih tidak valid atau non-aktif.',
            'homeroom_teacher_id.exists' => 'Wali kelas harus merupakan guru aktif yang terdaftar.',
            'name.required' => 'Nama rombongan belajar wajib diisi.',
            'name.unique' => 'Nama rombongan belajar sudah digunakan pada tahun ajaran yang dipilih.',
            'capacity.min' => 'Kapasitas kelas minimal 1 siswa.',
            'capacity.max' => 'Kapasitas kelas maksimal 100 siswa.',
        ];
    }
}
