<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\School;
use App\Models\Semester;
use App\Models\StudentProfile;
use App\Models\StudentReportCard;
use App\Models\StudentReportCardItem;
use App\Models\Subject;
use App\Models\SubjectGradebook;
use App\Models\SubjectGradebookItem;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\GradebookCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DigitalReportCardTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected Classroom $classroom;

    protected Subject $subject;

    protected User $teacher;

    protected User $homeroomTeacher;

    protected User $student;

    protected User $schoolAdmin;

    protected TeachingAssignment $teachingAssignment;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup tenant school and academic period
        $this->school = School::factory()->create([
            'name' => 'SMA Eduvia Unggulan',
            'city' => 'Surabaya',
            'npsn' => '20198765',
        ]);

        $this->academicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
            'name' => '2026/2027',
            'is_active' => true,
        ]);

        $this->semester = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Semester Ganjil',
            'is_active' => true,
        ]);

        // 2. Setup users
        $this->schoolAdmin = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::SCHOOL_ADMIN,
        ]);

        $this->teacher = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::TEACHER,
            'name' => 'Budi Santoso, M.Pd',
        ]);

        $this->homeroomTeacher = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::TEACHER,
            'name' => 'Siti Rahmawati, S.Pd',
        ]);

        $this->student = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::STUDENT,
            'name' => 'Ahmad Fauzi',
        ]);

        StudentProfile::create([
            'user_id' => $this->student->id,
            'nis' => '10293',
            'nisn' => '0098765432',
            'gender' => 'L',
        ]);

        // 3. Setup classroom and enroll student
        $this->classroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'homeroom_teacher_id' => $this->homeroomTeacher->id,
            'name' => 'X-MIPA-1',
        ]);

        ClassroomStudent::create([
            'classroom_id' => $this->classroom->id,
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'enrollment_status' => 'active',
            'joined_at' => now()->toDateString(),
        ]);

        $this->subject = Subject::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Matematika',
            'code' => 'MAT-10',
        ]);

        $this->teachingAssignment = TeachingAssignment::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'is_active' => true,
        ]);
    }

    /**
     * Test deterministic score calculation in GradebookCalculationService.
     */
    public function test_gradebook_calculation_service_calculates_deterministic_weighted_scores(): void
    {
        $service = new GradebookCalculationService;

        $weights = [
            'assignment_weight' => 30.0,
            'quiz_weight' => 20.0,
            'midterm_weight' => 25.0,
            'final_weight' => 25.0,
        ];

        // 80*0.3 + 90*0.2 + 85*0.25 + 75*0.25 = 24 + 18 + 21.25 + 18.75 = 82.00
        $finalScore = $service->calculateFinalScore(80.0, 90.0, 85.0, 75.0, $weights);
        $this->assertEquals(82.00, $finalScore);

        $predicate = $service->determinePredicate($finalScore);
        $this->assertEquals('B', $predicate);
        $this->assertTrue($service->isPassed($finalScore, 75.00));

        // Test boundary clamping (score cannot exceed 100 or drop below 0)
        $overflowScore = $service->calculateFinalScore(120.0, 110.0, 150.0, 100.0, $weights);
        $this->assertEquals(100.00, $overflowScore);

        $negativeScore = $service->calculateFinalScore(-20.0, 0.0, 0.0, 0.0, $weights);
        $this->assertEquals(0.00, $negativeScore);
    }

    /**
     * Test assessment weight configuration validation: must equal 100%.
     */
    public function test_assessment_weight_must_sum_to_100_percent(): void
    {
        $this->actingAs($this->schoolAdmin);

        // Valid weights (30 + 20 + 25 + 25 = 100%)
        $response = $this->post(route('reports.weights.store'), [
            'name' => 'Standar Sekolah',
            'assignment_weight' => 30,
            'quiz_weight' => 20,
            'midterm_weight' => 25,
            'final_weight' => 25,
            'passing_grade' => 75,
        ]);
        $response->assertRedirect(route('reports.weights.index'));
        $this->assertDatabaseHas('assessment_weight_configurations', [
            'name' => 'Standar Sekolah',
            'school_id' => $this->school->id,
        ]);

        // Invalid weights (30 + 30 + 30 + 30 = 120%)
        $responseInvalid = $this->post(route('reports.weights.store'), [
            'name' => 'Skema Salah',
            'assignment_weight' => 30,
            'quiz_weight' => 30,
            'midterm_weight' => 30,
            'final_weight' => 30,
            'passing_grade' => 75,
        ]);
        $responseInvalid->assertSessionHasErrors('assignment_weight');
    }

    /**
     * Test teacher workflow: teacher can create and view gradebook for their own assigned subject.
     */
    public function test_teacher_can_initialize_gradebook_and_compile_students(): void
    {
        $this->actingAs($this->teacher);

        $response = $this->post(route('reports.gradebook.store'), [
            'teaching_assignment_id' => $this->teachingAssignment->id,
        ]);

        $gradebook = SubjectGradebook::where('classroom_id', $this->classroom->id)
            ->where('subject_id', $this->subject->id)
            ->first();

        $this->assertNotNull($gradebook);
        $response->assertRedirect(route('reports.gradebook.show', $gradebook));

        // Check that student was enrolled in the gradebook item
        $this->assertDatabaseHas('subject_gradebook_items', [
            'subject_gradebook_id' => $gradebook->id,
            'student_id' => $this->student->id,
        ]);
    }

    /**
     * Test teacher authorization: teacher cannot edit gradebook of other teachers or classes.
     */
    public function test_teacher_cannot_edit_gradebook_of_another_teacher(): void
    {
        $otherTeacher = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::TEACHER,
        ]);

        $gradebook = SubjectGradebook::factory()->create([
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'status' => SubjectGradebook::STATUS_DRAFT,
        ]);

        $this->actingAs($otherTeacher);

        // Other teacher cannot access edit form
        $response = $this->get(route('reports.gradebook.edit', $gradebook));
        $response->assertStatus(403);
    }

    /**
     * Test teacher submission and school admin verification workflow.
     */
    public function test_gradebook_submission_and_verification_workflow(): void
    {
        $gradebook = SubjectGradebook::factory()->create([
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'status' => SubjectGradebook::STATUS_DRAFT,
        ]);

        $item = SubjectGradebookItem::factory()->create([
            'subject_gradebook_id' => $gradebook->id,
            'student_id' => $this->student->id,
            'final_score' => 88.00,
            'predicate' => 'B',
            'is_passed' => true,
        ]);

        // 1. Teacher submits gradebook
        $this->actingAs($this->teacher);
        $submitResponse = $this->post(route('reports.gradebook.submit', $gradebook), [
            'submission_notes' => 'Semua nilai telah lengkap.',
        ]);
        $submitResponse->assertRedirect(route('reports.gradebook.show', $gradebook));
        $this->assertEquals(SubjectGradebook::STATUS_SUBMITTED, $gradebook->fresh()->status);

        // 2. Admin can request revision
        $this->actingAs($this->schoolAdmin);
        $revisionResponse = $this->post(route('reports.gradebook.request-revision', $gradebook), [
            'verification_notes' => 'Periksa kembali deskripsi capaian.',
        ]);
        $revisionResponse->assertRedirect(route('reports.gradebook.show', $gradebook));
        $this->assertEquals(SubjectGradebook::STATUS_REVISION_REQUESTED, $gradebook->fresh()->status);
        $this->assertTrue($gradebook->fresh()->isEditable());

        // 3. Admin verifies and approves
        $verifyResponse = $this->post(route('reports.gradebook.verify', $gradebook));
        $verifyResponse->assertRedirect(route('reports.gradebook.show', $gradebook));
        $this->assertEquals(SubjectGradebook::STATUS_VERIFIED, $gradebook->fresh()->status);
        $this->assertFalse($gradebook->fresh()->isEditable());
    }

    /**
     * Test homeroom teacher (wali kelas) report card compilation and nonacademic evaluations.
     */
    public function test_homeroom_teacher_can_compile_and_edit_report_card(): void
    {
        // Setup attendance records
        $session = AttendanceSession::create([
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'teacher_id' => $this->teacher->id,
            'session_type' => 'subject',
            'meeting_number' => 1,
            'date' => now()->toDateString(),
            'status' => 'finalized',
        ]);

        AttendanceRecord::create([
            'attendance_session_id' => $session->id,
            'student_id' => $this->student->id,
            'status' => 'present',
        ]);

        $this->actingAs($this->homeroomTeacher);

        // Bulk sync report cards for classroom
        $syncResponse = $this->post(route('reports.cards.bulk-sync', $this->classroom), [
            'semester_id' => $this->semester->id,
        ]);
        $syncResponse->assertRedirect();

        $reportCard = StudentReportCard::where('student_id', $this->student->id)
            ->where('classroom_id', $this->classroom->id)
            ->first();

        $this->assertNotNull($reportCard);
        $this->assertEquals($this->homeroomTeacher->id, $reportCard->homeroom_teacher_id);
        $this->assertEquals(1, $reportCard->attendance_summary['present'] ?? 0);

        // Update homeroom evaluations
        $updateResponse = $this->put(route('reports.cards.update', $reportCard), [
            'attendance_summary' => [
                'present' => 85,
                'sick' => 1,
                'permit' => 0,
                'absent' => 0,
            ],
            'extracurriculars' => [
                ['name' => 'Pramuka', 'predicate' => 'A', 'description' => 'Sangat aktif dan disiplin.'],
            ],
            'homeroom_notes' => 'Ananda memiliki kepemimpinan yang baik dan prestasi gemilang.',
        ]);

        $updateResponse->assertRedirect(route('reports.cards.show', $reportCard));
        $this->assertEquals('Ananda memiliki kepemimpinan yang baik dan prestasi gemilang.', $reportCard->fresh()->homeroom_notes);
    }

    /**
     * Test publication and immutable snapshot: once published, subsequent grade changes do not alter snapshot.
     */
    public function test_report_card_publication_freezes_immutable_snapshot(): void
    {
        $reportCard = StudentReportCard::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'homeroom_teacher_id' => $this->homeroomTeacher->id,
            'status' => StudentReportCard::STATUS_PENDING_REVIEW,
        ]);

        StudentReportCardItem::create([
            'student_report_card_id' => $reportCard->id,
            'subject_id' => $this->subject->id,
            'subject_name' => 'Matematika',
            'subject_code' => 'MAT-10',
            'passing_grade' => 75.00,
            'final_score' => 92.50,
            'predicate' => 'A',
            'is_passed' => true,
            'achievement_description' => 'Sangat menguasai konsep kalkulus dasar.',
        ]);

        // Publish report card by school admin
        $this->actingAs($this->schoolAdmin);
        $publishResponse = $this->post(route('reports.cards.publish', $reportCard));
        $publishResponse->assertRedirect(route('reports.cards.show', $reportCard));

        $freshReport = $reportCard->fresh();
        $this->assertEquals(StudentReportCard::STATUS_PUBLISHED, $freshReport->status);
        $this->assertNotNull($freshReport->snapshot);
        $this->assertEquals(92.50, $freshReport->snapshot['items'][0]['final_score']);

        // Now modify original item directly to simulate post-publication grade tampering
        StudentReportCardItem::where('student_report_card_id', $reportCard->id)
            ->update(['final_score' => 50.00]);

        // The frozen snapshot MUST remain exactly 92.50!
        $this->assertEquals(92.50, $freshReport->fresh()->snapshot['items'][0]['final_score']);
    }

    /**
     * Test student access security:
     * - Student can see their own published report card.
     * - Student cannot see draft/unapproved report card.
     * - Student cannot view another student's report card.
     */
    public function test_student_report_card_access_rules(): void
    {
        $otherStudent = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::STUDENT,
        ]);

        $publishedReport = StudentReportCard::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'status' => StudentReportCard::STATUS_PUBLISHED,
        ]);

        $semester2 = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Semester Genap',
            'semester_type' => 'genap',
            'is_active' => false,
        ]);

        $draftReport = StudentReportCard::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $semester2->id,
            'status' => StudentReportCard::STATUS_DRAFT,
        ]);

        $otherStudentReport = StudentReportCard::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $otherStudent->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'status' => StudentReportCard::STATUS_PUBLISHED,
        ]);

        $this->actingAs($this->student);

        // 1. Student can view published report
        $responsePublished = $this->get(route('student.reports.show', $publishedReport));
        $responsePublished->assertOk();

        // 2. Student cannot view draft report (returns 404)
        $responseDraft = $this->get(route('student.reports.show', $draftReport));
        $responseDraft->assertStatus(404);

        // 3. Student cannot view other student's report (returns 403 or 404)
        $responseOther = $this->get(route('student.reports.show', $otherStudentReport));
        $this->assertTrue(in_array($responseOther->getStatusCode(), [403, 404]), 'Other student report card must be denied with 403 or 404.');
    }

    /**
     * Test PDF download generation and security.
     */
    public function test_pdf_report_card_generation_and_authorization(): void
    {
        $publishedReport = StudentReportCard::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'homeroom_teacher_id' => $this->homeroomTeacher->id,
            'status' => StudentReportCard::STATUS_PUBLISHED,
        ]);

        StudentReportCardItem::create([
            'student_report_card_id' => $publishedReport->id,
            'subject_id' => $this->subject->id,
            'subject_name' => 'Matematika',
            'passing_grade' => 75.00,
            'final_score' => 88.00,
            'predicate' => 'B',
            'is_passed' => true,
        ]);

        // 1. Anonymous user cannot download PDF
        $responseAnon = $this->get(route('reports.cards.pdf', $publishedReport));
        $responseAnon->assertRedirect(route('login'));

        // 2. Student can download their own published PDF
        $this->actingAs($this->student);
        $responseStudent = $this->get(route('reports.cards.pdf', $publishedReport));
        $responseStudent->assertOk();
        $this->assertEquals('application/pdf', $responseStudent->headers->get('content-type'));

        // 3. Homeroom teacher can download PDF
        $this->actingAs($this->homeroomTeacher);
        $responseHomeroom = $this->get(route('reports.cards.pdf', $publishedReport));
        $responseHomeroom->assertOk();
        $this->assertEquals('application/pdf', $responseHomeroom->headers->get('content-type'));

        // 4. Other student cannot download PDF
        $otherStudent = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::STUDENT,
        ]);
        $this->actingAs($otherStudent);
        $responseForbidden = $this->get(route('reports.cards.pdf', $publishedReport));
        $responseForbidden->assertStatus(403);
    }

    /**
     * Test tenant isolation: Admin from another school cannot access gradebooks or report cards.
     */
    public function test_tenant_isolation_protects_gradebooks_and_report_cards(): void
    {
        $otherSchool = School::factory()->create(['name' => 'SMA Luar Negeri']);
        $otherAdmin = User::factory()->create([
            'school_id' => $otherSchool->id,
            'role' => UserRole::SCHOOL_ADMIN,
        ]);

        $gradebook = SubjectGradebook::factory()->create([
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
        ]);

        $reportCard = StudentReportCard::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'status' => StudentReportCard::STATUS_PUBLISHED,
        ]);

        $this->actingAs($otherAdmin);

        // Other school admin cannot view gradebook of this school
        $resGb = $this->get(route('reports.gradebook.show', $gradebook));
        $this->assertTrue(in_array($resGb->status(), [403, 404]));

        // Other school admin cannot view report card of this school
        $resRc = $this->get(route('reports.cards.show', $reportCard));
        $this->assertTrue(in_array($resRc->status(), [403, 404]));

        // Other school admin cannot download PDF
        $resPdf = $this->get(route('reports.cards.pdf', $reportCard));
        $this->assertTrue(in_array($resPdf->status(), [403, 404]));
    }
}
