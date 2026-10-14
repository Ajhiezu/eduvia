<?php

namespace App\Http\Requests\Staff;

use App\Enums\StaffPosition;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $actor = $this->user();
        if (! $actor || (! $actor->isSuperAdmin() && ! $actor->isSchoolAdmin())) {
            return false;
        }

        /** @var User|null $targetUser */
        $targetUser = $this->route('staff') ?? $this->route('user');

        if ($targetUser && $targetUser->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User|null $targetUser */
        $targetUser = $this->route('staff') ?? $this->route('user');
        $targetId = $targetUser?->id;

        $actor = $this->user();
        $allowedRoles = [UserRole::SCHOOL_ADMIN->value, UserRole::TEACHER->value, UserRole::STAFF->value];
        if ($actor->isSuperAdmin()) {
            $allowedRoles[] = UserRole::SUPER_ADMIN->value;
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($targetId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => [
                'required',
                'string',
                Rule::in($allowedRoles),
                // Prevent self-role modification to prevent privilege escalation or accidental lockout
                function ($attribute, $value, $fail) use ($actor, $targetUser) {
                    if ($actor->id === $targetUser?->id && $value !== $targetUser->role->value) {
                        $fail('Anda tidak dapat mengubah peran akses akun Anda sendiri.');
                    }
                },
            ],
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
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'on_leave', 'resigned'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'change_password' => ['nullable', 'boolean'],
            'password' => [
                'nullable',
                'exclude_unless:change_password,1',
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
            'email.unique' => 'Email ini sudah digunakan oleh pengguna lain di sistem.',
            'role.in' => 'Peran pengguna yang dipilih di luar kewenangan Anda.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ];
    }
}
