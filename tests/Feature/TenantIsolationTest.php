<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AdmissionApplicant;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionTrack;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\GradeLevel;
use App\Models\Invoice;
use App\Models\LearningMaterial;
use App\Models\LibraryBook;
use App\Models\School;
use App\Models\SchoolAnnouncement;
use App\Models\SchoolLetter;
use App\Models\Semester;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected School $schoolA;

    protected School $schoolB;

    protected User $adminA;

    protected User $adminB;

    protected User $studentA;

    protected User $studentB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create School A
        $this->schoolA = School::create([
            'name' => 'SMA Nusantara Harapan A',
            'slug' => 'sma-nusantara-a',
            'npsn' => '10000001',
            'educational_levels' => ['sma'],
            'is_active' => true,
        ]);

        // 2. Create School B
        $this->schoolB = School::create([
            'name' => 'SMA Budi Luhur B',
            'slug' => 'sma-budi-luhur-b',
            'npsn' => '20000002',
            'educational_levels' => ['sma'],
            'is_active' => true,
        ]);

        // 3. Create School Admins
        $this->adminA = User::factory()->schoolAdmin()->create([
            'name' => 'Admin Sekolah A',
            'email' => 'admin.a@eduvia.test',
            'school_id' => $this->schoolA->id,
        ]);

        $this->adminB = User::factory()->schoolAdmin()->create([
            'name' => 'Admin Sekolah B',
            'email' => 'admin.b@eduvia.test',
            'school_id' => $this->schoolB->id,
        ]);

        // 4. Create Students
        $this->studentA = User::factory()->student()->create([
            'name' => 'Siswa Rahasia Sekolah A',
            'email' => 'siswa.a@eduvia.test',
            'school_id' => $this->schoolA->id,
        ]);
        StudentProfile::create([
            'user_id' => $this->studentA->id,
            'nis' => '11111',
            'nisn' => '0011111111',
            'gender' => 'male',
            'student_status' => 'active',
        ]);

        $this->studentB = User::factory()->student()->create([
            'name' => 'Siswa Publik Sekolah B',
            'email' => 'siswa.b@eduvia.test',
            'school_id' => $this->schoolB->id,
        ]);
        StudentProfile::create([
            'user_id' => $this->studentB->id,
            'nis' => '22222',
            'nisn' => '0022222222',
            'gender' => 'female',
            'student_status' => 'active',
        ]);
    }

    /**
     * Test 1: Admin Sekolah B dilarang membaca atau mengedit data Siswa milik Sekolah A (IDOR via URL).
     */
    public function test_school_admin_cannot_view_or_edit_student_from_another_school(): void
    {
        // Admin B mencoba mengakses detail siswa A
        $response = $this->actingAs($this->adminB)->get(route('students.show', $this->studentA));

        // Harus 404 (tidak ditemukan di tenant B) atau 403 (akses ditolak)
        $this->assertTrue(
            in_array($response->status(), [403, 404]),
            "Admin Sekolah B berhasil mengakses profil siswa Sekolah A (Status: {$response->status()})"
        );

        // Admin B mencoba membuka form edit siswa A
        $editResponse = $this->actingAs($this->adminB)->get(route('students.edit', $this->studentA));
        $this->assertTrue(
            in_array($editResponse->status(), [403, 404]),
            "Admin Sekolah B berhasil membuka form edit siswa Sekolah A (Status: {$editResponse->status()})"
        );
    }

    /**
     * Test 2: Index list siswa Sekolah B tidak boleh membocorkan nama atau data siswa Sekolah A.
     */
    public function test_school_admin_cannot_see_students_of_another_school_in_index(): void
    {
        $response = $this->actingAs($this->adminB)->get(route('students.index'));

        $response->assertStatus(200);
        $response->assertSee('Siswa Publik Sekolah B');
        $response->assertDontSee('Siswa Rahasia Sekolah A');
    }

    /**
     * Test 3: Admin Sekolah B dilarang mengubah atau menghapus rombel (Classroom) milik Sekolah A.
     */
    public function test_school_admin_cannot_access_or_modify_classroom_of_another_school(): void
    {
        $yearA = AcademicYear::create([
            'school_id' => $this->schoolA->id,
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        $gradeLevelA = GradeLevel::create([
            'school_id' => $this->schoolA->id,
            'name' => 'Kelas X',
            'level_number' => 10,
            'educational_level' => 'sma',
        ]);

        $classroomA = Classroom::create([
            'school_id' => $this->schoolA->id,
            'academic_year_id' => $yearA->id,
            'grade_level_id' => $gradeLevelA->id,
            'name' => 'X-IPA-A-Privat',
            'code' => 'CLS-X-IPA-A',
            'curriculum_type' => 'merdeka',
            'capacity' => 32,
            'is_active' => true,
        ]);

        // Admin B coba lihat
        $showRes = $this->actingAs($this->adminB)->get(route('classrooms.show', $classroomA));
        $this->assertTrue(
            in_array($showRes->status(), [403, 404]),
            "Admin Sekolah B berhasil melihat rombel Sekolah A (Status: {$showRes->status()})"
        );

        // Admin B coba update
        $updateRes = $this->actingAs($this->adminB)->put(route('classrooms.update', $classroomA), [
            'name' => 'X-IPA-Dibajak',
            'code' => 'CLS-X-HIJACK',
            'academic_year_id' => $yearA->id,
            'capacity' => 30,
        ]);
        $this->assertTrue(
            in_array($updateRes->status(), [403, 404]),
            "Admin Sekolah B berhasil mengupdate rombel Sekolah A (Status: {$updateRes->status()})"
        );
    }

    /**
     * Test 4: Admin Sekolah B dilarang mengakses tagihan SPP (Invoices) milik Sekolah A.
     */
    public function test_school_admin_cannot_access_invoices_of_another_school(): void
    {
        $yearA = AcademicYear::create([
            'school_id' => $this->schoolA->id,
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        $invoiceA = Invoice::create([
            'school_id' => $this->schoolA->id,
            'academic_year_id' => $yearA->id,
            'student_id' => $this->studentA->id,
            'invoice_number' => 'INV-A-99999',
            'title' => 'SPP Rahasia Sekolah A',
            'billing_period_key' => '2026-10',
            'subtotal' => 500000,
            'total_amount' => 500000,
            'paid_amount' => 0,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7),
            'status' => 'issued',
        ]);

        $response = $this->actingAs($this->adminB)->get(route('finance.invoices.show', $invoiceA));
        $this->assertTrue(
            in_array($response->status(), [403, 404]),
            "Admin Sekolah B berhasil mengakses tagihan keuangan Sekolah A (Status: {$response->status()})"
        );
    }

    /**
     * Test 5: Admin Sekolah B dilarang mengakses bank soal atau CBT milik Sekolah A.
     */
    public function test_school_admin_cannot_access_cbt_exams_of_another_school(): void
    {
        $teacherA = User::factory()->teacher()->create([
            'school_id' => $this->schoolA->id,
        ]);

        $subjectA = Subject::create([
            'school_id' => $this->schoolA->id,
            'code' => 'BIO-A',
            'name' => 'Biologi Sekolah A',
        ]);

        $examA = Exam::create([
            'school_id' => $this->schoolA->id,
            'subject_id' => $subjectA->id,
            'teacher_id' => $teacherA->id,
            'title' => 'Ujian Tengah Semester Rahasia A',
            'duration_minutes' => 90,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHours(2),
            'status' => 'published',
            'total_score' => 100,
        ]);

        $response = $this->actingAs($this->adminB)->get(route('exams.show', $examA));
        $this->assertTrue(
            in_array($response->status(), [403, 404]),
            "Admin Sekolah B berhasil mengakses ujian CBT Sekolah A (Status: {$response->status()})"
        );
    }

    /**
     * Test 6: Admin Sekolah B dilarang mengakses materi pembelajaran milik Sekolah A.
     */
    public function test_school_admin_cannot_access_learning_materials_of_another_school(): void
    {
        $teacherA = User::factory()->teacher()->create(['school_id' => $this->schoolA->id]);
        $subjectA = Subject::create(['school_id' => $this->schoolA->id, 'code' => 'MAT-A', 'name' => 'Matematika A']);
        $yearA = AcademicYear::create([
            'school_id' => $this->schoolA->id,
            'name' => '2026/2027 MAT',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);
        $gradeLevelA = GradeLevel::create([
            'name' => 'Kelas X Materi',
            'level_number' => 10,
            'educational_level' => 'sma',
        ]);
        $classroomA = Classroom::create([
            'school_id' => $this->schoolA->id,
            'academic_year_id' => $yearA->id,
            'grade_level_id' => $gradeLevelA->id,
            'name' => 'X-Materi-A',
            'code' => 'CLS-X-MAT',
            'capacity' => 30,
            'is_active' => true,
        ]);
        $teachingAssignmentA = TeachingAssignment::create([
            'school_id' => $this->schoolA->id,
            'academic_year_id' => $yearA->id,
            'classroom_id' => $classroomA->id,
            'subject_id' => $subjectA->id,
            'teacher_id' => $teacherA->id,
            'is_active' => true,
        ]);

        $materialA = LearningMaterial::create([
            'school_id' => $this->schoolA->id,
            'teaching_assignment_id' => $teachingAssignmentA->id,
            'classroom_id' => $classroomA->id,
            'subject_id' => $subjectA->id,
            'teacher_id' => $teacherA->id,
            'title' => 'Materi Rahasia Sekolah A',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->adminB)->get(route('learning-materials.show', $materialA));
        $this->assertTrue(
            in_array($response->status(), [403, 404]),
            "Admin Sekolah B berhasil mengakses materi pembelajaran Sekolah A (Status: {$response->status()})"
        );
    }

    /**
     * Test 7: Admin Sekolah B dilarang mengakses sesi presensi milik Sekolah A.
     */
    public function test_school_admin_cannot_access_attendance_sessions_of_another_school(): void
    {
        $yearA = AcademicYear::create([
            'school_id' => $this->schoolA->id,
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);
        $semesterA = Semester::create([
            'academic_year_id' => $yearA->id,
            'name' => 'Ganjil',
            'semester_type' => 'odd',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);
        $gradeLevelA = GradeLevel::create([
            'name' => 'Kelas X Presensi',
            'level_number' => 10,
            'educational_level' => 'sma',
        ]);
        $classroomA = Classroom::create([
            'school_id' => $this->schoolA->id,
            'academic_year_id' => $yearA->id,
            'grade_level_id' => $gradeLevelA->id,
            'name' => 'X-Presensi-A',
            'code' => 'CLS-X-ATT',
            'capacity' => 30,
            'is_active' => true,
        ]);
        $teacherA = User::factory()->teacher()->create(['school_id' => $this->schoolA->id]);

        $sessionA = AttendanceSession::create([
            'school_id' => $this->schoolA->id,
            'classroom_id' => $classroomA->id,
            'academic_year_id' => $yearA->id,
            'semester_id' => $semesterA->id,
            'teacher_id' => $teacherA->id,
            'date' => now()->toDateString(),
            'session_type' => 'daily',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->adminB)->get(route('attendance-sessions.show', $sessionA));
        $this->assertTrue(
            in_array($response->status(), [403, 404]),
            "Admin Sekolah B berhasil mengakses sesi presensi Sekolah A (Status: {$response->status()})"
        );
    }

    /**
     * Test 8: Admin Sekolah B dilarang mengakses katalog buku perpustakaan milik Sekolah A.
     */
    public function test_school_admin_cannot_access_library_books_of_another_school(): void
    {
        $bookA = LibraryBook::create([
            'school_id' => $this->schoolA->id,
            'title' => 'Buku Rahasia Sekolah A',
            'author' => 'Penulis A',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminB)->get(route('operations.library.books.show', $bookA));
        $this->assertTrue(
            in_array($response->status(), [403, 404]),
            "Admin Sekolah B berhasil mengakses katalog perpustakaan Sekolah A (Status: {$response->status()})"
        );
    }

    /**
     * Test 9: Admin Sekolah B dilarang mengakses surat dinas / tata usaha milik Sekolah A.
     */
    public function test_school_admin_cannot_access_official_letters_of_another_school(): void
    {
        $letterA = SchoolLetter::create([
            'school_id' => $this->schoolA->id,
            'type' => 'incoming',
            'reference_number' => '421/001/A/2026',
            'letter_date' => now()->toDateString(),
            'received_or_sent_date' => now()->toDateString(),
            'sender_or_recipient' => 'Dinas Pendidikan',
            'subject' => 'Surat Rahasia A',
            'status' => 'archived',
        ]);

        $response = $this->actingAs($this->adminB)->get(route('operations.correspondence.show', $letterA));
        $this->assertTrue(
            in_array($response->status(), [403, 404]),
            "Admin Sekolah B berhasil mengakses surat dinas Sekolah A (Status: {$response->status()})"
        );
    }

    /**
     * Test 10: Admin Sekolah B dilarang mengakses berkas pendaftar PPDB milik Sekolah A.
     */
    public function test_school_admin_cannot_access_admission_applicant_of_another_school(): void
    {
        $yearA = AcademicYear::create([
            'school_id' => $this->schoolA->id,
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        $periodA = AdmissionPeriod::create([
            'school_id' => $this->schoolA->id,
            'academic_year_id' => $yearA->id,
            'name' => 'PPDB 2026 Sekolah A',
            'code' => 'PPDB-A-2026',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'announcement_date' => now()->addDays(10)->toDateString(),
            're_registration_start_date' => now()->addDays(11)->toDateString(),
            're_registration_end_date' => now()->addDays(15)->toDateString(),
            'quota' => 100,
            'status' => 'open',
            'is_active' => true,
        ]);

        $trackA = AdmissionTrack::create([
            'admission_period_id' => $periodA->id,
            'name' => 'Zonasi',
            'code' => 'ZONASI',
            'quota' => 50,
        ]);

        $applicantA = AdmissionApplicant::create([
            'admission_period_id' => $periodA->id,
            'admission_track_id' => $trackA->id,
            'registration_number' => 'REG-A-001',
            'tracking_code' => 'TRK-A-001',
            'full_name' => 'Calon Siswa Rahasia A',
            'gender' => 'male',
            'phone' => '081234567890',
            'parent_name' => 'Orang Tua A',
            'parent_phone' => '081298765432',
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->adminB)->get(route('admissions.applicants.show', $applicantA));
        $this->assertTrue(
            in_array($response->status(), [403, 404]),
            "Admin Sekolah B berhasil mengakses pendaftar PPDB Sekolah A (Status: {$response->status()})"
        );
    }

    /**
     * Test 11: Super Admin dapat mengakses data lintas sekolah saat konteks tenant dipilih secara eksplisit.
     */
    public function test_super_admin_can_access_across_schools_with_explicit_context(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        // Super Admin mengakses data siswa A
        $responseA = $this->actingAs($superAdmin)->get(route('students.show', $this->studentA));
        $responseA->assertStatus(200);

        // Super Admin mengakses data siswa B
        $responseB = $this->actingAs($superAdmin)->get(route('students.show', $this->studentB));
        $responseB->assertStatus(200);
    }

    /**
     * Test 12: Model yang baru dibuat otomatis diberi school_id tenant aktif dan menolak spoofing.
     */
    public function test_created_model_automatically_scoped_to_active_tenant(): void
    {
        // Admin B membuat pengumuman baru, tapi mencoba menyisipkan school_id = School A
        $response = $this->actingAs($this->adminB)->post(route('announcements.store'), [
            'school_id' => $this->schoolA->id, // upaya spoofing
            'title' => 'Pengumuman Resmi Sekolah B',
            'content' => 'Konten pengumuman sekolah b yang sah.',
            'category' => 'general',
            'target_audience' => 'all',
            'status' => 'published',
        ]);

        $announcement = SchoolAnnouncement::where('title', 'Pengumuman Resmi Sekolah B')->first();
        $this->assertNotNull($announcement, 'Pengumuman tidak berhasil dibuat');

        // Harus terikat ke School B, BUKAN School A yang di-spoof
        $this->assertEquals(
            $this->schoolB->id,
            $announcement->school_id,
            'Spoofing school_id berhasil menembus tenant boundary!'
        );
    }
}
