<?php

namespace Database\Factories;

use App\Models\StudentReportCard;
use App\Models\StudentReportCardItem;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentReportCardItem>
 */
class StudentReportCardItemFactory extends Factory
{
    protected $model = StudentReportCardItem::class;

    public function definition(): array
    {
        return [
            'student_report_card_id' => StudentReportCard::factory(),
            'subject_id' => Subject::factory(),
            'subject_name' => 'Matematika',
            'subject_code' => 'MAT',
            'subject_group' => 'Umum',
            'teacher_name' => 'Guru Matematika, S.Pd',
            'passing_grade' => 75.00,
            'final_score' => 85.00,
            'predicate' => 'B',
            'is_passed' => true,
            'achievement_description' => 'Menguasai konsep aljabar dan geometri dengan baik.',
            'order_index' => 1,
        ];
    }
}
