<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\AdmissionApplicant;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionTrack;
use App\Models\ApplicantDocument;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdmissionsPPDBTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private AcademicYear $academicYear;

    private GradeLevel $gradeLevel;

    private Classroom $classroom;

    private AdmissionPeriod $period;

    private AdmissionTrack $trackZonasi;

    private User $admin;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

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

        $this->period = AdmissionPeriod::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'name' => 'PPDB 2026/2027 Gelombang 1',
            'code' => 'PPDB-2026-G1',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(20)->toDateString(),
            'announcement_date' => now()->addDays(25)->toDateString(),
            're_registration_start_date' => now()->addDays(26)->toDateString(),
            're_registration_end_date' => now()->addDays(30)->toDateString(),
            'quota' => 100,
            'status' => 'open',
            'is_active' => true,
        ]);

        $this->trackZonasi = AdmissionTrack::create([
            'admission_period_id' => $this->period->id,
            'name' => 'Jalur Zonasi Domisili',
            'code' => 'ZONASI',
            'quota' => 60,
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);
        $this->student = User::factory()->create(['role' => UserRole::STUDENT]);
    }

    public function test_guest_can_view_public_admissions_portal_and_open_periods(): void
    {
        $response = $this->get(route('ppdb.index'));
        $response->assertStatus(200);
        $response->assertSee('Penerimaan Peserta Didik Baru');
        $response->assertSee('PPDB 2026/2027 Gelombang 1');
        $response->assertSee('Jalur Zonasi Domisili');
    }

    public function test_guest_can_submit_admission_application_with_documents(): void
    {
        $payload = [
            'admission_track_id' => $this->trackZonasi->id,
            'target_grade_level_id' => $this->gradeLevel->id,
            'full_name' => 'Bayu Pratama Wijaya',
            'nisn' => '0098123456',
            'nik' => '3271012304130001',
            'gender' => 'male',
            'birth_place' => 'Jakarta',
            'birth_date' => '2013-04-23',
            'religion' => 'Islam',
            'address' => 'Jl. Saharjo No. 10, Jakarta Selatan',
            'phone' => '081234567890',
            'email' => 'bayu.pratama@example.test',
            'previous_school' => 'SD Negeri Menteng 01',
            'parent_name' => 'Wijaya Hendra',
            'parent_phone' => '081298765432',
            'parent_relationship' => 'Ayah',
            'document_kk' => UploadedFile::fake()->create('kartu_keluarga.pdf', 300, 'application/pdf'),
            'document_akta' => UploadedFile::fake()->create('akta_kelahiran.jpg', 250, 'image/jpeg'),
        ];

        $response = $this->post(route('ppdb.register.submit', $this->period), $payload);

        $applicant = AdmissionApplicant::where('nisn', '0098123456')->first();
        $this->assertNotNull($applicant);
        $this->assertEquals('Bayu Pratama Wijaya', $applicant->full_name);
        $this->assertNotNull($applicant->registration_number);
        $this->assertNotNull($applicant->tracking_code);
        $this->assertEquals('pending', $applicant->status);

        $response->assertRedirect(route('ppdb.success', [
            'registration_number' => $applicant->registration_number,
            'tracking_code' => $applicant->tracking_code,
        ]));

        // Check document uploads
        $this->assertDatabaseHas('applicant_documents', [
            'admission_applicant_id' => $applicant->id,
            'document_type' => 'kartu_keluarga',
        ]);
        $this->assertDatabaseHas('applicant_documents', [
            'admission_applicant_id' => $applicant->id,
            'document_type' => 'akta_kelahiran',
        ]);

        $kkDoc = $applicant->documents()->where('document_type', 'kartu_keluarga')->first();
        Storage::disk('local')->assertExists($kkDoc->file_path);
    }

    public function test_public_status_check_requires_valid_registration_number_and_tracking_code(): void
    {
        $applicant = AdmissionApplicant::create([
            'admission_period_id' => $this->period->id,
            'admission_track_id' => $this->trackZonasi->id,
            'registration_number' => 'PPDB-2026-9999',
            'tracking_code' => 'SECRET88',
            'full_name' => 'Nadia Syahrini',
            'gender' => 'female',
            'birth_place' => 'Bandung',
            'birth_date' => '2013-09-15',
            'religion' => 'Islam',
            'address' => 'Jl. Dago No. 12, Bandung',
            'phone' => '081234567899',
            'previous_school' => 'SD Kartika',
            'parent_name' => 'Syahrini Wati',
            'parent_phone' => '081234567898',
            'parent_relationship' => 'Ibu',
            'status' => 'verified',
        ]);

        // Wrong tracking code fails
        $wrongResponse = $this->post(route('ppdb.check-status.process'), [
            'registration_number' => 'PPDB-2026-9999',
            'tracking_code' => 'WRONGCODE',
        ]);
        $wrongResponse->assertSessionHasErrors('registration_number');

        // Correct registration and tracking code succeeds
        $successResponse = $this->post(route('ppdb.check-status.process'), [
            'registration_number' => 'PPDB-2026-9999',
            'tracking_code' => 'SECRET88',
        ]);
        $successResponse->assertStatus(200);
        $successResponse->assertSee('Nadia Syahrini');
        $successResponse->assertSee('Berkas Terverifikasi');
    }

    public function test_unauthorized_roles_cannot_access_admissions_admin_endpoints(): void
    {
        $this->get(route('admissions.dashboard'))->assertRedirect(route('login'));
        $this->actingAs($this->student)->get(route('admissions.dashboard'))->assertStatus(403);
        $this->actingAs($this->student)->get(route('admissions.periods.index'))->assertStatus(403);
        $this->actingAs($this->student)->get(route('admissions.applicants.index'))->assertStatus(403);
    }

    public function test_admin_can_view_admissions_dashboard_and_applicants_directory(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admissions.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Analitik PPDB / SPMB');
        $response->assertSee('Total Pendaftar');

        $applicantsResponse = $this->actingAs($this->admin)->get(route('admissions.applicants.index'));
        $applicantsResponse->assertStatus(200);
        $applicantsResponse->assertSee('Data Calon Siswa (Pendaftar PPDB)');
    }

    public function test_admin_can_create_and_update_admission_period(): void
    {
        $payload = [
            'name' => 'PPDB Gelombang 2 Reguler',
            'code' => 'PPDB-2026-G2',
            'academic_year_id' => $this->academicYear->id,
            'start_date' => now()->addDays(35)->toDateString(),
            'end_date' => now()->addDays(50)->toDateString(),
            'announcement_date' => now()->addDays(55)->toDateString(),
            're_registration_start_date' => now()->addDays(56)->toDateString(),
            're_registration_end_date' => now()->addDays(60)->toDateString(),
            'quota' => 80,
            'status' => 'draft',
            'description' => 'Gelombang penerimaan kedua khusus sisa kuota.',
            'tracks' => [
                ['name' => 'Jalur Tes Potensi', 'code' => 'TES', 'quota' => 50, 'description' => 'Tes tertulis'],
                ['name' => 'Jalur Kemitraan', 'code' => 'MITRA', 'quota' => 30, 'description' => 'Anak guru dan staf'],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('admissions.periods.store'), $payload);
        $createdPeriod = AdmissionPeriod::where('code', 'PPDB-2026-G2')->first();
        $this->assertNotNull($createdPeriod);
        $response->assertRedirect(route('admissions.periods.show', $createdPeriod));

        $this->assertDatabaseHas('admission_tracks', [
            'admission_period_id' => $createdPeriod->id,
            'code' => 'TES',
            'quota' => 50,
        ]);

        // Update period
        $updateResponse = $this->actingAs($this->admin)->put(route('admissions.periods.update', $createdPeriod), [
            'name' => 'PPDB Gelombang 2 Reguler (Revisi)',
            'code' => 'PPDB-2026-G2',
            'academic_year_id' => $this->academicYear->id,
            'start_date' => now()->addDays(35)->toDateString(),
            'end_date' => now()->addDays(50)->toDateString(),
            'announcement_date' => now()->addDays(55)->toDateString(),
            're_registration_start_date' => now()->addDays(56)->toDateString(),
            're_registration_end_date' => now()->addDays(60)->toDateString(),
            'quota' => 90,
            'status' => 'open',
        ]);

        $updateResponse->assertRedirect(route('admissions.periods.show', $createdPeriod));
        $createdPeriod->refresh();
        $this->assertEquals(90, $createdPeriod->quota);
        $this->assertEquals('open', $createdPeriod->status);
    }

    public function test_admin_can_verify_applicant_document(): void
    {
        $applicant = AdmissionApplicant::create([
            'admission_period_id' => $this->period->id,
            'admission_track_id' => $this->trackZonasi->id,
            'registration_number' => 'PPDB-2026-0101',
            'tracking_code' => 'DOC101',
            'full_name' => 'Gilang Ramadhan',
            'gender' => 'male',
            'phone' => '081234567891',
            'parent_name' => 'Ramadhan',
            'parent_phone' => '081234567892',
            'status' => 'pending',
        ]);

        $document = ApplicantDocument::create([
            'admission_applicant_id' => $applicant->id,
            'document_type' => 'kartu_keluarga',
            'file_path' => 'private/admission_documents/sample.pdf',
            'original_name' => 'kk_gilang.pdf',
            'file_size' => 102400,
            'mime_type' => 'application/pdf',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('admissions.applicants.verify-document', [$applicant, $document]),
            [
                'status' => 'valid',
                'review_notes' => 'Berkas asli dan stempel basah terverifikasi.',
            ]
        );

        $response->assertRedirect(route('admissions.applicants.show', $applicant));

        $document->refresh();
        $this->assertEquals('valid', $document->status);
        $this->assertEquals('Berkas asli dan stempel basah terverifikasi.', $document->review_notes);
        $this->assertEquals($this->admin->id, $document->verified_by);
    }

    public function test_admin_can_update_applicant_selection_status(): void
    {
        $applicant = AdmissionApplicant::create([
            'admission_period_id' => $this->period->id,
            'admission_track_id' => $this->trackZonasi->id,
            'registration_number' => 'PPDB-2026-0202',
            'tracking_code' => 'SEL202',
            'full_name' => 'Hendra Setiawan',
            'gender' => 'male',
            'phone' => '081234567893',
            'parent_name' => 'Setiawan',
            'parent_phone' => '081234567894',
            'status' => 'verified',
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('admissions.applicants.status', $applicant),
            [
                'status' => 'accepted',
                'selection_score' => 92.50,
                'selection_notes' => 'Lulus seleksi peringkat 3 jalur zonasi.',
            ]
        );

        $response->assertRedirect(route('admissions.applicants.show', $applicant));

        $applicant->refresh();
        $this->assertEquals('accepted', $applicant->status);
        $this->assertEquals(92.50, $applicant->selection_score);
    }

    public function test_admin_can_record_re_registration(): void
    {
        $applicant = AdmissionApplicant::create([
            'admission_period_id' => $this->period->id,
            'admission_track_id' => $this->trackZonasi->id,
            'registration_number' => 'PPDB-2026-0303',
            'tracking_code' => 'REG303',
            'full_name' => 'Indah Permatasari',
            'gender' => 'female',
            'phone' => '081234567895',
            'parent_name' => 'Permata',
            'parent_phone' => '081234567896',
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admissions.applicants.re-register', $applicant));
        $response->assertRedirect(route('admissions.applicants.show', $applicant));

        $applicant->refresh();
        $this->assertEquals('re_registered', $applicant->status);
        $this->assertNotNull($applicant->re_registered_at);
    }

    public function test_admin_can_convert_accepted_applicant_to_active_student_with_classroom(): void
    {
        $applicant = AdmissionApplicant::create([
            'admission_period_id' => $this->period->id,
            'admission_track_id' => $this->trackZonasi->id,
            'registration_number' => 'PPDB-2026-0404',
            'tracking_code' => 'CONV404',
            'full_name' => 'Joko Anwar Pratama',
            'nisn' => '0098999888',
            'gender' => 'male',
            'birth_place' => 'Solo',
            'birth_date' => '2013-01-10',
            'religion' => 'Islam',
            'address' => 'Jl. Slamet Riyadi No. 45',
            'phone' => '081234567897',
            'email' => 'joko.anwar@example.test',
            'parent_name' => 'Anwar',
            'parent_phone' => '081234567898',
            'parent_relationship' => 'Ayah',
            'status' => 're_registered',
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('admissions.applicants.convert', $applicant),
            ['classroom_id' => $this->classroom->id]
        );

        $applicant->refresh();
        $this->assertEquals('converted', $applicant->status);
        $this->assertNotNull($applicant->converted_user_id);

        $createdStudent = User::find($applicant->converted_user_id);
        $this->assertNotNull($createdStudent);
        $this->assertEquals(UserRole::STUDENT, $createdStudent->role);
        $this->assertEquals('Joko Anwar Pratama', $createdStudent->name);

        $profile = StudentProfile::where('user_id', $createdStudent->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('active', $profile->student_status);
        $this->assertEquals('0098999888', $profile->nisn);

        // Check classroom placement
        $this->assertDatabaseHas('classroom_students', [
            'classroom_id' => $this->classroom->id,
            'student_id' => $createdStudent->id,
            'enrollment_status' => 'active',
        ]);

        $response->assertRedirect(route('students.show', $createdStudent));
    }

    public function test_conversion_is_idempotent_and_prevents_duplicate_student_records(): void
    {
        $applicant = AdmissionApplicant::create([
            'admission_period_id' => $this->period->id,
            'admission_track_id' => $this->trackZonasi->id,
            'registration_number' => 'PPDB-2026-0505',
            'tracking_code' => 'IDEMP505',
            'full_name' => 'Karin Novilda',
            'nisn' => '0098777666',
            'gender' => 'female',
            'phone' => '081234567899',
            'parent_name' => 'Novilda',
            'parent_phone' => '081234567800',
            'status' => 'accepted',
        ]);

        // First conversion call
        $firstStudent = $applicant->convertToActiveStudent($this->classroom->id, $this->admin);

        // Second conversion call
        $secondStudent = $applicant->convertToActiveStudent($this->classroom->id, $this->admin);

        $this->assertEquals($firstStudent->id, $secondStudent->id);
        $this->assertEquals(1, User::where('name', 'Karin Novilda')->count());
        $this->assertEquals(1, StudentProfile::where('nisn', '0098777666')->count());
    }

    public function test_admin_can_download_private_applicant_document(): void
    {
        $file = UploadedFile::fake()->create('sample_akta.pdf', 100, 'application/pdf');
        $filePath = $file->store('private/admission_documents/test', 'local');

        $applicant = AdmissionApplicant::create([
            'admission_period_id' => $this->period->id,
            'admission_track_id' => $this->trackZonasi->id,
            'registration_number' => 'PPDB-2026-0606',
            'tracking_code' => 'DOWN606',
            'full_name' => 'Lukman Sardi',
            'gender' => 'male',
            'phone' => '081234567801',
            'parent_name' => 'Sardi',
            'parent_phone' => '081234567802',
            'status' => 'pending',
        ]);

        $document = ApplicantDocument::create([
            'admission_applicant_id' => $applicant->id,
            'document_type' => 'akta_kelahiran',
            'file_path' => $filePath,
            'original_name' => 'akta_lukman.pdf',
            'file_size' => 102400,
            'mime_type' => 'application/pdf',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admissions.documents.download', $document));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
