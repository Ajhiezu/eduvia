<?php

namespace App\Http\Requests\Setting;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSystemSettingsRequest extends FormRequest
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
            'timezone' => ['required', 'string', Rule::in(['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura', 'UTC'])],
            'locale' => ['required', 'string', Rule::in(['id', 'en'])],
            'date_format' => ['required', 'string', Rule::in(['d/m/Y', 'Y-m-d', 'd-m-Y', 'd M Y'])],
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
            'timezone.required' => 'Zona waktu wajib dipilih.',
            'timezone.in' => 'Zona waktu yang dipilih tidak didukung.',
            'locale.required' => 'Bahasa sistem wajib dipilih.',
            'locale.in' => 'Bahasa yang dipilih tidak didukung.',
            'date_format.required' => 'Format tampilan tanggal wajib dipilih.',
            'date_format.in' => 'Format tanggal yang dipilih tidak didukung.',
        ];
    }
}
