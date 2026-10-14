<?php

namespace App\Http\Requests\Admission;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateApplicantStatusRequest extends FormRequest
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
            'status' => ['required', 'in:pending,verified,revision_needed,accepted,rejected,re_registered'],
            'selection_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'selection_notes' => ['nullable', 'string', 'max:1000'],
            'rejection_reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'status' => 'Status Seleksi Calon Siswa',
            'selection_score' => 'Nilai / Skor Seleksi',
            'selection_notes' => 'Catatan Seleksi',
            'rejection_reason' => 'Alasan Penolakan',
        ];
    }
}
