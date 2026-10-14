<?php

namespace App\Http\Requests\Learning;

use App\Models\TeachingAssignment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
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

        if ($user->isSuperAdmin() || $user->isSchoolAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            $assignmentId = $this->input('teaching_assignment_id');

            return TeachingAssignment::where('id', $assignmentId)
                ->where('teacher_id', $user->id)
                ->where('is_active', true)
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
            'title' => ['required', 'string', 'max:255'],
            'topic' => ['nullable', 'string', 'max:150'],
            'instructions' => ['required', 'string'],
            'submission_type' => ['required', 'in:text_only,file_only,both'],
            'max_file_size_mb' => ['required', 'integer', 'min:1', 'max:50'],
            'allowed_file_types' => ['nullable', 'string', 'max:255'],
            'due_date' => ['required', 'date'],
            'max_score' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', 'in:draft,published,cancelled'],
            'allow_late_submission' => ['nullable', 'boolean'],
            'allow_resubmission' => ['nullable', 'boolean'],
            'attachments.*' => ['nullable', 'file', 'mimes:pdf,docx,pptx,xlsx,jpg,jpeg,png,zip', 'max:20480'],
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
            'teaching_assignment_id.required' => 'Mata pelajaran dan kelas yang diampu wajib dipilih.',
            'title.required' => 'Judul tugas pembelajaran wajib diisi.',
            'instructions.required' => 'Petunjuk / instruksi pengerjaan tugas wajib diisi.',
            'due_date.required' => 'Batas waktu / tenggat pengumpulan tugas wajib ditentukan.',
            'due_date.date' => 'Format tenggat waktu tidak valid.',
            'max_score.required' => 'Nilai maksimum wajib ditentukan.',
            'status.in' => 'Status tugas harus draft, published, atau cancelled.',
        ];
    }
}
