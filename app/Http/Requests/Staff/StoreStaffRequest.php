<?php

namespace App\Http\Requests\Staff;

use App\Enums\StaffPosition;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->isSuperAdmin() || $user->isSchoolAdmin());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $actor = $this->user();

        // School Admin can only assign teacher, staff, or school_admin (not super_admin)
        $allowedRoles = $actor->isSuperAdmin()
            ? [UserRole::SCHOOL_ADMIN->value, UserRole::TEACHER->value, UserRole::STAFF->value]
            : [UserRole::SCHOOL_ADMIN->value, UserRole::TEACHER->value, UserRole::STAFF->value];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'position' => ['required', 'string', Rule::enum(StaffPosition::class)],
            'work_unit' => ['nullable', 'string', 'max:100'],
            'nip' => ['nullable', 'string', 'max:50'],
            'nuptk' => ['nullable', 'string', 'max:50'],
            'nik' => ['nullable', 'string', 'max:30'],
            'gender' => ['required', 'string', Rule::in(['male', 'female', 'L', 'P'])],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'employment_status' => ['required', 'string', Rule::in(['pns', 'p3k', 'gty', 'gtt', 'pty', 'ptt', 'honor'])],
            'join_date' => ['nullable', 'date'],
            'last_education' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'creation_mode' => ['required', 'string', Rule::in(['invitation', 'direct_password'])],
            'password' => [
                'exclude_unless:creation_mode,direct_password',
                'required',
                'string',
                Password::min(8),
                'confirmed',
            ],
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Email ini sudah terdaftar di sistem Eduvia.',
            'role.in' => 'Peran pengguna yang dipilih tidak valid atau di luar hak akses Anda.',
            'password.required' => 'Kata sandi wajib diisi jika memilih pembuatan akun langsung.',
            'password.min' => 'Kata sandi minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ];
    }
}
