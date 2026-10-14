<?php

namespace App\Http\Requests\Learning;

use App\Models\ClassroomStudent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmitAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $assignment = $this->route('assignment');

        if (! $user || ! $user->isStudent() || ! $assignment) {
            return false;
        }

        // Must be actively enrolled in assignment's classroom
        return ClassroomStudent::where('classroom_id', $assignment->classroom_id)
            ->where('student_id', $user->id)
            ->where('enrollment_status', 'active')
            ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $assignment = $this->route('assignment');
        $maxKilobytes = ($assignment?->max_file_size_mb ?? 10) * 1024;
        $mimes = $assignment?->allowed_file_types ?: 'pdf,docx,jpg,jpeg,png,zip';
        $mimes = str_replace(' ', '', $mimes);

        return [
            'content' => ['nullable', 'string'],
            'student_notes' => ['nullable', 'string'],
            'action' => ['nullable', 'in:draft,submit'],
            'status' => ['nullable', 'in:draft,submitted,submit'],
            'files.*' => ['nullable', 'file', 'mimes:'.$mimes, 'max:'.$maxKilobytes],
            'attachments.*' => ['nullable', 'file', 'mimes:'.$mimes, 'max:'.$maxKilobytes],
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
            'action.in' => 'Aksi pengumpulan harus berupa simpan draft atau kirim jawaban.',
            'files.*.mimes' => 'Format berkas tidak diizinkan oleh guru pengampu.',
            'files.*.max' => 'Ukuran berkas melebihi batas maksimum yang ditentukan pada tugas ini.',
        ];
    }
}
