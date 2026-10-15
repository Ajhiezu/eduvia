<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Semester;
use App\Models\StudentProfile;
use App\Models\StudentReportCard;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AcademicAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected School $otherSchool;

    protected AcademicYear $academicYear;

    protected AcademicYear $nextAcademicYear;

    protected Semester $semester;

    protected GradeLevel $gradeLevel;

    protected Classroom $classroom;

    protected Classroom $targetClassroom;

    protected User $admin;

    protected User $otherAdmin;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup test schools
        $this->school = School::factory()->create([
            'name' => 'SMA Eduvia Nusantara',
            'slug' => 'sma-eduvia-nusantara',
            'educational_levels' => ['sma'],
        ]);

        $this->otherSchool = School::factory()->create([
            'name' => 'SMK Bina Teknologi',
            'slug' => 'smk-bina-teknologi',
            'educational_levels' => ['smk'],
        ]);

        // Academic Years
        $this->academicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
            'name' => '2025/2026',
            'start_date' => '2025-07-15',
            'end_date' => '2026-06-20',
            'is_active' => true,
        ]);

        $this->nextAcademicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
            'name' => '2026/2027',
            'start_date' => '2026-07-15',
            'end_date' => '2027-06-20',
            'is_active' => false,
        ]);

        $this->semester = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Genap',
            'is_active' => true,
        ]);

        $this->gradeLevel = GradeLevel::firstOrCreate(
            ['educational_level' => 'sma', 'level_number' => 10],
            ['name' => 'Kelas 10', 'order' => 10, 'is_active' => true]
        );

        $gradeLevel11 = GradeLevel::firstOrCreate(
            ['educational_level' => 'sma', 'level_number' => 11],
            ['name' => 'Kelas 11', 'order' => 11, 'is_active' => true]
        );

        // Classrooms
        $this->classroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'name' => 'X-MIPA-1',
            'capacity' => 36,
            'is_active' => true,
        ]);

        $this->targetClassroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->nextAcademicYear->id,
            'grade_level_id' => $gradeLevel11->id,
            'name' => 'XI-MIPA-1',
            'capacity' => 36,
            'is_active' => true,
        ]);

        // Users
        $this->admin = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::SCHOOL_ADMIN,
        ]);

        $this->otherAdmin = User::factory()->create([
            'school_id' => $this->otherSchool->id,
            'role' => UserRole::SCHOOL_ADMIN,
        ]);

        $this->student = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::STUDENT,
        ]);

        StudentProfile::create([
            'user_id' => $this->student->id,
            'school_id' => $this->school->id,
            'nis' => '20251001',
            'nisn' => '0081234567',
            'student_status' => 'active',
            'gender' => 'male',
        ]);

        $this->classroom->addStudent($this->student->id, now()->toDateString());
    }

    public function test_school_admin_can_view_student_list_scoped_to_school(): void
    {
        // Student in other school
        $otherStudent = User::factory()->create([
            'school_id' => $this->otherSchool->id,
            'name' => 'Siswa Sekolah Lain',
            'role' => UserRole::STUDENT,
        ]);
        StudentProfile::create([
            'user_id' => $otherStudent->id,
            'school_id' => $this->otherSchool->id,
            'nis' => '99999999',
            'student_status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('students.index'));

        $response->assertOk()
            ->assertSee($this->student->name)
            ->assertDontSee('Siswa Sekolah Lain');
    }

    public function test_school_admin_cannot_access_student_detail_from_other_school(): void
    {
        $otherStudent = User::factory()->create([
            'school_id' => $this->otherSchool->id,
            'role' => UserRole::STUDENT,
        ]);
        StudentProfile::create([
            'user_id' => $otherStudent->id,
            'school_id' => $this->otherSchool->id,
            'student_status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('students.show', $otherStudent));

        $response->assertStatus(403);
    }

    public function test_can_create_student_with_valid_profile_and_initial_enrollment(): void
    {
        $payload = [
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@siswa.sch.id',
            'phone' => '08123456789',
            'nis' => '20251099',
            'nisn' => '0089999999',
            'gender' => 'male',
            'birth_place' => 'Surabaya',
            'birth_date' => '2009-03-15',
            'religion' => 'Islam',
            'address' => 'Jl. Pahlawan No. 10',
            'parent_name' => 'Santoso',
            'parent_phone' => '08123456780',
            'parent_relationship' => 'Ayah',
            'student_status' => 'active',
            'enrollment_date' => now()->toDateString(),
            'classroom_id' => $this->classroom->id,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('students.store'), $payload);

        $createdUser = User::where('email', 'budi.santoso@siswa.sch.id')->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals($this->school->id, $createdUser->school_id);
        $this->assertEquals(UserRole::STUDENT, $createdUser->role);

        $response->assertRedirect(route('students.show', $createdUser));

        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $createdUser->id,
            'school_id' => $this->school->id,
            'nis' => '20251099',
            'nisn' => '0089999999',
        ]);

        $this->assertDatabaseHas('classroom_students', [
            'classroom_id' => $this->classroom->id,
            'student_id' => $createdUser->id,
            'enrollment_status' => 'active',
        ]);
    }

    public function test_student_destroy_prevented_if_enrollment_history_exists(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('students.destroy', $this->student));

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('users', ['id' => $this->student->id]);
    }

    public function test_student_status_lifecycle_creates_audit_history(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('students.status.update', $this->student), [
                'status' => 'transferred',
                'effective_date' => '2026-01-10',
                'reason' => 'Pindah domisili mengikuti tugas dinas orang tua.',
            ]);

        $response->assertRedirect(route('students.show', $this->student));

        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $this->student->id,
            'student_status' => 'transferred',
        ]);

        $this->assertDatabaseHas('student_status_histories', [
            'student_id' => $this->student->id,
            'previous_status' => 'active',
            'new_status' => 'transferred',
            'reason' => 'Pindah domisili mengikuti tugas dinas orang tua.',
            'changed_by' => $this->admin->id,
        ]);
    }

    public function test_student_import_csv_preview_and_validation(): void
    {
        $csvContent = "nama_lengkap,email,nis,nisn,jenis_kelamin,tempat_lahir,tanggal_lahir,nama_orang_tua,telepon_orang_tua,telepon\n";
        $csvContent .= "Citra Dewi,citra@siswa.sch.id,20252001,0082222222,female,Bandung,2009-04-12,Dewi Sartika,081299998888,081299998889\n";
        $csvContent .= "Invalid Email,not-an-email,20252002,0082222223,male,Jakarta,2009-01-01,Hasan,081299998880,081299998881\n";

        $file = UploadedFile::fake()->createWithContent('students.csv', $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('students.import.preview'), [
                'file' => $file,
                'classroom_id' => $this->classroom->id,
            ]);

        $response->assertOk()
            ->assertViewHas('validCount', 1)
            ->assertViewHas('invalidCount', 1);
    }

    public function test_student_import_csv_process_in_transaction(): void
    {
        $importPayload = [
            [
                'name' => 'Fajar Pratama',
                'email' => 'fajar.pratama@siswa.sch.id',
                'nis' => '20253001',
                'nisn' => '0083333331',
                'gender' => 'male',
                'birth_place' => 'Malang',
                'birth_date' => '2009-07-20',
                'parent_name' => 'Pratama',
                'parent_phone' => '081288887777',
                'phone' => '081288887778',
                'errors' => [],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('students.import.process'), [
                'students' => json_encode($importPayload),
                'classroom_id' => $this->classroom->id,
            ]);

        $response->assertRedirect(route('students.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'fajar.pratama@siswa.sch.id',
            'school_id' => $this->school->id,
            'role' => UserRole::STUDENT->value,
        ]);

        $newUser = User::where('email', 'fajar.pratama@siswa.sch.id')->first();
        $this->assertDatabaseHas('classroom_students', [
            'classroom_id' => $this->classroom->id,
            'student_id' => $newUser->id,
            'enrollment_status' => 'active',
        ]);
    }

    public function test_student_export_csv_scoped_to_tenant(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('students.export.csv'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }

    public function test_subject_master_crud_with_sma_smk_program_and_jp_allocation(): void
    {
        $payload = [
            'name' => 'Fisika Peminatan MIPA',
            'code' => 'FIS-10',
            'group' => 'Peminatan',
            'curriculum' => 'merdeka',
            'educational_level' => 'sma',
            'credit_hours' => 3,
            'academic_program' => 'MIPA',
            'order' => 1,
            'is_active' => 1,
            'description' => 'Mata pelajaran peminatan sains untuk jenjang SMA.',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('subjects.store'), $payload);

        $response->assertRedirect(route('subjects.index'));

        $this->assertDatabaseHas('subjects', [
            'school_id' => $this->school->id,
            'name' => 'Fisika Peminatan MIPA',
            'code' => 'FIS-10',
            'group' => 'Peminatan',
            'academic_program' => 'MIPA',
            'credit_hours' => 3,
        ]);

        $subject = Subject::where('code', 'FIS-10')->first();

        // Update
        $updateResponse = $this->actingAs($this->admin)
            ->put(route('subjects.update', $subject), array_merge($payload, [
                'name' => 'Fisika Tingkat Lanjut MIPA',
                'credit_hours' => 4,
            ]));

        $updateResponse->assertRedirect(route('subjects.index'));
        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'name' => 'Fisika Tingkat Lanjut MIPA',
            'credit_hours' => 4,
        ]);
    }

    public function test_cannot_delete_subject_with_existing_teaching_assignments(): void
    {
        $subject = Subject::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Matematika Wajib',
        ]);

        $teacher = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::TEACHER,
        ]);

        TeachingAssignment::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'hours_per_week' => 4,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('subjects.destroy', $subject));

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('subjects', ['id' => $subject->id]);
    }

    public function test_subject_tenant_isolation(): void
    {
        $otherSubject = Subject::factory()->create([
            'school_id' => $this->otherSchool->id,
            'name' => 'Pemrograman Berorientasi Objek',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('subjects.edit', $otherSubject));

        $response->assertStatus(403);
    }

    public function test_batch_promotion_preview_includes_attendance_cbt_and_report_card(): void
    {
        // Add attendance record
        $teacher = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::TEACHER,
        ]);

        $subject = Subject::factory()->create(['school_id' => $this->school->id]);

        $assignment = TeachingAssignment::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
        ]);

        $session = AttendanceSession::create([
            'school_id' => $this->school->id,
            'teacher_id' => $teacher->id,
            'teaching_assignment_id' => $assignment->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'classroom_id' => $this->classroom->id,
            'date' => now()->toDateString(),
            'status' => 'finalized',
        ]);

        AttendanceRecord::create([
            'attendance_session_id' => $session->id,
            'student_id' => $this->student->id,
            'status' => 'present',
        ]);

        // Add CBT attempt
        $exam = Exam::factory()->create([
            'school_id' => $this->school->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
        ]);

        ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'status' => 'graded',
            'total_score' => 88.5,
            'final_percentage' => 88.5,
        ]);

        // Add published Report Card
        StudentReportCard::create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'status' => StudentReportCard::STATUS_PUBLISHED,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('students.promotion.index', ['source_classroom_id' => $this->classroom->id]));

        $response->assertOk()
            ->assertSee('100%') // Attendance
            ->assertSee('1 ujian selesai') // CBT
            ->assertSee('Diterbitkan'); // Report Card
    }

    public function test_batch_progression_can_promote_students_to_target_classroom(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('students.promotion.process'), [
                'action' => 'promote',
                'source_classroom_id' => $this->classroom->id,
                'target_classroom_id' => $this->targetClassroom->id,
                'target_academic_year_id' => $this->nextAcademicYear->id,
                'effective_date' => '2026-07-15',
                'student_ids' => [$this->student->id],
                'notes' => 'Naik ke kelas XI berdasarkan hasil rapat pleno dewan guru.',
            ]);

        $response->assertRedirect(route('students.promotion.index', ['source_classroom_id' => $this->classroom->id]));

        // Old enrollment should be completed
        $this->assertDatabaseHas('classroom_students', [
            'classroom_id' => $this->classroom->id,
            'student_id' => $this->student->id,
            'enrollment_status' => 'completed',
        ]);

        // New enrollment should be active
        $this->assertDatabaseHas('classroom_students', [
            'classroom_id' => $this->targetClassroom->id,
            'student_id' => $this->student->id,
            'enrollment_status' => 'active',
        ]);
    }

    public function test_batch_progression_can_retain_students(): void
    {
        $repeatClassroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->nextAcademicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'name' => 'X-MIPA-2',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('students.promotion.process'), [
                'action' => 'retain',
                'source_classroom_id' => $this->classroom->id,
                'target_classroom_id' => $repeatClassroom->id,
                'effective_date' => '2026-07-15',
                'student_ids' => [$this->student->id],
                'notes' => 'Tinggal kelas untuk perbaikan kompetensi dasar.',
            ]);

        $response->assertRedirect(route('students.promotion.index', ['source_classroom_id' => $this->classroom->id]));

        $this->assertDatabaseHas('classroom_students', [
            'classroom_id' => $repeatClassroom->id,
            'student_id' => $this->student->id,
            'enrollment_status' => 'active',
        ]);
    }

    public function test_batch_progression_can_transfer_out_students(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('students.promotion.process'), [
                'action' => 'transfer_out',
                'source_classroom_id' => $this->classroom->id,
                'effective_date' => '2026-06-30',
                'student_ids' => [$this->student->id],
                'notes' => 'Mutasi pindah ke sekolah lain.',
            ]);

        $response->assertRedirect(route('students.promotion.index', ['source_classroom_id' => $this->classroom->id]));

        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $this->student->id,
            'student_status' => 'transferred',
        ]);
    }

    public function test_batch_progression_can_graduate_students(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('students.promotion.process'), [
                'action' => 'graduate',
                'source_classroom_id' => $this->classroom->id,
                'effective_date' => '2026-06-25',
                'student_ids' => [$this->student->id],
                'notes' => 'Lulus dan menyelesaikan seluruh program studi.',
            ]);

        $response->assertRedirect(route('students.promotion.index', ['source_classroom_id' => $this->classroom->id]));

        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $this->student->id,
            'student_status' => 'graduated',
        ]);
    }

    public function test_batch_progression_rejects_cross_school_classrooms(): void
    {
        $otherClassroom = Classroom::factory()->create([
            'school_id' => $this->otherSchool->id,
            'name' => 'X-TKJ-1',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('students.promotion.process'), [
                'action' => 'promote',
                'source_classroom_id' => $otherClassroom->id,
                'target_classroom_id' => $this->targetClassroom->id,
                'target_academic_year_id' => $this->nextAcademicYear->id,
                'effective_date' => '2026-07-15',
                'student_ids' => [$this->student->id],
            ]);

        $response->assertStatus(403);
    }
}
