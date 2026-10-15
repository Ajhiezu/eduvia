<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\School;
use App\Models\Semester;
use App\Models\StudentReportCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentReportCard>
 */
class StudentReportCardFactory extends Factory
{
    protected $model = StudentReportCard::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'student_id' => User::factory(),
            'classroom_id' => Classroom::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'semester_id' => Semester::factory(),
            'homeroom_teacher_id' => User::factory(),
            'status' => StudentReportCard::STATUS_DRAFT,
            'attendance_summary' => [
                'present' => 80,
                'sick' => 2,
                'permit' => 1,
                'absent' => 0,
                'late' => 1,
            ],
            'extracurricular_evaluations' => [
                ['name' => 'Pramuka', 'predicate' => 'A', 'description' => 'Aktif dan disiplin'],
            ],
            'student_achievements' => [],
            'homeroom_notes' => 'Tingkatkan terus prestasi belajar.',
        ];
    }
}
