<?php

namespace App\Http\Requests\Student;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BatchPromotionRequest extends FormRequest
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
            'action' => ['required', 'in:promote,retain,transfer_out,graduate'],
            'source_classroom_id' => ['required', 'exists:classrooms,id'],
            'target_classroom_id' => ['required_if:action,promote', 'nullable', 'exists:classrooms,id', 'different:source_classroom_id'],
            'target_academic_year_id' => ['required_if:action,promote', 'nullable', 'exists:academic_years,id'],
            'effective_date' => ['required', 'date'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'action' => 'Jenis Proses',
            'source_classroom_id' => 'Rombongan Belajar Asal',
            'target_classroom_id' => 'Rombongan Belajar Tujuan',
            'target_academic_year_id' => 'Tahun Ajaran Baru',
            'student_ids' => 'Daftar Siswa',
            'effective_date' => 'Tanggal Efektif',
        ];
    }
}
