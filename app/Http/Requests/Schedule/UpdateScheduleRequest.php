<?php

namespace App\Http\Requests\Schedule;

use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\LessonSchedule;
use App\Models\School;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user || ! $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN)) {
            return false;
        }

        /** @var LessonSchedule|null $schedule */
        $schedule = $this->route('schedule');
        if (! $schedule instanceof LessonSchedule) {
            $schedule = LessonSchedule::find($this->route('schedule'));
        }

        if (! $schedule) {
            return false;
        }

        $schoolId = $user->school_id ?? School::current()?->id;
        if ($schoolId && $schedule->school_id && (int) $schedule->school_id !== (int) $schoolId) {
            return false;
        }

        if ($schoolId && $schedule->teachingAssignment && (int) $schedule->teachingAssignment->school_id !== (int) $schoolId) {
            return false;
        }

        return true;
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
            'classroom_id' => [
                'required',
                'integer',
                Rule::exists('classrooms', 'id')->where(function ($query) use ($schoolId) {
                    if ($schoolId) {
                        $query->where('school_id', $schoolId);
                    }
                }),
            ],
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id')->where(function ($query) use ($schoolId) {
                    if ($schoolId) {
                        $query->where('school_id', $schoolId);
                    }
                }),
            ],
            'teacher_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) use ($schoolId) {
                    $query->where('role', UserRole::TEACHER->value)
                        ->where('is_active', true);
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
            'semester_id' => [
                'nullable',
                'integer',
                Rule::exists('semesters', 'id')->where(function ($query) use ($schoolId) {
                    if ($schoolId) {
                        $query->whereIn('academic_year_id', function ($sub) use ($schoolId) {
                            $sub->select('id')->from('academic_years')->where('school_id', $schoolId);
                        });
                    }
                }),
            ],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'room' => ['nullable', 'string', 'max:60'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $startTime = (string) $this->input('start_time');
            $endTime = (string) $this->input('end_time');

            // 1. Time interval validation
            if ($startTime && $endTime) {
                if ($startTime >= $endTime) {
                    $validator->errors()->add('end_time', 'Waktu selesai harus lebih lambat daripada waktu mulai.');

                    return;
                }
            }

            // If basic field errors already present, do not perform conflict checks
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $schoolId = (int) ($this->user()?->school_id ?? School::current()?->id);
            $classroomId = (int) $this->input('classroom_id');
            $teacherId = (int) $this->input('teacher_id');
            $dayOfWeek = (int) $this->input('day_of_week');
            $room = $this->filled('room') ? trim((string) $this->input('room')) : null;

            // Resolve academic year id
            $academicYearId = (int) $this->input('academic_year_id');
            if (! $academicYearId) {
                $classroom = Classroom::find($classroomId);
                $academicYearId = (int) ($classroom?->academic_year_id ?? School::current()?->activeAcademicYear?->id);
            }

            $semesterId = $this->filled('semester_id') ? (int) $this->input('semester_id') : null;

            /** @var LessonSchedule|null $schedule */
            $schedule = $this->route('schedule');
            $scheduleId = $schedule instanceof LessonSchedule ? $schedule->id : (int) $schedule;

            // 2. Perform backend conflict check excluding self
            $conflicts = LessonSchedule::findConflicts(
                schoolId: $schoolId,
                academicYearId: $academicYearId,
                semesterId: $semesterId,
                dayOfWeek: $dayOfWeek,
                startTime: $startTime,
                endTime: $endTime,
                teacherId: $teacherId,
                classroomId: $classroomId,
                room: $room,
                excludeScheduleId: $scheduleId
            );

            if (isset($conflicts['teacher'])) {
                $validator->errors()->add('teacher_id', $conflicts['teacher']);
            }
            if (isset($conflicts['classroom'])) {
                $validator->errors()->add('classroom_id', $conflicts['classroom']);
            }
            if (isset($conflicts['room'])) {
                $validator->errors()->add('room', $conflicts['room']);
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
            'classroom_id.required' => 'Kelas (rombongan belajar) wajib dipilih.',
            'classroom_id.exists' => 'Kelas yang dipilih tidak valid atau tidak terdaftar pada sekolah ini.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran yang dipilih tidak valid atau tidak terdaftar pada sekolah ini.',
            'teacher_id.required' => 'Guru pengampu wajib dipilih.',
            'teacher_id.exists' => 'Guru pengampu yang dipilih tidak valid, tidak aktif, atau tidak terdaftar pada sekolah ini.',
            'day_of_week.required' => 'Hari jadwal wajib ditentukan.',
            'day_of_week.between' => 'Hari harus bernilai antara 1 (Senin) hingga 7 (Minggu).',
            'start_time.required' => 'Waktu mulai wajib diisi.',
            'start_time.date_format' => 'Format waktu mulai harus HH:MM (contoh: 07:30).',
            'end_time.required' => 'Waktu selesai wajib diisi.',
            'end_time.date_format' => 'Format waktu selesai harus HH:MM (contoh: 09:00).',
            'room.max' => 'Nama atau nomor ruang maksimal 60 karakter.',
        ];
    }
}
