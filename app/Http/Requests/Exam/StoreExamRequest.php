<?php

namespace App\Http\Requests\Exam;

use App\Enums\UserRole;
use App\Models\Exam;
use App\Models\School;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN, UserRole::TEACHER);
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('explanation_policy') && ! $this->has('show_explanation_policy')) {
            $this->merge([
                'show_explanation_policy' => $this->input('explanation_policy'),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $schoolId = $this->user()?->school_id ?? School::current()?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id')->where(function ($query) use ($schoolId) {
                    if ($schoolId) {
                        $query->where('school_id', $schoolId);
                    }
                }),
            ],
            'academic_year_id' => [
                'nullable',
                'integer',
                Rule::exists('academic_years', 'id')->where(function ($query) use ($schoolId) {
                    if ($schoolId) {
                        $query->where('school_id', $schoolId);
                    }
                }),
            ],
            'classroom_ids' => ['required', 'array', 'min:1'],
            'classroom_ids.*' => [
                'integer',
                Rule::exists('classrooms', 'id')->where(function ($query) use ($schoolId) {
                    if ($schoolId) {
                        $query->where('school_id', $schoolId);
                    }
                }),
            ],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'passing_grade' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'shuffle_questions' => ['sometimes', 'boolean'],
            'shuffle_options' => ['sometimes', 'boolean'],
            'show_result_immediately' => ['sometimes', 'boolean'],
            'show_explanation_policy' => [
                'required',
                'string',
                Rule::in([
                    Exam::EXPLANATION_NEVER,
                    Exam::EXPLANATION_AFTER_SUBMIT,
                    Exam::EXPLANATION_AFTER_CLOSED,
                ]),
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
            'title.required' => 'Judul ujian wajib diisi.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran tidak valid atau tidak terdaftar pada sekolah ini.',
            'classroom_ids.required' => 'Pilih minimal satu kelas peserta ujian.',
            'classroom_ids.min' => 'Pilih minimal satu kelas peserta ujian.',
            'duration_minutes.required' => 'Durasi pengerjaan ujian wajib diisi.',
            'duration_minutes.min' => 'Durasi minimal pengerjaan adalah 1 menit.',
            'start_time.required' => 'Waktu mulai ujian wajib ditentukan.',
            'end_time.required' => 'Batas akhir waktu ujian wajib ditentukan.',
            'end_time.after' => 'Batas akhir ujian harus lebih lambat daripada waktu mulai ujian.',
            'max_attempts.required' => 'Batas percobaan wajib ditentukan.',
        ];
    }
}
