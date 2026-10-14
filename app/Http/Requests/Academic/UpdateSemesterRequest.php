<?php

namespace App\Http\Requests\Academic;

use App\Enums\UserRole;
use App\Models\Semester;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSemesterRequest extends FormRequest
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
        $semester = $this->route('semester');
        $semesterId = $semester instanceof Semester ? $semester->id : $semester;
        $academicYearId = $this->input('academic_year_id') ?? $semester?->academic_year_id;

        return [
            'academic_year_id' => ['sometimes', 'required', 'exists:academic_years,id'],
            'name' => ['required', 'string', 'max:50'],
            'semester_type' => [
                'required',
                'string',
                Rule::in(['ganjil', 'genap']),
                Rule::unique('semesters', 'semester_type')
                    ->where(fn ($query) => $query->where('academic_year_id', $academicYearId))
                    ->ignore($semesterId),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['nullable', 'boolean'],
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
            'name.required' => 'Nama semester wajib diisi.',
            'semester_type.required' => 'Pilih jenis semester (Ganjil atau Genap).',
            'semester_type.in' => 'Jenis semester harus ganjil atau genap.',
            'semester_type.unique' => 'Jenis semester ini sudah ada pada tahun ajaran tersebut.',
            'start_date.required' => 'Tanggal mulai semester wajib ditentukan.',
            'end_date.required' => 'Tanggal berakhir semester wajib ditentukan.',
            'end_date.after' => 'Tanggal berakhir semester harus setelah tanggal mulai.',
        ];
    }
}
