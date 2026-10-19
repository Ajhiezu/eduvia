<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private AcademicYear $academicYear;

    private GradeLevel $gradeLevel;

    private Classroom $classroom;

    private User $admin;

    private User $teacher;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create(['name' => 'SMP Eduvia Utama']);
        $this->academicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
            'name' => '2026/2027',
            'is_active' => true,
        ]);
        $this->gradeLevel = GradeLevel::factory()->create([
            'educational_level' => 'smp',
            'level_number' => 7,
            'name' => 'Kelas 7',
        ]);
        $this->classroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'name' => 'VII-A',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);
        $this->teacher = User::factory()->create(['role' => UserRole::TEACHER]);
        $this->student = User::factory()->create(['role' => UserRole::STUDENT]);
    }

    public function test_guest_and_unauthorized_roles_cannot_access_student_management(): void
    {
        $this->get(route('students.index'))->assertRedirect(route('login'));
        $this->actingAs($this->student)->get(route('students.index'))->assertStatus(403);
        $this->actingAs($this->teacher)->get(route('students.index'))->assertStatus(403);

        $this->actingAs($this->student)->get(route('students.promotion.index'))->assertStatus(403);
        $this->actingAs($this->student)->get(route('students.report.index'))->assertStatus(403);
    }

    public function test_admin_can_view_student_list_and_search(): void
    {
        $targetUser = User::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@eduvia.test',
            'role' => UserRole::STUDENT,
        ]);

        StudentProfile::create([
            'user_id' => $targetUser->id,
            'school_id' => $this->school->id,
            'nis' => '202607001',
            'nisn' => '0089991234',
            'gender' => 'male',
            'student_status' => 'active',
            'enrollment_date' => '2026-07-15',
        ]);

        $this->classroom->addStudent($targetUser);

        $response = $this->actingAs($this->admin)->get(route('students.index'));
        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertSee('202607001');

        // Test search filter
        $searchResponse = $this->actingAs($this->admin)->get(route('students.index', ['search' => 'Budi']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Budi Santoso');

        $emptySearchResponse = $this->actingAs($this->admin)->get(route('students.index', ['search' => 'NonExistentName']));
        $emptySearchResponse->assertStatus(200);
        $emptySearchResponse->assertDontSee('Budi Santoso');
    }

    public function test_admin_can_view_student_profile_detail(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Citra Lestari',
            'email' => 'citra@eduvia.test',
            'role' => UserRole::STUDENT,
        ]);

        StudentProfile::create([
            'user_id' => $targetUser->id,
            'school_id' => $this->school->id,
            'nis' => '202607002',
            'nisn' => '0089991235',
            'gender' => 'female',
            'birth_place' => 'Bandung',
            'birth_date' => '2012-05-10',
            'student_status' => 'active',
            'parent_name' => 'Bambang Lestari',
            'parent_phone' => '081234567890',
        ]);

        $this->classroom->addStudent($targetUser);

        $response = $this->actingAs($this->admin)->get(route('students.show', $targetUser));
        $response->assertStatus(200);
        $response->assertSee('Citra Lestari');
        $response->assertSee('202607002');
        $response->assertSee('0089991235');
        $response->assertSee('Bambang Lestari');
        $response->assertSee('VII-A');
    }

    public function test_admin_can_create_new_student_with_profile_and_classroom_assignment(): void
    {
        $payload = [
            'name' => 'Dewi Sartika',
            'email' => 'dewi.sartika@eduvia.test',
            'password' => 'Password123!',
            'nis' => '202607003',
            'nisn' => '0089991236',
            'gender' => 'female',
            'birth_place' => 'Yogyakarta',
            'birth_date' => '2012-08-20',
            'religion' => 'Islam',
            'address' => 'Jl. Malioboro No. 12',
            'parent_name' => 'Raden Sartika',
            'parent_phone' => '081987654321',
            'parent_relationship' => 'Ayah',
            'student_status' => 'active',
            'enrollment_date' => '2026-07-15',
            'classroom_id' => $this->classroom->id,
        ];

        $response = $this->actingAs($this->admin)->post(route('students.store'), $payload);

        $createdUser = User::where('email', 'dewi.sartika@eduvia.test')->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals(UserRole::STUDENT, $createdUser->role);

        $response->assertRedirect(route('students.show', $createdUser));
        $response->assertSessionHas('status');

        $profile = StudentProfile::where('user_id', $createdUser->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('202607003', $profile->nis);
        $this->assertEquals('0089991236', $profile->nisn);
        $this->assertEquals('active', $profile->student_status);

        $this->assertDatabaseHas('classroom_students', [
            'classroom_id' => $this->classroom->id,
            'student_id' => $createdUser->id,
            'enrollment_status' => 'active',
        ]);
    }

    public function test_validation_prevents_duplicate_nis_and_nisn(): void
    {
        $existing = User::factory()->create(['role' => UserRole::STUDENT]);
        StudentProfile::create([
            'user_id' => $existing->id,
            'school_id' => $this->school->id,
            'nis' => '202607009',
            'nisn' => '0089999999',
            'student_status' => 'active',
        ]);

        $payload = [
            'name' => 'Siswa Duplikat',
            'email' => 'duplikat@eduvia.test',
            'password' => 'Password123!',
            'nis' => '202607009', // Duplicate NIS
            'nisn' => '0089999999', // Duplicate NISN
            'gender' => 'male',
            'student_status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->post(route('students.store'), $payload);
        $response->assertSessionHasErrors(['nis', 'nisn']);
    }

    public function test_admin_can_update_student_profile(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Eko Prasetyo',
            'email' => 'eko@eduvia.test',
            'role' => UserRole::STUDENT,
        ]);

        $profile = StudentProfile::create([
            'user_id' => $targetUser->id,
            'school_id' => $this->school->id,
            'nis' => '202607004',
            'nisn' => '0089991237',
            'student_status' => 'active',
            'gender' => 'male',
        ]);

        $updatePayload = [
            'name' => 'Eko Prasetyo Wardhana',
            'email' => 'eko.wardhana@eduvia.test',
            'nis' => '202607004',
            'nisn' => '0089991237',
            'gender' => 'male',
            'birth_place' => 'Surabaya',
            'birth_date' => '2012-01-15',
            'parent_name' => 'Hadi Wardhana',
            'student_status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->put(route('students.update', $targetUser), $updatePayload);

        $response->assertRedirect(route('students.show', $targetUser));

        $targetUser->refresh();
        $this->assertEquals('Eko Prasetyo Wardhana', $targetUser->name);
        $this->assertEquals('eko.wardhana@eduvia.test', $targetUser->email);

        $profile->refresh();
        $this->assertEquals('Surabaya', $profile->birth_place);
        $this->assertEquals('Hadi Wardhana', $profile->parent_name);
    }

    public function test_admin_can_change_student_status_with_audit_trail(): void
    {
        $targetUser = User::factory()->create(['role' => UserRole::STUDENT]);
        $profile = StudentProfile::create([
            'user_id' => $targetUser->id,
            'school_id' => $this->school->id,
            'nis' => '202607005',
            'student_status' => 'active',
        ]);

        $payload = [
            'status' => 'transferred',
            'effective_date' => '2026-10-15',
            'reason' => 'Pindah domisili orang tua ke Surabaya',
        ];

        $response = $this->actingAs($this->admin)->post(route('students.status.update', $targetUser), $payload);
        $response->assertRedirect(route('students.show', $targetUser));
        $response->assertSessionHas('status');

        $profile->refresh();
        $this->assertEquals('transferred', $profile->student_status);

        $this->assertDatabaseHas('student_status_histories', [
            'student_id' => $targetUser->id,
            'previous_status' => 'active',
            'new_status' => 'transferred',
            'effective_date' => '2026-10-15',
            'reason' => 'Pindah domisili orang tua ke Surabaya',
            'changed_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_batch_promote_students_to_next_classroom(): void
    {
        $nextGradeLevel = GradeLevel::factory()->create([
            'educational_level' => 'smp',
            'level_number' => 8,
            'name' => 'Kelas 8',
        ]);

        $nextAcademicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
            'name' => '2027/2028',
            'is_active' => false,
        ]);

        $nextClassroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $nextAcademicYear->id,
            'grade_level_id' => $nextGradeLevel->id,
            'name' => 'VIII-A',
            'is_active' => true,
        ]);

        $s1 = User::factory()->create(['role' => UserRole::STUDENT]);
        $s2 = User::factory()->create(['role' => UserRole::STUDENT]);

        StudentProfile::create(['user_id' => $s1->id, 'school_id' => $this->school->id, 'student_status' => 'active', 'nis' => '202607010']);
        StudentProfile::create(['user_id' => $s2->id, 'school_id' => $this->school->id, 'student_status' => 'active', 'nis' => '202607011']);

        $this->classroom->addStudent($s1);
        $this->classroom->addStudent($s2);

        $payload = [
            'action' => 'promote',
            'source_classroom_id' => $this->classroom->id,
            'student_ids' => [$s1->id, $s2->id],
            'target_classroom_id' => $nextClassroom->id,
            'target_academic_year_id' => $nextAcademicYear->id,
            'effective_date' => '2027-07-15',
            'notes' => 'Kenaikan kelas reguler tahun ajaran baru',
        ];

        $response = $this->actingAs($this->admin)->post(route('students.promotion.process'), $payload);
        $response->assertRedirect(route('students.promotion.index', ['source_classroom_id' => $this->classroom->id]));
        $response->assertSessionHas('status');

        // Check new enrollment in VIII-A
        $this->assertDatabaseHas('classroom_students', [
            'classroom_id' => $nextClassroom->id,
            'student_id' => $s1->id,
            'enrollment_status' => 'active',
        ]);
        $this->assertDatabaseHas('classroom_students', [
            'classroom_id' => $nextClassroom->id,
            'student_id' => $s2->id,
            'enrollment_status' => 'active',
        ]);

        // Old enrollment should be completed
        $this->assertDatabaseHas('classroom_students', [
            'classroom_id' => $this->classroom->id,
            'student_id' => $s1->id,
            'enrollment_status' => 'completed',
        ]);
    }

    public function test_admin_can_batch_graduate_students(): void
    {
        $seniorGradeLevel = GradeLevel::factory()->create([
            'educational_level' => 'smp',
            'level_number' => 9,
            'name' => 'Kelas 9',
        ]);

        $seniorClassroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $seniorGradeLevel->id,
            'name' => 'IX-A',
            'is_active' => true,
        ]);

        $s1 = User::factory()->create(['role' => UserRole::STUDENT]);
        StudentProfile::create(['user_id' => $s1->id, 'school_id' => $this->school->id, 'student_status' => 'active', 'nis' => '202607020']);

        $seniorClassroom->addStudent($s1);

        $payload = [
            'action' => 'graduate',
            'source_classroom_id' => $seniorClassroom->id,
            'student_ids' => [$s1->id],
            'effective_date' => '2027-06-25',
            'notes' => 'Kelulusan angkatan 2026/2027',
        ];

        $response = $this->actingAs($this->admin)->post(route('students.promotion.process'), $payload);
        $response->assertRedirect(route('students.promotion.index', ['source_classroom_id' => $seniorClassroom->id]));

        $profile = StudentProfile::where('user_id', $s1->id)->first();
        $this->assertEquals('graduated', $profile->student_status);
        $this->assertEquals('2027-06-25', $profile->graduation_date->toDateString());

        $this->assertDatabaseHas('classroom_students', [
            'classroom_id' => $seniorClassroom->id,
            'student_id' => $s1->id,
            'enrollment_status' => 'completed',
        ]);

        $this->assertDatabaseHas('student_status_histories', [
            'student_id' => $s1->id,
            'previous_status' => 'active',
            'new_status' => 'graduated',
            'changed_by' => $this->admin->id,
        ]);
    }

    public function test_student_deletion_protection_when_academic_enrollments_exist(): void
    {
        $targetUser = User::factory()->create(['role' => UserRole::STUDENT]);
        StudentProfile::create([
            'user_id' => $targetUser->id,
            'school_id' => $this->school->id,
            'nis' => '202607030',
            'student_status' => 'active',
        ]);

        $this->classroom->addStudent($targetUser);

        // Try to delete
        $response = $this->actingAs($this->admin)->delete(route('students.destroy', $targetUser));
        $response->assertRedirect();
        $response->assertSessionHasErrors('error');

        // User should still exist
        $this->assertDatabaseHas('users', ['id' => $targetUser->id]);
    }

    public function test_admin_can_view_demographics_and_report_summary(): void
    {
        $response = $this->actingAs($this->admin)->get(route('students.report.index'));
        $response->assertStatus(200);
        $response->assertSee('Demografi Kesiswaan');
        $response->assertSee('Komposisi Jenis Kelamin');
        $response->assertSee('Distribusi Rombongan Belajar');
    }
}
