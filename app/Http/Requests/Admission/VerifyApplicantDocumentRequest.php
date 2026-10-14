<?php

namespace App\Http\Requests\Admission;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifyApplicantDocumentRequest extends FormRequest
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
            'status' => ['required', 'in:valid,invalid,pending'],
            'review_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'status' => 'Status Verifikasi Dokumen',
            'review_notes' => 'Catatan Verifikasi',
        ];
    }
}
