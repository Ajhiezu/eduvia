<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentWeightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (
            auth()->user()->isSuperAdmin() ||
            auth()->user()->isSchoolAdmin()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'semester_id' => ['nullable', 'exists:semesters,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'assignment_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'quiz_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'midterm_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'final_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'passing_grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $sum = (float) $this->input('assignment_weight', 0)
                + (float) $this->input('quiz_weight', 0)
                + (float) $this->input('midterm_weight', 0)
                + (float) $this->input('final_weight', 0);

            if (abs($sum - 100.00) > 0.001) {
                $validator->errors()->add('assignment_weight', "Total seluruh bobot harus tepat 100%. Saat ini: {$sum}%.");
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama skema bobot penilaian wajib diisi.',
            'assignment_weight.required' => 'Bobot tugas wajib diisi.',
            'quiz_weight.required' => 'Bobot kuis / formatif wajib diisi.',
            'midterm_weight.required' => 'Bobot UTS wajib diisi.',
            'final_weight.required' => 'Bobot UAS wajib diisi.',
            'passing_grade.required' => 'Batas KKM wajib diisi.',
        ];
    }
}
