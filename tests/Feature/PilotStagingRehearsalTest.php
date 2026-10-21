<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\Pilot\PilotImportBatch;
use App\Models\School;
use App\Models\Semester;
use App\Models\User;
use App\Services\Pilot\PilotOnboardingImportService;
use App\Services\Pilot\PilotWaveManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PilotStagingRehearsalTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected User $admin;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected array $classrooms = [];

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Pilot School Environment
        $this->school = School::create([
            'name' => 'SMA Eduvia Unggulan Surabaya',
            'code' => 'SCH-SURABAYA-01',
            'slug' => 'sma-eduvia-surabaya',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::SCHOOL_ADMIN,
            'email' => 'admin.tu@eduvia.test',
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

        $grade10 = GradeLevel::factory()->create(['level_number' => 10, 'educational_level' => 'sma', 'name' => 'Kelas 10']);
        $grade11 = GradeLevel::factory()->create(['level_number' => 11, 'educational_level' => 'sma', 'name' => 'Kelas 11']);
        $grade12 = GradeLevel::factory()->create(['level_number' => 12, 'educational_level' => 'sma', 'name' => 'Kelas 12']);

        // Create 6 classrooms for simulation
        $classNames = ['10 MIPA 1', '10 MIPA 2', '10 IPS 1', '11 MIPA 1', '11 IPS 1', '12 MIPA 1'];
        foreach ($classNames as $idx => $cName) {
            $grade = str_starts_with($cName, '10') ? $grade10 : (str_starts_with($cName, '11') ? $grade11 : $grade12);
            $this->classrooms[$cName] = Classroom::create([
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id,
                'grade_level_id' => $grade->id,
                'name' => $cName,
                'code' => 'CLS-'.($idx + 1),
                'capacity' => 36,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Rehearsal 1: Onboarding with Dirty Data (Duplicates, Missing Classroom, Invalid Email).
     * System must strictly catch all anomalies and refuse to import unclean data.
     */
    public function test_dirty_data_rehearsal_rejection(): void
    {
        $dirtyCsv = "name,email,phone,nis,nisn,gender,birth_place,birth_date,classroom\n"
            ."Siswa Valid Satu,valid1@eduvia.test,0812000001,1001,0081000001,L,Surabaya,2008-01-01,10 MIPA 1\n"
            ."Siswa Email Rusak,bukan_email,0812000002,1002,0081000002,P,Surabaya,2008-01-02,10 MIPA 1\n" // Error: invalid email
            ."Siswa Kelas Fiktif,fiktif@eduvia.test,0812000003,1003,0081000003,L,Surabaya,2008-01-03,Kelas Robot\n" // Error: classroom not found
            ."Siswa Valid Dua,valid2@eduvia.test,0812000004,1004,0081000004,P,Surabaya,2008-01-04,10 MIPA 1\n"
            ."Siswa Duplikat NISN,duplikat@eduvia.test,0812000005,1005,0081000001,L,Surabaya,2008-01-05,10 MIPA 1\n"; // Error: Duplicate NISN with row 2

        $file = UploadedFile::fake()->createWithContent('dirty_rehearsal_students.csv', $dirtyCsv);

        $service = app(PilotOnboardingImportService::class);
        $preview = $service->previewCsv($file, 'students', $this->school);

        $this->assertEquals(5, $preview['total_rows']);
        $this->assertEquals(2, $preview['valid_rows_count']);
        $this->assertEquals(3, $preview['error_rows_count']);
        $this->assertFalse($preview['can_proceed']);
        $this->assertCount(3, $preview['errors']);
    }

    /**
     * Rehearsal 2: Full Scale Simulation with 60 Clean Synthetic Students + Surgical Rollback.
     */
    public function test_full_scale_synthetic_import_and_surgical_rollback(): void
    {
        $cleanRows = [];
        for ($i = 1; $i <= 60; $i++) {
            $classKey = array_keys($this->classrooms)[$i % count($this->classrooms)];
            $cleanRows[] = [
                'name' => "Siswa Rehearsal {$i}",
                'email' => "siswa.rehearsal.{$i}@eduvia.test",
                'phone' => '0812'.str_pad((string) $i, 8, '0', STR_PAD_LEFT),
                'nis' => (string) (1000 + $i),
                'nisn' => '008'.str_pad((string) $i, 7, '0', STR_PAD_LEFT),
                'gender' => $i % 2 === 0 ? 'P' : 'L',
                'birth_place' => 'Surabaya',
                'birth_date' => '2008-05-15',
                'classroom' => $classKey,
            ];
        }

        $service = app(PilotOnboardingImportService::class);
        $importResult = $service->executeImport(
            $cleanRows,
            'students',
            'simulasi_60_siswa.csv',
            $this->school,
            $this->admin
        );

        $this->assertEquals(60, $importResult['total_imported']);
        $this->assertEquals(0, $importResult['reconciliation_report']['discrepancy']);

        $batchCode = $importResult['batch_code'];
        $batch = PilotImportBatch::where('batch_code', $batchCode)->first();
        $this->assertNotNull($batch);
        $this->assertEquals('completed', $batch->status);
        $this->assertCount(60, $batch->created_ids);

        // Verify database state: exactly 60 students created for this school
        $this->assertEquals(60, User::where('school_id', $this->school->id)->where('role', UserRole::STUDENT)->count());

        // Perform Surgical Rollback Rehearsal
        $service->rollbackBatch($batchCode, $this->school, $this->admin);

        // Verify zero residual students left after rollback
        $this->assertEquals(0, User::where('school_id', $this->school->id)->where('role', UserRole::STUDENT)->count());
        $batch->refresh();
        $this->assertEquals('rolled_back', $batch->status);
    }

    /**
     * Rehearsal 3: CLI Pre-Flight Verification & Wave Activation Simulation.
     */
    public function test_cli_wave1_preflight_and_activation(): void
    {
        // 1. Create a teacher
        User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::TEACHER,
            'is_active' => true,
        ]);

        // 2. Preflight should fail because no enrolled students yet
        $exitPreflightFail = Artisan::call('pilot:preflight', [
            'wave' => 1,
            'school' => $this->school->id,
        ]);
        $this->assertEquals(1, $exitPreflightFail);

        // 3. Enroll 1 student to classroom
        $student = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::STUDENT,
            'is_active' => true,
        ]);
        $this->classrooms['10 MIPA 1']->students()->create([
            'student_id' => $student->id,
            'academic_year_id' => $this->academicYear->id,
            'enrollment_status' => 'active',
            'joined_at' => now()->toDateString(),
        ]);

        // 4. Preflight should now succeed
        $exitPreflightPass = Artisan::call('pilot:preflight', [
            'wave' => 1,
            'school' => $this->school->id,
        ]);
        $this->assertEquals(0, $exitPreflightPass);

        // 5. Activate Wave 1
        $exitActivate = Artisan::call('pilot:activate-wave', [
            'wave' => 1,
            'school' => $this->school->id,
        ]);
        $this->assertEquals(0, $exitActivate);
        $this->assertTrue(app(PilotWaveManager::class)->isWaveActive(1, $this->school->id));

        // 6. Test Rollback Wave 1
        $exitRollback = Artisan::call('pilot:rollback-wave', [
            'wave' => 1,
            'school' => $this->school->id,
        ]);
        $this->assertEquals(0, $exitRollback);
        $this->assertFalse(app(PilotWaveManager::class)->isWaveActive(1, $this->school->id));
    }
}
