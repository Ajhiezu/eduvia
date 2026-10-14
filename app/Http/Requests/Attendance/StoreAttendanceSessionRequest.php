<?php

namespace App\Http\Requests\Attendance;

use App\Enums\UserRole;
use App\Models\TeachingAssignment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceSessionRequest extends FormRequest
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

        if ($user->hasRole(UserRole::TEACHER)) {
            $assignmentId = $this->input('teaching_assignment_id');
            if (! $assignmentId) {
                return false;
            }

            return TeachingAssignment::where('id', $assignmentId)
                ->where('teacher_id', $user->id)
                ->exists();
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'teaching_assignment_id' => ['required', 'exists:teaching_assignments,id'],
            'meeting_number' => ['required', 'integer', 'min:1', 'max:100'],
            'date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after_or_equal:start_time'],
            'topic' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'in:draft,finalized'],
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
            'teaching_assignment_id.required' => 'Penugasan mengajar mata pelajaran wajib dipilih.',
            'meeting_number.required' => 'Nomor pertemuan pembelajaran wajib diisi.',
            'meeting_number.min' => 'Nomor pertemuan minimal 1.',
            'date.required' => 'Tanggal pertemuan wajib diisi.',
            'end_time.after_or_equal' => 'Jam selesai harus sama dengan atau setelah jam mulai.',
        ];
    }
}
