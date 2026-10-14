<?php

namespace App\Http\Requests\Learning;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLearningMaterialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $material = $this->route('material');

        if (! $user || ! $material) {
            return false;
        }

        if ($user->isSuperAdmin() || $user->isSchoolAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            return $material->teacher_id === $user->id;
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
            'title' => ['required', 'string', 'max:255'],
            'topic' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'in:draft,published,archived'],
            'attachments.*' => ['nullable', 'file', 'mimes:pdf,docx,pptx,xlsx,jpg,jpeg,png,mp4,zip', 'max:20480'],
            'delete_attachments.*' => ['nullable', 'integer', 'exists:learning_material_attachments,id'],
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
            'title.required' => 'Judul materi pembelajaran wajib diisi.',
            'title.max' => 'Judul materi maksimal 255 karakter.',
            'video_url.url' => 'Format URL video pembelajaran tidak valid.',
            'status.in' => 'Status materi harus draft, published, atau archived.',
            'attachments.*.mimes' => 'Format file lampiran harus berupa PDF, Word, PowerPoint, Excel, Gambar, Video MP4, atau ZIP.',
            'attachments.*.max' => 'Ukuran setiap berkas lampiran maksimal 20 MB.',
        ];
    }
}
