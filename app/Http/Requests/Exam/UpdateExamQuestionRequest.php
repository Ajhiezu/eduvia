<?php

namespace App\Http\Requests\Exam;

use App\Enums\UserRole;
use App\Models\ExamQuestion;
use App\Models\School;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateExamQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user || ! $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN, UserRole::TEACHER)) {
            return false;
        }

        /** @var ExamQuestion|null $question */
        $question = $this->route('question');
        if (! $question instanceof ExamQuestion) {
            $question = ExamQuestion::find($this->route('question'));
        }

        if (! $question) {
            return false;
        }

        $schoolId = $user->school_id ?? School::current()?->id;
        if ($schoolId && (int) $question->school_id !== (int) $schoolId) {
            return false;
        }

        return true;
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'question_type' => $this->input('question_type') ?? $this->input('type'),
            'content' => $this->input('content') ?? $this->input('question'),
            'correct_answer' => $this->input('correct_answer')
                ?? $this->input('correct_answer_tf')
                ?? $this->input('correct_answer_short'),
        ]);
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
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id')->where(function ($query) use ($schoolId) {
                    if ($schoolId) {
                        $query->where('school_id', $schoolId);
                    }
                }),
            ],
            'grade_level_id' => [
                'nullable',
                'integer',
                Rule::exists('grade_levels', 'id'),
            ],
            'question_type' => [
                'required',
                'string',
                Rule::in([
                    ExamQuestion::TYPE_MC_SINGLE,
                    ExamQuestion::TYPE_MC_MULTI,
                    ExamQuestion::TYPE_TRUE_FALSE,
                    ExamQuestion::TYPE_SHORT_ANSWER,
                    ExamQuestion::TYPE_ESSAY,
                ]),
            ],
            'content' => ['required', 'string'],
            'options' => ['nullable', 'array'],
            'options.*.key' => ['nullable', 'string', 'max:20'],
            'options.*.text' => ['nullable', 'string'],
            'correct_answer' => ['nullable'],
            'default_score' => ['required', 'numeric', 'min:0.5', 'max:100'],
            'explanation' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Custom validation rules based on question type.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = $this->input('question_type');
            $options = $this->input('options', []);
            $correct = $this->input('correct_answer');

            if ($type === ExamQuestion::TYPE_MC_SINGLE) {
                if (empty($options) || count($options) < 2) {
                    $validator->errors()->add('options', 'Soal pilihan ganda minimal membutuhkan 2 opsi jawaban.');
                }
                if ($correct === null || $correct === '') {
                    $validator->errors()->add('correct_answer', 'Kunci jawaban pilihan ganda wajib ditentukan.');
                }
            } elseif ($type === ExamQuestion::TYPE_MC_MULTI) {
                if (empty($options) || count($options) < 2) {
                    $validator->errors()->add('options', 'Soal pilihan ganda multi-jawaban minimal membutuhkan 2 opsi jawaban.');
                }
                if (! is_array($correct) || empty($correct)) {
                    $validator->errors()->add('correct_answer', 'Pilih minimal satu kunci jawaban yang benar.');
                }
            } elseif ($type === ExamQuestion::TYPE_TRUE_FALSE) {
                if ($correct === null || ! in_array((string) $correct, ['true', 'false', '1', '0', 'Benar', 'Salah'], true)) {
                    $validator->errors()->add('correct_answer', 'Kunci jawaban Benar atau Salah wajib dipilih.');
                }
            } elseif ($type === ExamQuestion::TYPE_SHORT_ANSWER) {
                if ($correct === null || $correct === '') {
                    $validator->errors()->add('correct_answer', 'Kunci jawaban isian singkat wajib diisi.');
                }
            }
        });
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran yang dipilih tidak valid atau tidak terdaftar pada sekolah ini.',
            'question_type.required' => 'Tipe soal wajib ditentukan.',
            'content.required' => 'Isi butir pertanyaan wajib diisi.',
            'default_score.required' => 'Bobot skor soal wajib diisi.',
            'default_score.min' => 'Bobot skor minimal 0.5 poin.',
            'default_score.max' => 'Bobot skor maksimal 100 poin.',
        ];
    }
}
