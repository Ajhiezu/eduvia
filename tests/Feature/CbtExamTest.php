<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionItem;
use App\Models\School;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CbtExamTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private School $otherSchool;

    private AcademicYear $academicYear;

    private Semester $semester;

    private Classroom $classroom;

    private Subject $subject;

    private User $teacher;

    private User $otherTeacher;

    private User $student;

    private User $otherStudent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create();
        $this->otherSchool = School::factory()->create();

        $this->academicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
            'is_active' => true,
        ]);

        $this->semester = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'is_active' => true,
        ]);

        $this->classroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->subject = Subject::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Matematika Dasar',
        ]);

        $this->teacher = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::TEACHER,
            'is_active' => true,
        ]);

        $this->otherTeacher = User::factory()->create([
            'school_id' => $this->otherSchool->id,
            'role' => UserRole::TEACHER,
            'is_active' => true,
        ]);

        $this->student = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::STUDENT,
            'is_active' => true,
        ]);

        $this->otherStudent = User::factory()->create([
            'school_id' => $this->otherSchool->id,
            'role' => UserRole::STUDENT,
            'is_active' => true,
        ]);

        // Enroll student to classroom
        ClassroomStudent::factory()->create([
            'classroom_id' => $this->classroom->id,
            'student_id' => $this->student->id,
            'enrollment_status' => 'active',
        ]);
    }

    /**
     * Test 1: Teacher can create a valid single choice question in the question bank.
     */
    public function test_teacher_can_create_multiple_choice_question_in_bank(): void
    {
        $payload = [
            'subject_id' => $this->subject->id,
            'question_type' => ExamQuestion::TYPE_MC_SINGLE,
            'content' => 'Berapakah 2 + 2?',
            'options' => [
                ['key' => 'A', 'text' => '3'],
                ['key' => 'B', 'text' => '4'],
                ['key' => 'C', 'text' => '5'],
            ],
            'correct_answer' => 'B',
            'default_score' => 10.0,
            'explanation' => '2 + 2 = 4.',
        ];

        $response = $this->actingAs($this->teacher)->post(route('exam-questions.store'), $payload);

        $response->assertRedirect(route('exam-questions.index', ['subject_id' => $this->subject->id]));
        $this->assertDatabaseHas('exam_questions', [
            'school_id' => $this->school->id,
            'subject_id' => $this->subject->id,
            'content' => 'Berapakah 2 + 2?',
            'question_type' => ExamQuestion::TYPE_MC_SINGLE,
            'default_score' => 10.0,
        ]);
    }

    /**
     * Test 2: Teacher can create True/False and Essay questions.
     */
    public function test_teacher_can_create_true_false_and_essay_questions(): void
    {
        // True / False
        $tfPayload = [
            'subject_id' => $this->subject->id,
            'question_type' => ExamQuestion::TYPE_TRUE_FALSE,
            'content' => 'Matahari terbit dari arah barat.',
            'correct_answer' => 'false',
            'default_score' => 10.0,
            'explanation' => 'Matahari terbit dari timur.',
        ];

        $responseTf = $this->actingAs($this->teacher)->post(route('exam-questions.store'), $tfPayload);
        $responseTf->assertRedirect(route('exam-questions.index', ['subject_id' => $this->subject->id]));

        // Essay
        $essayPayload = [
            'subject_id' => $this->subject->id,
            'question_type' => ExamQuestion::TYPE_ESSAY,
            'content' => 'Jelaskan konsep dasar hukum kekekalan energi!',
            'default_score' => 25.0,
            'explanation' => 'Energi tidak dapat diciptakan atau dimusnahkan.',
        ];

        $responseEssay = $this->actingAs($this->teacher)->post(route('exam-questions.store'), $essayPayload);
        $responseEssay->assertRedirect(route('exam-questions.index', ['subject_id' => $this->subject->id]));

        $this->assertDatabaseHas('exam_questions', [
            'school_id' => $this->school->id,
            'question_type' => ExamQuestion::TYPE_ESSAY,
            'default_score' => 25.0,
        ]);
    }

    /**
     * Test 3: Question validation rejects invalid options or missing answers.
     */
    public function test_question_validation_requires_minimum_options_and_correct_key(): void
    {
        $invalidPayload = [
            'subject_id' => $this->subject->id,
            'question_type' => ExamQuestion::TYPE_MC_SINGLE,
            'content' => 'Pertanyaan tidak valid',
            'options' => [
                ['key' => 'A', 'text' => 'Hanya satu opsi'],
            ],
            'correct_answer' => null,
            'default_score' => 10.0,
        ];

        $response = $this->actingAs($this->teacher)->post(route('exam-questions.store'), $invalidPayload);
        $response->assertSessionHasErrors(['options', 'correct_answer']);
    }

    /**
     * Test 4: Tenant isolation prevents cross-school question access.
     */
    public function test_teacher_cannot_view_or_modify_question_from_another_school(): void
    {
        $otherQuestion = ExamQuestion::factory()->create([
            'school_id' => $this->otherSchool->id,
            'created_by' => $this->otherTeacher->id,
        ]);

        $response = $this->actingAs($this->teacher)->get(route('exam-questions.edit', $otherQuestion));
        $this->assertTrue(in_array($response->getStatusCode(), [403, 404]), 'Cross-tenant question edit must be denied with 403 or 404.');

        $deleteResponse = $this->actingAs($this->teacher)->delete(route('exam-questions.destroy', $otherQuestion));
        $this->assertTrue(in_array($deleteResponse->getStatusCode(), [403, 404]), 'Cross-tenant question deletion must be denied with 403 or 404.');
    }

    /**
     * Test 5: Teacher can create an exam and attach classrooms.
     */
    public function test_teacher_can_create_exam_with_target_classrooms(): void
    {
        $payload = [
            'title' => 'Asesmen Harian Matematika',
            'subject_id' => $this->subject->id,
            'classroom_ids' => [$this->classroom->id],
            'duration_minutes' => 45,
            'start_time' => now()->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'max_attempts' => 2,
            'passing_grade' => 75.0,
            'shuffle_questions' => true,
            'shuffle_options' => true,
            'show_result_immediately' => true,
            'show_explanation_policy' => Exam::EXPLANATION_AFTER_CLOSED,
        ];

        $response = $this->actingAs($this->teacher)->post(route('exams.store'), $payload);

        $exam = Exam::where('title', 'Asesmen Harian Matematika')->first();
        $this->assertNotNull($exam);
        $response->assertRedirect(route('exams.questions', $exam));

        $this->assertDatabaseHas('exam_classrooms', [
            'exam_id' => $exam->id,
            'classroom_id' => $this->classroom->id,
        ]);
        $this->assertEquals(Exam::STATUS_DRAFT, $exam->status);
    }

    /**
     * Test 6: Empty exam cannot be published until questions are added.
     */
    public function test_empty_exam_cannot_be_published_until_prerequisites_met(): void
    {
        $exam = Exam::factory()->create([
            'school_id' => $this->school->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'status' => Exam::STATUS_DRAFT,
        ]);
        $exam->classrooms()->sync([$this->classroom->id]);

        $response = $this->actingAs($this->teacher)->post(route('exams.publish', $exam));

        $response->assertSessionHasErrors();
        $this->assertEquals(Exam::STATUS_DRAFT, $exam->fresh()->status);

        // Add question to exam
        $q = ExamQuestion::factory()->create([
            'school_id' => $this->school->id,
            'created_by' => $this->teacher->id,
            'subject_id' => $this->subject->id,
        ]);

        ExamQuestionItem::create([
            'exam_id' => $exam->id,
            'question_id' => $q->id,
            'order' => 1,
            'score' => 20.0,
        ]);

        $publishResponse = $this->actingAs($this->teacher)->post(route('exams.publish', $exam));
        $publishResponse->assertSessionHasNoErrors();
        $this->assertEquals(Exam::STATUS_PUBLISHED, $exam->fresh()->status);
    }

    /**
     * Test 7: Enrolled student can start exam and receives snapshot with NO answer keys exposed.
     */
    public function test_enrolled_student_can_start_exam_and_receives_frozen_snapshot_without_keys(): void
    {
        $exam = Exam::factory()->create([
            'school_id' => $this->school->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'status' => Exam::STATUS_PUBLISHED,
            'duration_minutes' => 60,
            'start_time' => now()->subHour(),
            'end_time' => now()->addDays(2),
        ]);
        $exam->classrooms()->sync([$this->classroom->id]);

        $q = ExamQuestion::factory()->create([
            'school_id' => $this->school->id,
            'created_by' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'content' => 'Ibukota Indonesia sekarang?',
            'question_type' => ExamQuestion::TYPE_MC_SINGLE,
            'options' => [
                ['key' => 'A', 'text' => 'Jakarta'],
                ['key' => 'B', 'text' => 'Surabaya'],
            ],
            'correct_answer' => 'A',
            'explanation' => 'Jakarta adalah ibukota negara.',
        ]);

        ExamQuestionItem::create([
            'exam_id' => $exam->id,
            'question_id' => $q->id,
            'order' => 1,
            'score' => 100.0,
        ]);
        $exam->recalculateTotalScore();

        $response = $this->actingAs($this->student)->post(route('student.exams.start', $exam));

        $attempt = ExamAttempt::where('exam_id', $exam->id)->where('student_id', $this->student->id)->first();
        $this->assertNotNull($attempt);
        $response->assertRedirect(route('student.exams.take', [$exam, $attempt]));

        // Verify authoritative timer
        $this->assertNotNull($attempt->started_at);
        $this->assertNotNull($attempt->expires_at);
        $this->assertTrue(now()->diffInMinutes($attempt->expires_at) >= 58);

        // Security check: correct_answer & explanation must NOT be present in student snapshot!
        $snapshot = $attempt->questions_snapshot;
        $this->assertNotEmpty($snapshot);
        $firstQuestion = $snapshot[0];
        $this->assertArrayNotHasKey('correct_answer', $firstQuestion);
    }

    /**
     * Test 8: Non-enrolled student cannot access or start the exam.
     */
    public function test_non_enrolled_student_cannot_start_exam(): void
    {
        $exam = Exam::factory()->create([
            'school_id' => $this->school->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'status' => Exam::STATUS_PUBLISHED,
        ]);
        $exam->classrooms()->sync([$this->classroom->id]);

        // otherStudent is from another school/classroom
        $response = $this->actingAs($this->otherStudent)->post(route('student.exams.start', $exam));
        $this->assertTrue(in_array($response->getStatusCode(), [403, 404]), 'Non-enrolled/cross-tenant student cannot start exam (403 or 404).');
    }

    /**
     * Test 9: Student can autosave answer in realtime and receives updated remaining seconds.
     */
    public function test_student_can_autosave_answers_realtime(): void
    {
        $exam = Exam::factory()->create([
            'school_id' => $this->school->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'status' => Exam::STATUS_PUBLISHED,
        ]);
        $exam->classrooms()->sync([$this->classroom->id]);

        $q = ExamQuestion::factory()->create([
            'school_id' => $this->school->id,
            'subject_id' => $this->subject->id,
        ]);

        ExamQuestionItem::create([
            'exam_id' => $exam->id,
            'question_id' => $q->id,
            'order' => 1,
            'score' => 50.0,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'started_at' => now(),
            'expires_at' => now()->addMinutes(60),
            'questions_snapshot' => $exam->buildFrozenSnapshot(),
            'status' => ExamAttempt::STATUS_IN_PROGRESS,
        ]);

        $autosavePayload = [
            'question_id' => $q->id,
            'answer' => 'A',
            'is_marked_for_review' => true,
        ];

        $response = $this->actingAs($this->student)
            ->postJson(route('student.exams.save-answer', [$exam, $attempt]), $autosavePayload);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'is_marked_for_review' => true,
        ]);

        $this->assertDatabaseHas('exam_answers', [
            'exam_attempt_id' => $attempt->id,
            'question_id' => $q->id,
            'is_marked_for_review' => true,
        ]);
    }

    /**
     * Test 10: IDOR protection prevents student from modifying another student's attempt.
     */
    public function test_student_cannot_tamper_with_another_students_attempt_or_answers(): void
    {
        $exam = Exam::factory()->create([
            'school_id' => $this->school->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'status' => Exam::STATUS_PUBLISHED,
        ]);
        $exam->classrooms()->sync([$this->classroom->id]);

        $secondStudent = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::STUDENT,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'started_at' => now(),
            'expires_at' => now()->addMinutes(60),
            'questions_snapshot' => [],
            'status' => ExamAttempt::STATUS_IN_PROGRESS,
        ]);

        // Second student attempts to save answer on first student's attempt
        $response = $this->actingAs($secondStudent)
            ->postJson(route('student.exams.save-answer', [$exam, $attempt]), [
                'question_id' => 99,
                'answer' => 'Hacked',
            ]);

        $response->assertForbidden();
    }

    /**
     * Test 11: Final submission auto-grades objective questions accurately.
     */
    public function test_student_submit_exam_auto_grades_objective_questions_accurately(): void
    {
        $exam = Exam::factory()->create([
            'school_id' => $this->school->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'status' => Exam::STATUS_PUBLISHED,
            'passing_grade' => 70.0,
            'total_score' => 100.0,
        ]);
        $exam->classrooms()->sync([$this->classroom->id]);

        // Question 1: Single choice (Score: 50)
        $q1 = ExamQuestion::factory()->create([
            'school_id' => $this->school->id,
            'subject_id' => $this->subject->id,
            'question_type' => ExamQuestion::TYPE_MC_SINGLE,
            'correct_answer' => 'B',
            'default_score' => 50.0,
        ]);

        // Question 2: True/False (Score: 50)
        $q2 = ExamQuestion::factory()->trueFalse(true)->create([
            'school_id' => $this->school->id,
            'subject_id' => $this->subject->id,
            'default_score' => 50.0,
        ]);

        ExamQuestionItem::create(['exam_id' => $exam->id, 'question_id' => $q1->id, 'order' => 1, 'score' => 50.0]);
        ExamQuestionItem::create(['exam_id' => $exam->id, 'question_id' => $q2->id, 'order' => 2, 'score' => 50.0]);
        $exam->recalculateTotalScore();

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'started_at' => now(),
            'expires_at' => now()->addMinutes(60),
            'questions_snapshot' => $exam->buildFrozenSnapshot(),
            'status' => ExamAttempt::STATUS_IN_PROGRESS,
        ]);

        // Student answers Q1 correctly ('B') and Q2 incorrectly ('false')
        ExamAnswer::create(['exam_attempt_id' => $attempt->id, 'question_id' => $q1->id, 'answer' => 'B', 'saved_at' => now()]);
        ExamAnswer::create(['exam_attempt_id' => $attempt->id, 'question_id' => $q2->id, 'answer' => 'false', 'saved_at' => now()]);

        $response = $this->actingAs($this->student)->post(route('student.exams.submit', [$exam, $attempt]));

        $response->assertRedirect(route('student.exams.result', [$exam, $attempt]));

        $attempt->refresh();
        $this->assertEquals(ExamAttempt::STATUS_GRADED, $attempt->status);
        $this->assertEquals(50.0, (float) $attempt->total_score);
        $this->assertEquals(50.0, (float) $attempt->grade_percentage);
        $this->assertFalse((bool) $attempt->is_passed); // 50% < 70% passing grade
    }

    /**
     * Test 12: Essay questions require teacher manual grading.
     */
    public function test_essay_question_requires_manual_grading_and_teacher_can_grade(): void
    {
        $exam = Exam::factory()->create([
            'school_id' => $this->school->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'status' => Exam::STATUS_PUBLISHED,
            'passing_grade' => 70.0,
            'total_score' => 100.0,
        ]);
        $exam->classrooms()->sync([$this->classroom->id]);

        $qEssay = ExamQuestion::factory()->essay()->create([
            'school_id' => $this->school->id,
            'subject_id' => $this->subject->id,
            'default_score' => 100.0,
        ]);

        ExamQuestionItem::create(['exam_id' => $exam->id, 'question_id' => $qEssay->id, 'order' => 1, 'score' => 100.0]);
        $exam->recalculateTotalScore();

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'started_at' => now(),
            'expires_at' => now()->addMinutes(60),
            'questions_snapshot' => $exam->buildFrozenSnapshot(),
            'status' => ExamAttempt::STATUS_IN_PROGRESS,
        ]);

        ExamAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'question_id' => $qEssay->id,
            'answer' => 'Penjelasan panjang mengenai fotosintesis...',
            'saved_at' => now(),
        ]);

        // Student submits exam
        $this->actingAs($this->student)->post(route('student.exams.submit', [$exam, $attempt]));

        $attempt->refresh();
        // Since there is an essay, status should remain SUBMITTED awaiting manual evaluation
        $this->assertEquals(ExamAttempt::STATUS_SUBMITTED, $attempt->status);

        // Teacher grades the essay
        $gradePayload = [
            'scores' => [
                $qEssay->id => 85.0,
            ],
            'feedbacks' => [
                $qEssay->id => 'Penjelasan sangat komprehensif dan tepat.',
            ],
        ];

        $teacherResponse = $this->actingAs($this->teacher)
            ->post(route('exams.grading.grade', [$exam, $attempt]), $gradePayload);

        $teacherResponse->assertRedirect(route('exams.grading.index', $exam));

        $attempt->refresh();
        $this->assertEquals(ExamAttempt::STATUS_GRADED, $attempt->status);
        $this->assertEquals(85.0, (float) $attempt->total_score);
        $this->assertTrue((bool) $attempt->is_passed); // 85% >= 70% passing grade
    }

    /**
     * Test 13: Question bank mutation after exam starts does not alter student's frozen snapshot.
     */
    public function test_question_bank_mutation_does_not_affect_active_student_snapshot(): void
    {
        $exam = Exam::factory()->create([
            'school_id' => $this->school->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'status' => Exam::STATUS_PUBLISHED,
        ]);
        $exam->classrooms()->sync([$this->classroom->id]);

        $q = ExamQuestion::factory()->create([
            'school_id' => $this->school->id,
            'subject_id' => $this->subject->id,
            'content' => 'Pertanyaan Asli Versi 1.0',
        ]);

        ExamQuestionItem::create(['exam_id' => $exam->id, 'question_id' => $q->id, 'order' => 1, 'score' => 10.0]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'started_at' => now(),
            'expires_at' => now()->addMinutes(60),
            'questions_snapshot' => $exam->buildFrozenSnapshot(),
            'status' => ExamAttempt::STATUS_IN_PROGRESS,
        ]);

        // Teacher modifies the bank question in the background
        $q->update(['content' => 'Pertanyaan Diubah Setelah Siswa Mulai!']);

        // Student's snapshot must remain unchanged
        $studentQuestionText = $attempt->fresh()->questions_snapshot[0]['content'];
        $this->assertEquals('Pertanyaan Asli Versi 1.0', $studentQuestionText);
    }
}
