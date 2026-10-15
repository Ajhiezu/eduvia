<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ParentStudentRelation;
use App\Models\School;
use App\Models\Semester;
use App\Models\StudentReportCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_super_admin_can_access_admin_restricted_endpoint(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/restricted-admin-demo');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_school_admin_can_access_admin_restricted_endpoint(): void
    {
        $schoolAdmin = User::factory()->schoolAdmin()->create();

        $response = $this->actingAs($schoolAdmin)->get('/restricted-admin-demo');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_student_is_denied_from_admin_restricted_endpoint_with_403(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get('/restricted-admin-demo');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_api_request_returns_401_json(): void
    {
        $response = $this->getJson('/restricted-admin-demo');

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    /**
     * Role Isolation: Orang tua tidak boleh dapat mengakses fitur-fitur internal siswa.
     */
    public function test_parent_is_denied_from_student_features_with_403(): void
    {
        $parent = User::factory()->parentRole()->create();

        $studentRoutes = [
            '/my-attendance',
            '/student/schedule',
            '/student/materials',
            '/student/assignments',
            '/student/exams',
            '/student/reports',
            '/student/finance',
        ];

        foreach ($studentRoutes as $url) {
            $response = $this->actingAs($parent)->get($url);
            $response->assertStatus(403);
        }
    }

    /**
     * Role Isolation: Orang tua tidak boleh dapat mengakses dashboard & modul operasional sekolah.
     */
    public function test_parent_is_denied_from_school_operations_with_403(): void
    {
        $parent = User::factory()->parentRole()->create();

        $operationsRoutes = [
            '/operations/dashboard',
            '/operations/library/books',
            '/operations/inventory/assets',
            '/operations/facilities/rooms',
            '/operations/correspondence',
        ];

        foreach ($operationsRoutes as $url) {
            $response = $this->actingAs($parent)->get($url);
            $response->assertStatus(403);
        }
    }

    /**
     * Role Isolation: Siswa tidak boleh dapat mengakses modul portal orang tua.
     */
    public function test_student_is_denied_from_parent_portal_features_with_403(): void
    {
        $student = User::factory()->student()->create();

        $parentRoutes = [
            '/parent/dashboard',
            '/parent/attendance',
            '/parent/assignments',
            '/parent/grades',
            '/parent/reports',
            '/parent/children',
            '/parent/finance',
        ];

        foreach ($parentRoutes as $url) {
            $response = $this->actingAs($student)->get($url);
            $response->assertStatus(403);
        }
    }

    /**
     * Role Isolation: Siswa tidak boleh dapat mengakses modul operasional sekolah.
     */
    public function test_student_is_denied_from_school_operations_with_403(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get('/operations/dashboard');
        $response->assertStatus(403);
    }

    /**
     * Otorisasi Rapor PDF: Orang tua hanya boleh mengunduh rapor anak terhubung yang berstatus published.
     */
    public function test_parent_can_only_download_own_child_published_report_card_pdf(): void
    {
        $school = School::factory()->create();
        $parent = User::factory()->parentRole()->create(['school_id' => $school->id]);
        $ownChild = User::factory()->student()->create(['school_id' => $school->id]);
        $otherStudent = User::factory()->student()->create(['school_id' => $school->id]);

        // Hubungkan anak dengan parent
        ParentStudentRelation::create([
            'school_id' => $school->id,
            'parent_id' => $parent->id,
            'student_id' => $ownChild->id,
            'relationship_type' => 'father',
            'status' => ParentStudentRelation::STATUS_ACTIVE,
            'is_primary_contact' => true,
            'verified_at' => now(),
        ]);

        $academicYear = AcademicYear::factory()->create(['school_id' => $school->id]);
        $semester = Semester::factory()->create(['academic_year_id' => $academicYear->id]);

        $academicYear2 = AcademicYear::factory()->create(['school_id' => $school->id]);
        $semester2 = Semester::factory()->create(['academic_year_id' => $academicYear2->id]);

        $classroom = Classroom::factory()->create(['school_id' => $school->id]);

        // Rapor published milik anak sendiri
        $publishedReport = StudentReportCard::factory()->create([
            'school_id' => $school->id,
            'student_id' => $ownChild->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'status' => StudentReportCard::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        // Rapor draft (belum published) milik anak sendiri (tahun ajaran berbeda)
        $draftReport = StudentReportCard::factory()->create([
            'school_id' => $school->id,
            'student_id' => $ownChild->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $academicYear2->id,
            'semester_id' => $semester2->id,
            'status' => StudentReportCard::STATUS_DRAFT,
            'published_at' => null,
        ]);

        // Rapor published milik anak orang lain
        $otherChildReport = StudentReportCard::factory()->create([
            'school_id' => $school->id,
            'student_id' => $otherStudent->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'status' => StudentReportCard::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        // 1. Parent unduh rapor anak sendiri yang published -> berhasil (200)
        $successResponse = $this->actingAs($parent)->get(route('reports.cards.pdf', $publishedReport));
        $successResponse->assertStatus(200);

        // 2. Parent unduh rapor anak sendiri yang belum published -> ditolak (403)
        $draftResponse = $this->actingAs($parent)->get(route('reports.cards.pdf', $draftReport));
        $draftResponse->assertStatus(403);

        // 3. Parent unduh rapor anak orang lain -> ditolak (403)
        $otherResponse = $this->actingAs($parent)->get(route('reports.cards.pdf', $otherChildReport));
        $otherResponse->assertStatus(403);
    }
}
