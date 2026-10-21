<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\GradeLevel;
use App\Models\Pilot\PilotFeedback;
use App\Models\Pilot\PilotImportBatch;
use App\Models\School;
use App\Models\Semester;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\Pilot\PilotOnboardingImportService;
use App\Services\Pilot\PilotSupportAccessService;
use App\Services\Pilot\PilotWaveManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PilotSupportToolsTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected User $admin;

    protected User $teacher;

    protected User $parent;

    protected Classroom $classroom;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name' => 'SMA Eduvia Unggulan Surabaya',
            'code' => 'SCH-SURABAYA-01',
            'slug' => 'sma-eduvia-surabaya',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::SCHOOL_ADMIN,
            'email' => 'admin.pilot@eduvia.test',
        ]);

        $this->teacher = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::TEACHER,
            'email' => 'guru.pilot@eduvia.test',
        ]);

        $this->parent = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::PARENT,
            'email' => 'ortu.pilot@eduvia.test',
        ]);

        $this->academicYear = AcademicYear::create([
            'school_id' => $this->school->id,
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        $this->semester = Semester::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Semester Ganjil',
            'semester_type' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);

        $gradeLevel = GradeLevel::factory()->create([
            'educational_level' => 'sma',
            'level_number' => 10,
            'name' => 'Kelas 10',
        ]);

        $this->classroom = Classroom::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $gradeLevel->id,
            'name' => '10 MIPA 1',
            'code' => 'CLS-10-MIPA-1',
            'capacity' => 36,
            'is_active' => true,
        ]);
    }

    /**
     * Test 1: CSV preview parses rows and detects duplicates/validation errors.
     */
    public function test_csv_preview_and_validation(): void
    {
        $csvContent = "name,email,phone,nis,nisn,gender,birth_place,birth_date,classroom\n"
            ."Budi Prakoso,budi.p@eduvia.test,081234567891,1001,0081234567,L,Surabaya,2008-01-01,10 MIPA 1\n"
            ."Siti Rahma,siti.r@eduvia.test,081234567892,1002,0081234568,P,Surabaya,2008-02-02,10 MIPA 1\n"
            ."Budi Prakoso,budi.p@eduvia.test,081234567891,1001,0081234567,L,Surabaya,2008-01-01,10 MIPA 1\n"; // duplicate row

        $file = UploadedFile::fake()->createWithContent('students.csv', $csvContent);

        $service = app(PilotOnboardingImportService::class);
        $preview = $service->previewCsv($file, 'students', $this->school);

        $this->assertEquals(3, $preview['total_rows']);
        $this->assertEquals(2, $preview['valid_rows_count']);
        $this->assertEquals(1, $preview['error_rows_count']);
        $this->assertCount(1, $preview['errors']);
        $this->assertStringContainsString('Duplikat', $preview['errors'][0]['reason']);
    }

    /**
     * Test 2: Atomic batch import and surgical rollback.
     */
    public function test_atomic_import_and_surgical_rollback(): void
    {
        $rows = [
            [
                'name' => 'Fajar Pratama',
                'email' => 'fajar.pratama@eduvia.test',
                'phone' => '081234567800',
                'nis' => '1005',
                'nisn' => '0089999001',
                'gender' => 'L',
                'birth_place' => 'Surabaya',
                'birth_date' => '2008-05-10',
                'classroom' => '10 MIPA 1',
            ],
            [
                'name' => 'Gita Gutawa',
                'email' => 'gita.gutawa@eduvia.test',
                'phone' => '081234567801',
                'nis' => '1006',
                'nisn' => '0089999002',
                'gender' => 'P',
                'birth_place' => 'Surabaya',
                'birth_date' => '2008-06-15',
                'classroom' => '10 MIPA 1',
            ],
        ];

        $service = app(PilotOnboardingImportService::class);
        $result = $service->executeImport($rows, 'students', 'test_students.csv', $this->school, $this->admin);

        $this->assertDatabaseHas('users', ['email' => 'fajar.pratama@eduvia.test', 'school_id' => $this->school->id]);
        $this->assertDatabaseHas('users', ['email' => 'gita.gutawa@eduvia.test', 'school_id' => $this->school->id]);
        $this->assertDatabaseHas('student_profiles', ['nisn' => '0089999001', 'school_id' => $this->school->id]);
        $this->assertDatabaseHas('classroom_students', ['classroom_id' => $this->classroom->id]);

        $batchCode = $result['batch_code'];
        $batch = PilotImportBatch::where('batch_code', $batchCode)->first();
        $this->assertNotNull($batch);
        $this->assertEquals('completed', $batch->status);
        $this->assertCount(2, $batch->created_ids);

        // Surgical Rollback
        $service->rollbackBatch($batchCode, $this->school, $this->admin);

        $this->assertDatabaseMissing('users', ['email' => 'fajar.pratama@eduvia.test']);
        $this->assertDatabaseMissing('users', ['email' => 'gita.gutawa@eduvia.test']);
        $this->assertDatabaseMissing('student_profiles', ['nisn' => '0089999001']);

        $batch->refresh();
        $this->assertEquals('rolled_back', $batch->status);
        $this->assertEquals($this->admin->id, $batch->rolled_back_by);
    }

    /**
     * Test 3: Feedback submission strips PII and enables triage.
     */
    public function test_feedback_submission_and_pii_scrubbing(): void
    {
        Sanctum::actingAs($this->teacher);

        $deviceInfo = [
            'os' => 'Android 14',
            'device_model' => 'Galaxy A54',
            'user_email' => 'guru.pilot@eduvia.test',
            'user_phone' => '08123456789',
            'student_name' => 'Ananda Rizky',
        ];

        $response = $this->postJson('/api/v1/pilot/feedback', [
            'category' => 'bug',
            'severity' => 'p1',
            'title' => 'Tombol presensi tidak merespons di koneksi lambat',
            'description' => 'Saat jaringan turun ke 3G, tombol simpan presensi tidak memberikan indikasi loading.',
            'app_version' => '1.0.0-pilot',
            'device_info' => $deviceInfo,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.category', 'bug')
            ->assertJsonPath('data.severity', 'p1');

        $feedback = PilotFeedback::first();
        $this->assertNotNull($feedback);

        // Device info should be scrubbed of PII
        $this->assertArrayNotHasKey('user_email', $feedback->device_info);
        $this->assertArrayNotHasKey('user_phone', $feedback->device_info);
        $this->assertArrayNotHasKey('student_name', $feedback->device_info);
        $this->assertEquals('Galaxy A54', $feedback->device_info['device_model']);

        // Triage feedback
        Sanctum::actingAs($this->admin);
        $triageResponse = $this->putJson("/api/v1/pilot/feedbacks/{$feedback->id}/triage", [
            'status' => 'in_progress',
            'severity' => 'p1',
            'triage_notes' => 'Sedang diperbaiki dengan menambahkan optimistic UI feedback.',
            'assigned_to' => $this->admin->id,
        ]);

        $triageResponse->assertOk();
        $feedback->refresh();
        $this->assertEquals('in_progress', $feedback->status);
        $this->assertEquals($this->admin->id, $feedback->assigned_to);
    }

    /**
     * Test 4: Anonymous survey submission and weekly metrics aggregation without PII.
     */
    public function test_survey_submission_and_weekly_metrics_report(): void
    {
        Sanctum::actingAs($this->parent);

        $surveyResponse = $this->postJson('/api/v1/pilot/surveys', [
            'wave_number' => 1,
            'satisfaction_score' => 5,
            'ease_of_use_score' => 4,
            'main_obstacle' => 'Awalnya bingung dengan klaim kode anak',
            'constructive_feedback' => 'Bagus sekali ada notifikasi WhatsApp atau push notification.',
        ]);

        $surveyResponse->assertStatus(201);
        $this->assertDatabaseHas('pilot_surveys', [
            'school_id' => $this->school->id,
            'wave_number' => 1,
            'respondent_role' => 'parent',
            'satisfaction_score' => 5,
        ]);

        // Weekly Report with Zero PII
        Sanctum::actingAs($this->admin);
        $reportResponse = $this->getJson('/api/v1/pilot/metrics/weekly');
        $reportResponse->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'metadata' => ['school_id', 'school_name', 'reporting_period'],
                    'teacher_engagement',
                    'attendance_operations',
                    'parent_adoption',
                    'issues_and_triages',
                    'user_satisfaction',
                ],
            ]);
    }

    /**
     * Test 5: Pre-flight checks fail when prerequisites missing, pass when complete.
     */
    public function test_wave_preflight_checks(): void
    {
        $waveManager = app(PilotWaveManager::class);

        // Preflight Wave 1 should fail because there are no enrolled students yet
        $checkFail = $waveManager->preFlightCheck(1, $this->school->id);
        $this->assertFalse($checkFail['can_activate']);

        // Now enroll a student to classroom
        $studentUser = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::STUDENT,
        ]);
        StudentProfile::create([
            'school_id' => $this->school->id,
            'user_id' => $studentUser->id,
            'nis' => '1099',
            'nisn' => '0089999099',
            'gender' => 'L',
        ]);
        ClassroomStudent::create([
            'classroom_id' => $this->classroom->id,
            'student_id' => $studentUser->id,
            'academic_year_id' => $this->academicYear->id,
            'enrollment_status' => 'active',
            'joined_at' => now()->toDateString(),
        ]);

        // Preflight should now pass
        $checkPass = $waveManager->preFlightCheck(1, $this->school->id);
        $this->assertTrue($checkPass['can_activate']);
    }

    /**
     * Test 6: CLI commands pilot:preflight and pilot:readonly.
     */
    public function test_pilot_artisan_commands(): void
    {
        // Test pilot:readonly command
        $exitCode = Artisan::call('pilot:readonly', [
            'module' => 'attendance',
            'school' => $this->school->id,
        ]);
        $this->assertEquals(0, $exitCode);
        $this->assertTrue(app(PilotWaveManager::class)->isModuleReadOnly('attendance', $this->school->id));

        // Disable readonly
        Artisan::call('pilot:readonly', [
            'module' => 'attendance',
            'school' => $this->school->id,
            '--disable' => true,
        ]);
        $this->assertFalse(app(PilotWaveManager::class)->isModuleReadOnly('attendance', $this->school->id));
    }

    /**
     * Test 7: Time-bound PIC-authorized diagnostic access.
     */
    public function test_time_bound_support_access(): void
    {
        $service = app(PilotSupportAccessService::class);

        $log = $service->grantDiagnosticAccess(
            operator: $this->admin,
            targetUser: $this->teacher,
            authorizedByPic: 'Kepala Sekolah',
            reason: 'Investigasi kendala sinkronisasi presensi',
            durationMinutes: 30
        );

        $this->assertNotNull($log);
        $this->assertGreaterThanOrEqual(29, max(0, (int) now()->diffInMinutes($log->expires_at)));

        $diag = $service->getScrubbedDiagnostics($this->admin, $this->teacher);
        $this->assertArrayHasKey('account_status', $diag);
        $this->assertArrayHasKey('masked_email', $diag['account_status']);
        $this->assertStringContainsString('*', $diag['account_status']['masked_email']);

        $service->revokeAccess($log, $this->admin);
        $this->expectException(\RuntimeException::class);
        $service->getScrubbedDiagnostics($this->admin, $this->teacher);
    }
}
