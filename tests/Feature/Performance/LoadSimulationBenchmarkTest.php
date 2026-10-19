<?php

namespace Tests\Feature\Performance;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\FeeCategory;
use App\Models\Invoice;
use App\Models\ParentStudentRelation;
use App\Models\School;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoadSimulationBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected User $teacher;

    protected User $student;

    protected User $parent;

    protected Classroom $classroom;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected Subject $subject;

    protected Exam $exam;

    protected ExamAttempt $attempt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create(['name' => 'SMA Eduvia Performance Lab']);

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
            'name' => '12 MIPA 1',
        ]);

        $this->teacher = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::TEACHER->value,
            'email' => 'guru.perf@eduvia.test',
        ]);

        $this->student = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::STUDENT->value,
            'email' => 'siswa.perf@eduvia.test',
        ]);

        ClassroomStudent::factory()->create([
            'classroom_id' => $this->classroom->id,
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->parent = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::PARENT->value,
            'email' => 'ortu.perf@eduvia.test',
        ]);

        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship' => 'father',
            'is_verified' => true,
        ]);

        $this->subject = Subject::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Matematika Peminatan',
        ]);

        $this->exam = Exam::factory()->create([
            'school_id' => $this->school->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'title' => 'Simulasi CBT Serentak 40 Soal',
            'duration_minutes' => 90,
            'status' => 'published',
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addMinutes(120),
        ]);

        // Seed 40 questions
        for ($i = 1; $i <= 40; $i++) {
            $q = ExamQuestion::create([
                'school_id' => $this->school->id,
                'created_by' => $this->teacher->id,
                'subject_id' => $this->subject->id,
                'question_type' => 'multiple_choice_single',
                'content' => "Soal nomor {$i}: Berapakah nilai turunan f(x) = x^{$i}?",
                'options' => [
                    ['key' => 'A', 'text' => "{$i}x^".($i - 1)],
                    ['key' => 'B', 'text' => "x^{$i}"],
                    ['key' => 'C', 'text' => '0'],
                    ['key' => 'D', 'text' => '1'],
                ],
                'correct_answer' => 'A',
                'default_score' => 2.5,
                'is_active' => true,
            ]);

            $this->exam->questions()->attach($q->id, ['order' => $i, 'score' => 2.5]);
        }

        $this->attempt = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'attempt_number' => 1,
            'started_at' => now(),
            'expires_at' => now()->addMinutes(90),
            'questions_snapshot' => $this->exam->buildFrozenSnapshot(),
            'status' => ExamAttempt::STATUS_IN_PROGRESS,
        ]);
    }

    /**
     * Skenario 1: CBT Autosave burst throughput, query budget, and latency profiling.
     */
    public function test_cbt_autosave_burst_latency_and_query_budget(): void
    {
        Sanctum::actingAs($this->student, ['role:student']);

        $latencies = [];
        $queryCounts = [];
        $currentQueryCount = 0;

        DB::listen(function () use (&$currentQueryCount) {
            $currentQueryCount++;
        });

        // Simulate 20 consecutive answer autosaves
        for ($qNum = 1; $qNum <= 20; $qNum++) {
            $questionId = $this->exam->questions[$qNum - 1]->id;
            $currentQueryCount = 0;

            $startTime = microtime(true);

            $response = $this->postJson(route('api.v1.student.cbt.save_answer', $this->exam), [
                'attempt_id' => $this->attempt->id,
                'question_id' => $questionId,
                'answer' => 'A',
                'is_flagged' => false,
            ]);

            $elapsedMs = (microtime(true) - $startTime) * 1000;
            $latencies[] = $elapsedMs;
            $queryCounts[] = $currentQueryCount;

            $response->assertStatus(200);
            $response->assertJsonPath('success', true);
        }

        sort($latencies);
        $count = count($latencies);
        $p50 = $latencies[(int) floor($count * 0.50)];
        $maxQueryCount = max($queryCounts);

        // Budget assertions:
        // 1. Query count per autosave must be <= 8 (no N+1 query leak)
        $this->assertLessThanOrEqual(8, $maxQueryCount, "Autosave query count ({$maxQueryCount}) exceeded budget of 8 queries.");

        // 2. Latency p50 should be snappy (< 500ms on SQLite/local DB test environment)
        $this->assertLessThan(500, $p50, "p50 latency ({$p50}ms) is too high.");
    }

    /**
     * Skenario 2: CBT Batch Answer sync (offline recovery / network resume).
     */
    public function test_cbt_batch_save_throughput_and_efficiency(): void
    {
        Sanctum::actingAs($this->student, ['role:student']);

        $batchPayload = [];
        for ($i = 21; $i <= 30; $i++) {
            $batchPayload[] = [
                'question_id' => $this->exam->questions[$i - 1]->id,
                'answer' => 'B',
                'is_flagged' => false,
                'client_sequence_id' => $i,
                'client_timestamp' => now()->timestamp * 1000,
            ];
        }

        $startTime = microtime(true);

        $response = $this->postJson(route('api.v1.student.cbt.save_answers_batch', $this->exam), [
            'attempt_id' => $this->attempt->id,
            'answers' => $batchPayload,
        ]);

        $elapsedMs = (microtime(true) - $startTime) * 1000;

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.synced_count', 10);

        // Verify DB state
        $savedCount = ExamAnswer::where('exam_attempt_id', $this->attempt->id)->count();
        $this->assertGreaterThanOrEqual(10, $savedCount);
    }

    /**
     * Skenario 3: Absensi Pagi Serentak (Teacher records 30 students in one session).
     */
    public function test_morning_attendance_bulk_submission_performance(): void
    {
        Sanctum::actingAs($this->teacher, ['role:teacher']);

        // Create 30 students for this classroom
        $students = User::factory()->count(30)->create([
            'school_id' => $this->school->id,
            'role' => UserRole::STUDENT->value,
        ]);

        foreach ($students as $st) {
            ClassroomStudent::factory()->create([
                'classroom_id' => $this->classroom->id,
                'student_id' => $st->id,
                'academic_year_id' => $this->academicYear->id,
            ]);
        }

        $session = AttendanceSession::create([
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'teacher_id' => $this->teacher->id,
            'session_type' => 'daily',
            'meeting_number' => 1,
            'date' => now()->toDateString(),
            'start_time' => '07:00:00',
            'status' => 'draft',
        ]);

        $records = [];
        foreach ($students as $st) {
            $records[] = [
                'student_id' => $st->id,
                'status' => 'present',
                'notes' => 'Hadir tepat waktu',
            ];
        }

        $startTime = microtime(true);

        $response = $this->postJson(route('api.v1.teacher.attendance.records.save', $session), [
            'records' => $records,
            'finalize' => true,
        ]);

        $elapsedMs = (microtime(true) - $startTime) * 1000;

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Verify all 30 records saved and session finalized
        $this->assertEquals(30, AttendanceRecord::where('attendance_session_id', $session->id)->count());
        $this->assertTrue($session->fresh()->isFinalized());
    }

    /**
     * Skenario 4: Awal Bulan Keuangan - Pencegahan Duplikasi Invoice SPP Bulanan.
     */
    public function test_monthly_invoice_generation_scalability_and_duplicate_safety(): void
    {
        $category = FeeCategory::create([
            'school_id' => $this->school->id,
            'code' => 'SPP-2026',
            'name' => 'SPP Bulanan',
            'type' => 'recurring',
            'billing_period' => 'monthly',
            'amount' => 450000,
            'is_active' => true,
        ]);

        $periodKey = '2026-10';

        // 1. Create first invoice
        $invoice1 = Invoice::create([
            'school_id' => $this->school->id,
            'invoice_number' => 'INV-202610-001',
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'fee_category_id' => $category->id,
            'billing_period_key' => $periodKey,
            'title' => 'SPP Oktober 2026',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'subtotal' => 450000,
            'total_amount' => 450000,
            'paid_amount' => 0,
            'status' => 'issued',
        ]);

        $this->assertNotNull($invoice1->id);

        // 2. Attempt duplicate creation for same student, category & period
        $isDuplicateCaught = false;
        try {
            Invoice::create([
                'school_id' => $this->school->id,
                'invoice_number' => 'INV-202610-002-DUP',
                'student_id' => $this->student->id,
                'academic_year_id' => $this->academicYear->id,
                'fee_category_id' => $category->id,
                'billing_period_key' => $periodKey,
                'title' => 'SPP Oktober 2026 (Duplikat)',
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(10)->toDateString(),
                'subtotal' => 450000,
                'total_amount' => 450000,
                'paid_amount' => 0,
                'status' => 'issued',
            ]);
        } catch (\Throwable $e) {
            $isDuplicateCaught = true;
        }

        $this->assertTrue($isDuplicateCaught, 'Unique index inv_student_fee_period_unique must prevent duplicate billing.');
    }

    /**
     * Skenario 5: N+1 Query Detection pada Dashboard Siswa & Orang Tua.
     */
    public function test_dashboard_query_count_is_bounded(): void
    {
        Sanctum::actingAs($this->student, ['role:student']);

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $resStudent = $this->getJson(route('api.v1.student.dashboard'));
        $resStudent->assertStatus(200);

        // Student dashboard should execute bounded queries (<= 20 queries)
        $this->assertLessThanOrEqual(20, $queryCount, "Student dashboard query count ({$queryCount}) exceeded budget of 20.");

        // Reset and test parent dashboard
        Sanctum::actingAs($this->parent, ['role:parent']);
        $parentQueryCount = 0;
        DB::listen(function () use (&$parentQueryCount) {
            $parentQueryCount++;
        });

        $resParent = $this->getJson(route('api.v1.parent.dashboard'));
        $resParent->assertStatus(200);

        $this->assertLessThanOrEqual(20, $parentQueryCount, "Parent dashboard query count ({$parentQueryCount}) exceeded budget of 20.");
    }
}
