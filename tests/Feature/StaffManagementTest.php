<?php

namespace Tests\Feature;

use App\Enums\StaffPosition;
use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\StaffProfile;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected User $schoolAdmin;

    protected User $teacher;

    protected AcademicYear $academicYear;

    protected Classroom $classroom;

    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name' => 'SMP Eduvia Test Utama',
            'npsn' => '10293847',
            'educational_levels' => ['smp'],
            'address' => 'Jl. Pendidikan No. 1',
            'city' => 'Jakarta Selatan',
            'is_active' => true,
        ]);

        $this->schoolAdmin = User::create([
            'school_id' => $this->school->id,
            'name' => 'Kepala Sekolah Utama',
            'email' => 'kepala.sekolah@eduvia.test',
            'role' => UserRole::SCHOOL_ADMIN->value,
            'is_active' => true,
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
        ]);

        StaffProfile::create([
            'user_id' => $this->schoolAdmin->id,
            'school_id' => $this->school->id,
            'position' => StaffPosition::PRINCIPAL->value,
            'status' => 'active',
            'gender' => 'male',
            'employment_status' => 'pns',
            'activated_at' => now(),
        ]);

        $this->teacher = User::create([
            'school_id' => $this->school->id,
            'name' => 'Guru Matematika Senior',
            'email' => 'guru.senior@eduvia.test',
            'role' => UserRole::TEACHER->value,
            'is_active' => true,
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
        ]);

        StaffProfile::create([
            'user_id' => $this->teacher->id,
            'school_id' => $this->school->id,
            'nip' => '198501012010011001',
            'position' => StaffPosition::SUBJECT_TEACHER->value,
            'status' => 'active',
            'gender' => 'male',
            'employment_status' => 'gty',
            'activated_at' => now(),
        ]);

        $this->academicYear = AcademicYear::create([
            'school_id' => $this->school->id,
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        $grade7 = GradeLevel::create([
            'educational_level' => 'smp',
            'level_number' => 7,
            'name' => 'Kelas 7',
            'order' => 1,
            'is_active' => true,
        ]);

        $this->classroom = Classroom::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $grade7->id,
            'name' => 'VII-A',
            'code' => 'VII-A',
            'capacity' => 32,
            'is_active' => true,
        ]);

        $this->subject = Subject::create([
            'school_id' => $this->school->id,
            'name' => 'Matematika Dasar',
            'code' => 'MTK-01',
            'is_active' => true,
        ]);
    }

    public function test_school_admin_can_view_staff_list_and_metrics(): void
    {
        $response = $this->actingAs($this->schoolAdmin)->get(route('staff.index'));

        $response->assertOk();
        $response->assertSee('Pendidik & Tenaga Kependidikan', false);
        $response->assertSee('Guru Matematika Senior');
        $response->assertSee('Kepala Sekolah Utama');
        $response->assertViewHas('totalStaffCount', 2);
        $response->assertViewHas('teacherCount', 1);
    }

    public function test_school_admin_can_create_new_teacher_with_invitation_token(): void
    {
        $payload = [
            'name' => 'Siti Aminah, S.Pd.',
            'email' => 'siti.aminah@eduvia.test',
            'phone' => '081234567891',
            'role' => UserRole::TEACHER->value,
            'position' => StaffPosition::SUBJECT_TEACHER->value,
            'work_unit' => 'Bidang Kurikulum',
            'nip' => '199003152015012002',
            'gender' => 'female',
            'employment_status' => 'gty',
            'creation_mode' => 'invitation',
        ];

        $response = $this->actingAs($this->schoolAdmin)->post(route('staff.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'siti.aminah@eduvia.test',
            'role' => UserRole::TEACHER->value,
            'is_active' => false,
        ]);

        $newUser = User::where('email', 'siti.aminah@eduvia.test')->first();
        $this->assertNotNull($newUser);
        $this->assertNotNull($newUser->staffProfile->invitation_token);
        $this->assertDatabaseHas('staff_status_histories', [
            'user_id' => $newUser->id,
            'action' => 'created',
        ]);
    }

    public function test_teacher_can_activate_account_using_token_and_set_password(): void
    {
        $token = Str::random(64);
        $newUser = User::create([
            'school_id' => $this->school->id,
            'name' => 'Rudi Hermawan',
            'email' => 'rudi.hermawan@eduvia.test',
            'role' => UserRole::TEACHER->value,
            'is_active' => false,
            'password' => Hash::make(Str::random(32)),
        ]);

        StaffProfile::create([
            'user_id' => $newUser->id,
            'school_id' => $this->school->id,
            'position' => StaffPosition::SUBJECT_TEACHER->value,
            'gender' => 'male',
            'employment_status' => 'gty',
            'invitation_token' => $token,
            'invitation_sent_at' => now(),
            'status' => 'inactive',
        ]);

        // 1. Visit activation page
        $pageResponse = $this->get(route('staff.activate', ['token' => $token]));
        $pageResponse->assertOk();
        $pageResponse->assertSee('Aktivasi Akun Pegawai');
        $pageResponse->assertSee('Rudi Hermawan');

        // 2. Submit new password
        $submitResponse = $this->post(route('staff.activate.submit', ['token' => $token]), [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $submitResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($newUser);

        $newUser->refresh();
        $staffProfile = $newUser->staffProfile()->first();
        $this->assertTrue($newUser->is_active);
        $this->assertNotNull($newUser->email_verified_at);
        $this->assertNull($staffProfile->invitation_token);
        $this->assertNotNull($staffProfile->activated_at);
        $this->assertTrue(Hash::check('NewPassword123!', $newUser->password));
    }

    public function test_school_admin_can_create_staff_with_direct_password(): void
    {
        $payload = [
            'name' => 'Bambang Trihatmoko',
            'email' => 'bambang.tu@eduvia.test',
            'phone' => '081298765432',
            'role' => UserRole::STAFF->value,
            'position' => StaffPosition::ADMINISTRATION->value,
            'work_unit' => 'Tata Usaha',
            'gender' => 'male',
            'employment_status' => 'pty',
            'creation_mode' => 'direct_password',
            'password' => 'StaffPassword123!',
            'password_confirmation' => 'StaffPassword123!',
        ];

        $response = $this->actingAs($this->schoolAdmin)->post(route('staff.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'bambang.tu@eduvia.test',
            'role' => UserRole::STAFF->value,
            'is_active' => true,
        ]);

        $user = User::where('email', 'bambang.tu@eduvia.test')->first();
        $this->assertTrue(Hash::check('StaffPassword123!', $user->password));
    }

    public function test_school_admin_cannot_assign_super_admin_role_privilege_escalation(): void
    {
        $payload = [
            'name' => 'Hacker Admin',
            'email' => 'hacker@eduvia.test',
            'role' => UserRole::SUPER_ADMIN->value, // Escalate!
            'position' => StaffPosition::ADMINISTRATION->value,
            'gender' => 'male',
            'employment_status' => 'gty',
            'creation_mode' => 'direct_password',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->actingAs($this->schoolAdmin)->post(route('staff.store'), $payload);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'hacker@eduvia.test']);
    }

    public function test_school_admin_cannot_escalate_own_role(): void
    {
        $response = $this->actingAs($this->schoolAdmin)->put(route('staff.update', $this->schoolAdmin), [
            'name' => $this->schoolAdmin->name,
            'email' => $this->schoolAdmin->email,
            'role' => UserRole::SUPER_ADMIN->value, // Self escalate!
            'position' => StaffPosition::PRINCIPAL->value,
            'gender' => 'male',
            'employment_status' => 'pns',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('role');
        $this->schoolAdmin->refresh();
        $this->assertEquals(UserRole::SCHOOL_ADMIN, $this->schoolAdmin->role);
    }

    public function test_school_admin_cannot_access_or_modify_staff_from_another_school(): void
    {
        $otherSchool = School::create([
            'name' => 'SMP Luar Kota',
            'is_active' => true,
        ]);

        $otherTeacher = User::create([
            'school_id' => $otherSchool->id,
            'name' => 'Guru Sekolah Lain',
            'email' => 'guru.lain@luar.test',
            'role' => UserRole::TEACHER->value,
            'is_active' => true,
            'password' => Hash::make('Password123!'),
        ]);

        StaffProfile::create([
            'user_id' => $otherTeacher->id,
            'school_id' => $otherSchool->id,
            'position' => StaffPosition::SUBJECT_TEACHER->value,
            'gender' => 'male',
            'employment_status' => 'gty',
        ]);

        // Attempting to view staff from other school yields 403 Forbidden
        $response = $this->actingAs($this->schoolAdmin)->get(route('staff.show', $otherTeacher));
        $response->assertForbidden();
    }

    public function test_school_admin_can_toggle_active_status_and_audit_log_created(): void
    {
        $this->assertTrue($this->teacher->is_active);

        // Deactivate
        $response = $this->actingAs($this->schoolAdmin)->post(route('staff.toggle-status', $this->teacher), [
            'reason' => 'Cuti panjang semester',
        ]);

        $response->assertRedirect();
        $this->teacher->refresh();
        $this->assertFalse($this->teacher->is_active);

        $this->assertDatabaseHas('staff_status_histories', [
            'user_id' => $this->teacher->id,
            'action' => 'deactivated',
            'reason' => 'Cuti panjang semester',
        ]);
    }

    public function test_deactivated_staff_cannot_login(): void
    {
        $this->teacher->update(['is_active' => false]);

        $response = $this->post(route('login'), [
            'email' => $this->teacher->email,
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_school_admin_can_assign_teaching_assignment_to_teacher(): void
    {
        $response = $this->actingAs($this->schoolAdmin)->post(route('staff.teaching-assignments.store', $this->teacher), [
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'academic_year_id' => $this->academicYear->id,
            'hours_per_week' => 4,
            'notes' => 'Pengampu semester ganjil',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('teaching_assignments', [
            'teacher_id' => $this->teacher->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'hours_per_week' => 4,
        ]);
    }

    public function test_duplicate_teaching_assignment_is_rejected(): void
    {
        TeachingAssignment::create([
            'school_id' => $this->school->id,
            'teacher_id' => $this->teacher->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'academic_year_id' => $this->academicYear->id,
            'hours_per_week' => 4,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->schoolAdmin)->post(route('staff.teaching-assignments.store', $this->teacher), [
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'academic_year_id' => $this->academicYear->id,
            'hours_per_week' => 4,
        ]);

        $response->assertSessionHasErrors('error');
    }

    public function test_staff_with_active_dependencies_cannot_be_deleted(): void
    {
        TeachingAssignment::create([
            'school_id' => $this->school->id,
            'teacher_id' => $this->teacher->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'academic_year_id' => $this->academicYear->id,
            'hours_per_week' => 4,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->schoolAdmin)->delete(route('staff.destroy', $this->teacher));

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('users', ['id' => $this->teacher->id]);
    }

    public function test_bulk_csv_import_validates_and_creates_staff(): void
    {
        $csvContent = "nama_lengkap,email,peran,jabatan,unit_kerja,nip,nuptk,jenis_kelamin,status_kepegawaian,telepon\n";
        $csvContent .= "Indah Permata,indah.permata@eduvia.test,teacher,guru_mapel,Kurikulum,,87654321,female,gty,0812998877\n";
        $csvContent .= "Joko Anwar,joko.anwar@eduvia.test,staff,tata_usaha,TU,,,male,pty,\n";

        $file = UploadedFile::fake()->createWithContent('ptk.csv', $csvContent);

        // Preview
        $previewResponse = $this->actingAs($this->schoolAdmin)->post(route('staff.import.preview'), [
            'file' => $file,
        ]);

        $previewResponse->assertOk();
        $previewResponse->assertViewHas('validCount', 2);
        $previewResponse->assertViewHas('invalidCount', 0);

        // Process
        $items = [
            [
                'name' => 'Indah Permata',
                'email' => 'indah.permata@eduvia.test',
                'role' => 'teacher',
                'position' => 'guru_mapel',
                'work_unit' => 'Kurikulum',
                'gender' => 'female',
                'employment_status' => 'gty',
            ],
            [
                'name' => 'Joko Anwar',
                'email' => 'joko.anwar@eduvia.test',
                'role' => 'staff',
                'position' => 'tata_usaha',
                'work_unit' => 'TU',
                'gender' => 'male',
                'employment_status' => 'pty',
            ],
        ];

        $processResponse = $this->actingAs($this->schoolAdmin)->post(route('staff.import.process'), [
            'items' => json_encode($items),
        ]);

        $processResponse->assertRedirect(route('staff.index'));
        $this->assertDatabaseHas('users', ['email' => 'indah.permata@eduvia.test', 'role' => 'teacher']);
        $this->assertDatabaseHas('users', ['email' => 'joko.anwar@eduvia.test', 'role' => 'staff']);
    }

    public function test_unauthorized_role_cannot_access_staff_management(): void
    {
        $student = User::create([
            'school_id' => $this->school->id,
            'name' => 'Siswa Penguji',
            'email' => 'siswa.penguji@eduvia.test',
            'role' => UserRole::STUDENT->value,
            'is_active' => true,
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->actingAs($student)->get(route('staff.index'));
        $response->assertForbidden();

        $teacherResponse = $this->actingAs($this->teacher)->get(route('staff.index'));
        $teacherResponse->assertForbidden();
    }
}
