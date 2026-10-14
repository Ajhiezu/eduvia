<?php

namespace App\Http\Requests\Academic;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicYearRequest extends FormRequest
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
        $academicYear = $this->route('academic_year');
        $academicYearId = $academicYear instanceof AcademicYear ? $academicYear->id : $academicYear;
        $schoolId = $academicYear?->school_id ?? School::current()?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('academic_years', 'name')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($academicYearId),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom Indonesian error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama/format tahun ajaran wajib diisi.',
            'name.unique' => 'Tahun ajaran dengan nama ini sudah terdaftar di sekolah ini.',
            'start_date.required' => 'Tanggal mulai tahun ajaran wajib ditentukan.',
            'end_date.required' => 'Tanggal berakhir tahun ajaran wajib ditentukan.',
            'end_date.after' => 'Tanggal berakhir harus lebih besar setelah tanggal mulai.',
        ];
    }
}
