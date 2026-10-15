<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionItem;
use App\Models\School;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class CbtSeeder extends Seeder
{
    /**
     * Run the database seeds for CBT system demo.
     */
    public function run(): void
    {
        $school = School::first() ?? School::create([
            'name' => 'SMP Nusantara Jaya',
            'slug' => 'smp-nusantara-jaya',
            'npsn' => '20104567',
            'is_active' => true,
        ]);

        $teacher = User::where('email', 'guru@eduvia.test')->first() ?? User::first();
        $student = User::where('email', 'siswa@eduvia.test')->first();
        $academicYear = AcademicYear::first();
        $semester = Semester::first();
        $classroom = Classroom::first();
        $subject = Subject::where('name', 'like', '%Matematika%')->first() ?? Subject::first();

        if (! $teacher || ! $subject || ! $academicYear || ! $semester) {
            return;
        }

        // 1. Seed Question Bank items
        $q1 = ExamQuestion::firstOrCreate(
            [
                'school_id' => $school->id,
                'content' => 'Jika 3x + 7 = 22, berapakah nilai x yang memenuhi persamaan tersebut?',
            ],
            [
                'created_by' => $teacher->id,
                'subject_id' => $subject->id,
                'question_type' => ExamQuestion::TYPE_MC_SINGLE,
                'options' => [
                    ['key' => 'A', 'text' => '3'],
                    ['key' => 'B', 'text' => '5'],
                    ['key' => 'C', 'text' => '7'],
                    ['key' => 'D', 'text' => '15'],
                ],
                'correct_answer' => 'B',
                'default_score' => 20.0,
                'explanation' => '3x + 7 = 22 -> 3x = 15 -> x = 5.',
                'is_active' => true,
            ]
        );

        $q2 = ExamQuestion::firstOrCreate(
            [
                'school_id' => $school->id,
                'content' => 'Manakah di antara bangun datar berikut yang selalu memiliki keempat sisi sama panjang? (Pilih semua yang benar)',
            ],
            [
                'created_by' => $teacher->id,
                'subject_id' => $subject->id,
                'question_type' => ExamQuestion::TYPE_MC_MULTI,
                'options' => [
                    ['key' => 'A', 'text' => 'Persegi'],
                    ['key' => 'B', 'text' => 'Belah Ketupat'],
                    ['key' => 'C', 'text' => 'Persegi Panjang'],
                    ['key' => 'D', 'text' => 'Jajar Genjang'],
                ],
                'correct_answer' => ['A', 'B'],
                'default_score' => 20.0,
                'explanation' => 'Persegi dan Belah Ketupat memiliki 4 sisi yang sama panjang.',
                'is_active' => true,
            ]
        );

        $q3 = ExamQuestion::firstOrCreate(
            [
                'school_id' => $school->id,
                'content' => 'Bilangan prima genap satu-satunya dalam himpunan bilangan bulat adalah 2.',
            ],
            [
                'created_by' => $teacher->id,
                'subject_id' => $subject->id,
                'question_type' => ExamQuestion::TYPE_TRUE_FALSE,
                'options' => null,
                'correct_answer' => 'true',
                'default_score' => 15.0,
                'explanation' => 'Pernyataan benar karena hanya angka 2 yang merupakan bilangan genap sekaligus bilangan prima.',
                'is_active' => true,
            ]
        );

        $q4 = ExamQuestion::firstOrCreate(
            [
                'school_id' => $school->id,
                'content' => 'Sebuah persegi memiliki keliling 48 cm. Berapakah panjang sisi persegi tersebut (dalam cm)?',
            ],
            [
                'created_by' => $teacher->id,
                'subject_id' => $subject->id,
                'question_type' => ExamQuestion::TYPE_SHORT_ANSWER,
                'options' => null,
                'correct_answer' => '12',
                'default_score' => 20.0,
                'explanation' => 'Keliling persegi = 4 x sisi -> 48 / 4 = 12 cm.',
                'is_active' => true,
            ]
        );

        $q5 = ExamQuestion::firstOrCreate(
            [
                'school_id' => $school->id,
                'content' => 'Jelaskan perbedaan antara segitiga sama sisi dan segitiga sama kaki ditinjau dari panjang sisi dan besar sudut-sudutnya!',
            ],
            [
                'created_by' => $teacher->id,
                'subject_id' => $subject->id,
                'question_type' => ExamQuestion::TYPE_ESSAY,
                'options' => null,
                'correct_answer' => null,
                'default_score' => 25.0,
                'explanation' => 'Segitiga sama sisi memiliki 3 sisi sama panjang dan 3 sudut sama besar (60 derajat), sedangkan segitiga sama kaki hanya memiliki 2 sisi sama panjang dan 2 sudut alas yang sama besar.',
                'is_active' => true,
            ]
        );

        // 2. Create sample published exam
        $exam = Exam::firstOrCreate(
            [
                'school_id' => $school->id,
                'title' => 'Asesmen Sumatif Tengah Semester CBT - Matematika',
            ],
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'description' => 'Kerjakan ujian ini dengan jujur dan teliti. Sesi akan otomatis dikumpulkan jika waktu habis.',
                'duration_minutes' => 60,
                'start_time' => now()->subHours(2),
                'end_time' => now()->addDays(5),
                'max_attempts' => 2,
                'passing_grade' => 75.0,
                'status' => Exam::STATUS_PUBLISHED,
                'shuffle_questions' => true,
                'shuffle_options' => true,
                'show_result_immediately' => true,
                'show_explanation_policy' => Exam::EXPLANATION_AFTER_CLOSED,
                'total_score' => 100.0,
            ]
        );

        if ($classroom) {
            $exam->classrooms()->syncWithoutDetaching([$classroom->id]);
        }

        // Attach question items with custom scores
        $questions = [$q1, $q2, $q3, $q4, $q5];
        foreach ($questions as $order => $q) {
            ExamQuestionItem::firstOrCreate(
                [
                    'exam_id' => $exam->id,
                    'question_id' => $q->id,
                ],
                [
                    'order' => $order + 1,
                    'score' => $q->default_score,
                ]
            );
        }

        $exam->recalculateTotalScore();
    }
}
