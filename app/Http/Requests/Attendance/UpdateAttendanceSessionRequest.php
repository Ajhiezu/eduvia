<?php

namespace App\Http\Requests\Attendance;

use App\Enums\UserRole;
use App\Models\AttendanceSession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        if ($user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN)) {
            return true;
        }

        $session = $this->route('attendance_session') ?? $this->route('session');
        if (is_numeric($session) || is_string($session)) {
            $session = AttendanceSession::find($session);
        }

        if (! $session instanceof AttendanceSession) {
            return false;
        }

        return $user->hasRole(UserRole::TEACHER) && (int) $session->teacher_id === (int) $user->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'topic' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'records' => ['required', 'array'],
            'records.*.student_id' => ['required', 'exists:users,id'],
            'records.*.status' => ['required', 'in:present,sick,permit,absent,late'],
            'records.*.notes' => ['nullable', 'string', 'max:255'],
            'action' => ['nullable', 'in:save_draft,finalize'],
            'correction_reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'records.required' => 'Daftar presensi siswa wajib dikirimkan.',
            'records.*.status.in' => 'Status kehadiran siswa tidak valid.',
        ];
    }
}
