<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\FeeCategory;
use App\Models\GradeLevel;
use App\Models\Invoice;
use App\Models\ParentStudentRelation;
use App\Models\School;
use App\Models\Semester;
use App\Models\User;
use App\Policies\FacilityPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\StudentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GranularRbacTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected GradeLevel $gradeLevel;

    protected Classroom $classroomA;

    protected Classroom $classroomB;

    protected User $admin;

    protected User $teacherHomeroomA;

    protected User $teacherRegular;

    protected User $studentA;

    protected User $studentB;

    protected User $parentA;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name' => 'SMA Eduvia Unggulan',
            'code' => 'SCH-RBAC-01',
            'is_active' => true,
        ]);

        $this->academicYear = AcademicYear::create([
            'school_id' => $this->school->id,
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        $this->semester = Semester::create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Ganjil',
            'semester_type' => 'odd',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);

        $this->gradeLevel = GradeLevel::create([
            'name' => 'Kelas X RBAC',
            'level_number' => 10,
            'educational_level' => 'sma',
        ]);

        $this->admin = User::factory()->schoolAdmin()->create(['school_id' => $this->school->id]);
        $this->staff = User::factory()->staff()->create(['school_id' => $this->school->id]);
        $this->teacherHomeroomA = User::factory()->teacher()->create(['school_id' => $this->school->id]);
        $this->teacherRegular = User::factory()->teacher()->create(['school_id' => $this->school->id]);

        $this->classroomA = Classroom::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'homeroom_teacher_id' => $this->teacherHomeroomA->id,
            'name' => 'X-A Unggulan',
            'code' => 'CLS-X-A',
            'capacity' => 30,
            'is_active' => true,
        ]);

        $this->classroomB = Classroom::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'name' => 'X-B Reguler',
            'code' => 'CLS-X-B',
            'capacity' => 30,
            'is_active' => true,
        ]);

        $this->studentA = User::factory()->student()->create(['school_id' => $this->school->id]);
        $this->studentB = User::factory()->student()->create(['school_id' => $this->school->id]);

        ClassroomStudent::create([
            'classroom_id' => $this->classroomA->id,
            'student_id' => $this->studentA->id,
            'academic_year_id' => $this->academicYear->id,
            'enrollment_status' => 'active',
            'joined_at' => now()->toDateString(),
        ]);

        ClassroomStudent::create([
            'classroom_id' => $this->classroomB->id,
            'student_id' => $this->studentB->id,
            'academic_year_id' => $this->academicYear->id,
            'enrollment_status' => 'active',
            'joined_at' => now()->toDateString(),
        ]);

        $this->parentA = User::factory()->parent()->create(['school_id' => $this->school->id]);
        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parentA->id,
            'student_id' => $this->studentA->id,
            'relationship_type' => 'father',
            'status' => ParentStudentRelation::STATUS_ACTIVE,
            'is_primary_contact' => true,
        ]);
    }

    /**
     * Test 1: Guru Wali Kelas memiliki hak akses kontekstual atas siswanya.
     */
    public function test_homeroom_teacher_can_access_own_class_students_but_not_other_classes(): void
    {
        $policy = new StudentPolicy;

        // Wali Kelas X-A dapat mengakses berkas Siswa A
        $this->assertTrue(
            $policy->view($this->teacherHomeroomA, $this->studentA),
            'Wali Kelas X-A seharusnya diizinkan mengakses berkas siswanya.'
        );

        // Wali Kelas X-A TIDAK dapat mengakses berkas Siswa B (di Kelas X-B)
        $this->assertFalse(
            $policy->view($this->teacherHomeroomA, $this->studentB),
            'Wali Kelas X-A tidak boleh mengakses siswa dari kelas lain yang tidak diajarnya.'
        );

        // Guru reguler yang tidak mengajar Siswa A tidak dapat mengakses Siswa A
        $this->assertFalse(
            $policy->view($this->teacherRegular, $this->studentA),
            'Guru yang tidak mengajar siswa tertentu tidak boleh mengakses berkas siswa tersebut.'
        );
    }

    /**
     * Test 2: Orang tua hanya dapat melihat berkas dan tagihan anaknya sendiri.
     */
    public function test_parent_can_only_view_own_child_data(): void
    {
        $studentPolicy = new StudentPolicy;
        $invoicePolicy = new InvoicePolicy;

        // Orang tua A dapat mengakses berkas Siswa A (anaknya)
        $this->assertTrue($studentPolicy->view($this->parentA, $this->studentA));

        // Orang tua A TIDAK dapat mengakses berkas Siswa B (anak orang lain)
        $this->assertFalse($studentPolicy->view($this->parentA, $this->studentB));

        // Setup invoice
        $category = FeeCategory::create([
            'school_id' => $this->school->id,
            'name' => 'SPP Bulanan',
            'code' => 'SPP-01',
            'is_active' => true,
        ]);

        $invoiceA = Invoice::create([
            'school_id' => $this->school->id,
            'student_id' => $this->studentA->id,
            'academic_year_id' => $this->academicYear->id,
            'title' => 'Tagihan SPP Siswa A',
            'invoice_number' => 'INV-2026-A01',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'subtotal' => 500000,
            'total_amount' => 500000,
            'status' => 'pending',
        ]);

        $invoiceB = Invoice::create([
            'school_id' => $this->school->id,
            'student_id' => $this->studentB->id,
            'academic_year_id' => $this->academicYear->id,
            'title' => 'Tagihan SPP Siswa B',
            'invoice_number' => 'INV-2026-B01',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'subtotal' => 500000,
            'total_amount' => 500000,
            'status' => 'pending',
        ]);

        // Orang tua A berhak melihat tagihan anaknya
        $this->assertTrue($invoicePolicy->view($this->parentA, $invoiceA));

        // Orang tua A DITOLAK melihat tagihan anak lain
        $this->assertFalse($invoicePolicy->view($this->parentA, $invoiceB));
    }

    /**
     * Test 3: Siswa hanya dapat melihat data dirinya sendiri dan dilarang melihat siswa lain.
     */
    public function test_student_self_access_boundary(): void
    {
        $policy = new StudentPolicy;

        // Siswa A dapat melihat dirinya sendiri
        $this->assertTrue($policy->view($this->studentA, $this->studentA));

        // Siswa A dilarang melihat berkas Siswa B
        $this->assertFalse($policy->view($this->studentA, $this->studentB));
    }

    /**
     * Test 4: Maker-Checker separation pada keuangan sekolah.
     */
    public function test_maker_checker_in_finance_invoices(): void
    {
        $policy = new InvoicePolicy;

        // Staf / Admin dapat membuat tagihan (Maker)
        $this->assertTrue($policy->create($this->staff));
        $this->assertTrue($policy->create($this->admin));

        // Siswa dan Orang Tua dilarang membuat tagihan
        $this->assertFalse($policy->create($this->studentA));
        $this->assertFalse($policy->create($this->parentA));

        $invoice = Invoice::create([
            'school_id' => $this->school->id,
            'student_id' => $this->studentA->id,
            'academic_year_id' => $this->academicYear->id,
            'title' => 'Tagihan SPP Maker',
            'invoice_number' => 'INV-MAKER-01',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'subtotal' => 300000,
            'total_amount' => 300000,
            'status' => 'paid', // tagihan lunas
        ]);

        // Tagihan yang sudah lunas tidak boleh di-update lagi
        $this->assertFalse($policy->update($this->staff, $invoice));
    }

    /**
     * Test 5: Token Sanctum Student tidak dapat mengakses API Parent (Privilege Isolation).
     */
    public function test_student_sanctum_token_cannot_access_parent_endpoints(): void
    {
        // Berikan token dengan ability role:student
        Sanctum::actingAs($this->studentA, ['role:student']);

        // Student token dapat mengakses endpoint dashboard student
        $studentRes = $this->getJson(route('api.v1.student.dashboard'));
        $this->assertNotEquals(403, $studentRes->status());

        // Student token DITOLAK (403) saat mencoba mengakses endpoint parent
        $parentRes = $this->getJson(route('api.v1.parent.children'));
        $parentRes->assertStatus(403);
    }

    /**
     * Test 6: Token Sanctum Parent tidak dapat mengakses API Student (Privilege Isolation).
     */
    public function test_parent_sanctum_token_cannot_access_student_endpoints(): void
    {
        // Berikan token dengan ability role:parent
        Sanctum::actingAs($this->parentA, ['role:parent']);

        // Parent token dapat mengakses endpoint parent
        $parentRes = $this->getJson(route('api.v1.parent.children'));
        $this->assertNotEquals(403, $parentRes->status());

        // Parent token DITOLAK (403) saat mencoba mengakses endpoint student
        $studentRes = $this->getJson(route('api.v1.student.dashboard'));
        $studentRes->assertStatus(403);
    }

    /**
     * Test 7: Hak reservasi fasilitas oleh guru melalui policy & controller.
     */
    public function test_teacher_can_reserve_facility_via_policy(): void
    {
        $facilityPolicy = new FacilityPolicy;

        // Guru berhak melakukan reservasi
        $this->assertTrue($facilityPolicy->reserve($this->teacherRegular));

        // Guru dapat melihat katalog ruangan
        $this->assertTrue($facilityPolicy->viewRooms($this->teacherRegular));

        // Guru TIDAK berhak mengelola master ruangan
        $this->assertFalse($facilityPolicy->manageRooms($this->teacherRegular));
    }
}
