<?php

namespace App\Http\Requests\Academic;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSemesterRequest extends FormRequest
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
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'name' => ['required', 'string', 'max:50'],
            'semester_type' => [
                'required',
                'string',
                Rule::in(['ganjil', 'genap']),
                Rule::unique('semesters', 'semester_type')->where(
                    fn ($query) => $query->where('academic_year_id', $this->input('academic_year_id'))
                ),
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
            'academic_year_id.required' => 'Pilih tahun ajaran yang valid.',
            'academic_year_id.exists' => 'Tahun ajaran tidak ditemukan.',
            'name.required' => 'Nama semester wajib diisi (contoh: Semester Ganjil).',
            'semester_type.required' => 'Pilih jenis semester (Ganjil atau Genap).',
            'semester_type.in' => 'Jenis semester harus ganjil atau genap.',
            'semester_type.unique' => 'Jenis semester ini sudah ada pada tahun ajaran yang dipilih.',
            'start_date.required' => 'Tanggal mulai semester wajib ditentukan.',
            'end_date.required' => 'Tanggal berakhir semester wajib ditentukan.',
            'end_date.after' => 'Tanggal berakhir semester harus setelah tanggal mulai.',
        ];
    }
}
