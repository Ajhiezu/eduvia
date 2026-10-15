<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHomeroomReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (
            auth()->user()->isSuperAdmin() ||
            auth()->user()->isSchoolAdmin() ||
            auth()->user()->isTeacher()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'attendance_summary' => ['nullable', 'array'],
            'attendance_summary.present' => ['nullable', 'integer', 'min:0'],
            'attendance_summary.sick' => ['nullable', 'integer', 'min:0'],
            'attendance_summary.permit' => ['nullable', 'integer', 'min:0'],
            'attendance_summary.absent' => ['nullable', 'integer', 'min:0'],
            'attendance_summary.late' => ['nullable', 'integer', 'min:0'],
            'extracurriculars' => ['nullable', 'array'],
            'extracurriculars.*.name' => ['required_with:extracurriculars.*.predicate', 'string', 'max:100'],
            'extracurriculars.*.predicate' => ['nullable', 'string', 'max:10'],
            'extracurriculars.*.description' => ['nullable', 'string', 'max:500'],
            'achievements' => ['nullable', 'array'],
            'achievements.*.title' => ['required_with:achievements.*.level', 'string', 'max:150'],
            'achievements.*.level' => ['nullable', 'string', 'max:50'],
            'achievements.*.year' => ['nullable', 'string', 'max:10'],
            'homeroom_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
