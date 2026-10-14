<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\LearningMaterial;
use App\Models\LearningMaterialAttachment;
use App\Models\School;
use App\Models\Semester;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CoreLearningFlowTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private School $otherSchool;

    private AcademicYear $academicYear;

    private Semester $semester;

    private Classroom $classroom;

    private Classroom $otherClassroom;

    private Subject $subject;

    private User $teacher;

    private User $otherTeacher;

    private User $student1;

    private User $student2;

    private TeachingAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->school = School::factory()->create();
        $this->otherSchool = School::factory()->create();

        $this->academicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
            'name' => '2026/2027',
            'is_active' => true,
        ]);

        $this->semester = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'semester_type' => 'ganjil',
            'is_active' => true,
        ]);

        $gradeLevel = GradeLevel::factory()->create(['educational_level' => 'smp', 'level_number' => 7]);

        $this->classroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $gradeLevel->id,
            'name' => 'VII-A',
        ]);

        $this->otherClassroom = Classroom::factory()->create([
            'school_id' => $this->otherSchool->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $gradeLevel->id,
            'name' => 'VII-B',
        ]);

        $this->subject = Subject::create([
            'school_id' => $this->school->id,
            'code' => 'FIS',
            'name' => 'Fisika',
            'is_active' => true,
        ]);

        $this->teacher = User::factory()->create(['role' => UserRole::TEACHER]);
        $this->otherTeacher = User::factory()->create(['role' => UserRole::TEACHER]);

        $this->student1 = User::factory()->create(['role' => UserRole::STUDENT]);
        $this->student2 = User::factory()->create(['role' => UserRole::STUDENT]);

        StudentProfile::create([
            'user_id' => $this->student1->id,
            'school_id' => $this->school->id,
            'nisn' => '1234567890',
            'nis' => '1001',
            'student_status' => 'active',
        ]);

        StudentProfile::create([
            'user_id' => $this->student2->id,
            'school_id' => $this->otherSchool->id,
            'nisn' => '0987654321',
            'nis' => '1002',
            'student_status' => 'active',
        ]);

        // Place student 1 in classroom VII-A
        $this->classroom->addStudent($this->student1);

        // Place student 2 in otherClassroom VII-B
        $this->otherClassroom->addStudent($this->student2);

        // Teaching assignment for teacher in classroom VII-A
        $this->assignment = TeachingAssignment::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'hours_per_week' => 3,
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_learning_modules(): void
    {
        $this->get(route('learning-materials.index'))->assertRedirect(route('login'));
        $this->get(route('assignments.index'))->assertRedirect(route('login'));
        $this->get(route('student.materials.index'))->assertRedirect(route('login'));
        $this->get(route('student.assignments.index'))->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_teacher_learning_management(): void
    {
        $this->actingAs($this->student1);
        $this->get(route('learning-materials.index'))->assertForbidden();
        $this->get(route('learning-materials.create'))->assertForbidden();
        $this->get(route('assignments.index'))->assertForbidden();
        $this->get(route('assignments.create'))->assertForbidden();
    }

    public function test_teacher_can_create_and_publish_learning_material_with_attachment(): void
    {
        $this->actingAs($this->teacher);

        $fakePdf = UploadedFile::fake()->create('modul-fisika.pdf', 500, 'application/pdf');

        $response = $this->post(route('learning-materials.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'topic' => 'Bab 1 - Pengukuran',
            'title' => 'Besaran dan Satuan Fisika',
            'description' => 'Materi pengantar besaran pokok dan turunan.',
            'content' => 'Berikut penjelasan detail besaran pokok...',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'status' => 'published',
            'published_at' => now()->format('Y-m-d H:i:s'),
            'attachments' => [$fakePdf],
        ]);

        $response->assertRedirect(route('learning-materials.index'));
        $this->assertDatabaseHas('learning_materials', [
            'teaching_assignment_id' => $this->assignment->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'title' => 'Besaran dan Satuan Fisika',
            'status' => 'published',
        ]);

        $material = LearningMaterial::where('title', 'Besaran dan Satuan Fisika')->first();
        $this->assertNotNull($material);
        $this->assertCount(1, $material->attachments);
        $this->assertEquals('modul-fisika.pdf', $material->attachments->first()->file_name);
        Storage::disk('local')->assertExists($material->attachments->first()->file_path);
    }

    public function test_teacher_cannot_create_material_for_classroom_not_assigned(): void
    {
        // otherTeacher does not teach this assignment
        $this->actingAs($this->otherTeacher);

        $response = $this->post(route('learning-materials.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'title' => 'Materi Ilegal',
            'content' => 'Konten tanpa wewenang',
            'status' => 'published',
        ]);

        $response->assertForbidden();
    }

    public function test_student_can_view_published_material_and_complete_progress(): void
    {
        $material = LearningMaterial::create([
            'teaching_assignment_id' => $this->assignment->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Kinematika Gerak Lurus',
            'content' => 'Isi materi gerak lurus beraturan...',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        // Student 1 (in VII-A) should see it
        $this->actingAs($this->student1);
        $response = $this->get(route('student.materials.index'));
        $response->assertOk();
        $response->assertSee('Kinematika Gerak Lurus');

        $showResponse = $this->get(route('student.materials.show', $material));
        $showResponse->assertOk();

        // Mark as completed
        $completeResponse = $this->post(route('student.materials.complete', $material));
        $completeResponse->assertRedirect(route('student.materials.show', $material));

        $this->assertDatabaseHas('learning_material_progress', [
            'learning_material_id' => $material->id,
            'student_id' => $this->student1->id,
            'status' => 'completed',
        ]);
    }

    public function test_student_cannot_view_draft_material(): void
    {
        $draftMaterial = LearningMaterial::create([
            'teaching_assignment_id' => $this->assignment->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Materi Rahasia Masih Draft',
            'content' => 'Konten draft',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $this->actingAs($this->student1);
        $this->get(route('student.materials.show', $draftMaterial))->assertNotFound();
    }

    public function test_student_cannot_view_material_of_other_classroom(): void
    {
        $material = LearningMaterial::create([
            'teaching_assignment_id' => $this->assignment->id, // VII-A
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Materi Khusus VII-A',
            'content' => 'Konten untuk VII-A',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        // Student 2 is enrolled in VII-B, not VII-A
        $this->actingAs($this->student2);
        $this->get(route('student.materials.show', $material))->assertForbidden();
    }

    public function test_teacher_can_create_assignment_with_parameters(): void
    {
        $this->actingAs($this->teacher);

        $fakeDoc = UploadedFile::fake()->create('lembar-soal.pdf', 300, 'application/pdf');

        $response = $this->post(route('assignments.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'title' => 'Tugas 1: Hukum Newton',
            'instructions' => 'Kerjakan 5 soal perhitungan gaya dan percepatan.',
            'submission_type' => 'both',
            'due_date' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'max_score' => 100,
            'max_file_size_mb' => 10,
            'allowed_file_types' => 'pdf,doc,docx,zip,jpg,png',
            'max_attachments' => 3,
            'status' => 'published',
            'published_at' => now()->format('Y-m-d H:i:s'),
            'attachments' => [$fakeDoc],
        ]);

        $response->assertRedirect(route('assignments.index'));
        $this->assertDatabaseHas('assignments', [
            'teaching_assignment_id' => $this->assignment->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'title' => 'Tugas 1: Hukum Newton',
            'submission_type' => 'both',
            'max_score' => 100,
            'status' => 'published',
        ]);
    }

    public function test_student_can_submit_assignment_and_detect_late(): void
    {
        // 1. On-time assignment
        $onTimeAssignment = Assignment::create([
            'teaching_assignment_id' => $this->assignment->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Tugas Tepat Waktu',
            'instructions' => 'Kumpulkan sebelum lusa.',
            'submission_type' => 'both',
            'due_date' => now()->addDays(2),
            'max_score' => 100,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($this->student1);
        $fakeAnswerPdf = UploadedFile::fake()->create('jawaban-student1.pdf', 200, 'application/pdf');

        $submitResponse = $this->post(route('student.assignments.submit', $onTimeAssignment), [
            'student_notes' => 'Berikut lembar jawaban tugas saya.',
            'status' => 'submitted',
            'attachments' => [$fakeAnswerPdf],
        ]);

        $submitResponse->assertRedirect(route('student.assignments.show', $onTimeAssignment));

        $submission = AssignmentSubmission::where('assignment_id', $onTimeAssignment->id)
            ->where('student_id', $this->student1->id)
            ->first();

        $this->assertNotNull($submission);
        $this->assertEquals('submitted', $submission->status);
        $this->assertFalse($submission->is_late);
        $this->assertCount(1, $submission->attachments);

        // 2. Late assignment
        $lateAssignment = Assignment::create([
            'teaching_assignment_id' => $this->assignment->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Tugas Sudah Terlewat',
            'instructions' => 'Deadline kemarin.',
            'submission_type' => 'text',
            'due_date' => now()->subHours(5),
            'max_score' => 100,
            'status' => 'published',
            'published_at' => now()->subDays(2),
        ]);

        $lateSubmitResponse = $this->post(route('student.assignments.submit', $lateAssignment), [
            'student_notes' => 'Maaf terlambat mengumpulkan.',
            'status' => 'submitted',
        ]);

        $lateSubmitResponse->assertRedirect(route('student.assignments.show', $lateAssignment));

        $lateSubmission = AssignmentSubmission::where('assignment_id', $lateAssignment->id)
            ->where('student_id', $this->student1->id)
            ->first();

        $this->assertNotNull($lateSubmission);
        $this->assertTrue($lateSubmission->is_late);
    }

    public function test_teacher_can_grade_submission_and_publish_grade(): void
    {
        $assignment = Assignment::create([
            'teaching_assignment_id' => $this->assignment->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Tugas Dinilai',
            'instructions' => 'Instruksi tugas',
            'submission_type' => 'text',
            'due_date' => now()->addDays(2),
            'max_score' => 100,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student1->id,
            'attempt_number' => 1,
            'student_notes' => 'Jawaban lengkap saya.',
            'status' => 'submitted',
            'submitted_at' => now(),
            'is_late' => false,
        ]);

        // Teacher grades without publishing immediately
        $this->actingAs($this->teacher);
        $gradeResponse = $this->post(route('assignments.submissions.grade', [$assignment, $submission]), [
            'score' => 88.5,
            'teacher_feedback' => 'Penjelasan konsep sangat jelas.',
            'publish_grade' => false,
        ]);

        $gradeResponse->assertRedirect(route('assignments.submissions.show', [$assignment, $submission]));

        $submission->refresh();
        $this->assertEquals(88.5, $submission->score);
        $this->assertNull($submission->grade_published_at);

        // Student views assignment: should NOT see the unpublished score
        $this->actingAs($this->student1);
        $studentView = $this->get(route('student.assignments.show', $assignment));
        $studentView->assertOk();
        $studentView->assertDontSee('88.5');

        // Teacher publishes grade
        $this->actingAs($this->teacher);
        $publishResponse = $this->post(route('assignments.submissions.publish-grade', [$assignment, $submission]));
        $publishResponse->assertRedirect(route('assignments.submissions.show', [$assignment, $submission]));

        $submission->refresh();
        $this->assertNotNull($submission->grade_published_at);

        // Student views assignment again: score and feedback should now be visible
        $this->actingAs($this->student1);
        $studentViewAfter = $this->get(route('student.assignments.show', $assignment));
        $studentViewAfter->assertOk();
        $studentViewAfter->assertSee('88.5');
        $studentViewAfter->assertSee('Penjelasan konsep sangat jelas.');
    }

    public function test_teacher_can_request_revision_and_student_resubmits_with_history(): void
    {
        $assignment = Assignment::create([
            'teaching_assignment_id' => $this->assignment->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Tugas Uji Revisi',
            'instructions' => 'Instruksi',
            'submission_type' => 'both',
            'due_date' => now()->addDays(3),
            'max_score' => 100,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student1->id,
            'attempt_number' => 1,
            'content' => 'Draft jawaban awal nomor 1-5.',
            'status' => 'submitted',
            'submitted_at' => now(),
            'is_late' => false,
        ]);

        // Teacher requests revision
        $this->actingAs($this->teacher);
        $revResponse = $this->post(route('assignments.submissions.request-revision', [$assignment, $submission]), [
            'teacher_feedback' => 'Nomor 3 salah rumus, mohon hitung ulang.',
        ]);

        $revResponse->assertRedirect(route('assignments.submissions.show', [$assignment, $submission]));

        $submission->refresh();
        $this->assertEquals('revision_requested', $submission->status);
        $this->assertEquals('Nomor 3 salah rumus, mohon hitung ulang.', $submission->teacher_feedback);

        // Student submits revised answer
        $this->actingAs($this->student1);
        $resubmitResponse = $this->post(route('student.assignments.submit', $assignment), [
            'student_notes' => 'Sudah diperbaiki nomor 3 dengan rumus yang benar.',
            'status' => 'submitted',
        ]);

        $resubmitResponse->assertRedirect(route('student.assignments.show', $assignment));

        $submission->refresh();
        $this->assertEquals('resubmitted', $submission->status);
        $this->assertEquals(2, $submission->attempt_number);
        $this->assertEquals('Sudah diperbaiki nomor 3 dengan rumus yang benar.', $submission->student_notes);

        // History snapshot should exist for attempt 1
        $this->assertCount(1, $submission->histories);
        $history = $submission->histories->first();
        $this->assertEquals(1, $history->attempt_number);
        $this->assertEquals('Draft jawaban awal nomor 1-5.', $history->student_notes);
        $this->assertEquals('Nomor 3 salah rumus, mohon hitung ulang.', $history->teacher_feedback);
    }

    public function test_file_download_authorization_and_isolation(): void
    {
        $material = LearningMaterial::create([
            'teaching_assignment_id' => $this->assignment->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Materi Terisolasi',
            'content' => 'Konten',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $fakeFile = UploadedFile::fake()->create('rahasia.pdf', 100);
        $path = $fakeFile->store('materials/'.$material->id, 'local');

        $attachment = LearningMaterialAttachment::create([
            'learning_material_id' => $material->id,
            'file_name' => 'rahasia.pdf',
            'file_path' => $path,
            'file_size' => 100,
            'mime_type' => 'application/pdf',
        ]);

        // Student 1 (in VII-A) can download
        $this->actingAs($this->student1);
        $this->get(route('student.materials.attachments.download', $attachment))->assertOk();

        // Student 2 (in VII-B) CANNOT download
        $this->actingAs($this->student2);
        $this->get(route('student.materials.attachments.download', $attachment))->assertForbidden();

        // Other teacher from other school CANNOT download
        $this->actingAs($this->otherTeacher);
        $this->get(route('learning-materials.attachments.download', $attachment))->assertForbidden();
    }
}
