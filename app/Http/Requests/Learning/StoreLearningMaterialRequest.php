<?php

namespace App\Http\Requests\Learning;

use App\Models\TeachingAssignment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLearningMaterialRequest extends FormRequest
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
            // Must belong to teacher's active assignments
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
            'description' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'in:draft,published,archived'],
            'attachments.*' => ['nullable', 'file', 'mimes:pdf,docx,pptx,xlsx,jpg,jpeg,png,mp4,zip', 'max:20480'], // max 20MB per file
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
            'title.required' => 'Judul materi pembelajaran wajib diisi.',
            'title.max' => 'Judul materi maksimal 255 karakter.',
            'video_url.url' => 'Format URL video pembelajaran tidak valid.',
            'status.in' => 'Status materi harus draft, published, atau archived.',
            'attachments.*.mimes' => 'Format file lampiran harus berupa PDF, Word, PowerPoint, Excel, Gambar, Video MP4, atau ZIP.',
            'attachments.*.max' => 'Ukuran setiap berkas lampiran maksimal 20 MB.',
        ];
    }
}
