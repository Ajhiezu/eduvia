<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Classroom;
use App\Models\ConversationMessage;
use App\Models\FeeCategory;
use App\Models\Invoice;
use App\Models\LearningMaterial;
use App\Models\LearningMaterialAttachment;
use App\Models\ParentLinkCode;
use App\Models\ParentStudentRelation;
use App\Models\ParentTeacherConversation;
use App\Models\Payment;
use App\Models\School;
use App\Models\Semester;
use App\Models\StudentReportCard;
use App\Models\StudentReportCardItem;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileApiV1CompletenessTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected User $student;

    protected User $parent;

    protected User $teacher;

    protected Classroom $classroom;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected Subject $subject;

    protected TeachingAssignment $teachingAssignment;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        $this->school = School::factory()->create(['name' => 'SMA Eduvia Jakarta', 'is_active' => true]);

        $this->classroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'XII RPL 1',
        ]);

        $this->student = User::factory()->student()->create([
            'school_id' => $this->school->id,
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@eduvia.id',
        ]);

        $this->academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);
        $this->semester = Semester::factory()->create(['academic_year_id' => $this->academicYear->id]);
        $this->subject = Subject::factory()->create(['school_id' => $this->school->id, 'name' => 'Pemrograman Mobile']);

        \App\Models\ClassroomStudent::factory()->create([
            'classroom_id' => $this->classroom->id,
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'enrollment_status' => 'active',
        ]);

        $this->parent = User::factory()->parent()->create([
            'school_id' => $this->school->id,
            'name' => 'Pak Joko Santoso',
            'email' => 'joko.santoso@eduvia.id',
        ]);

        $this->teacher = User::factory()->teacher()->create([
            'school_id' => $this->school->id,
            'name' => 'Bu Siti Guru',
            'email' => 'siti.guru@eduvia.id',
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

        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship_type' => 'father',
            'status' => 'active',
        ]);
    }

    public function test_student_can_submit_assignment_with_file_upload(): void
    {
        Sanctum::actingAs($this->student, ['role:student']);

        $assignment = Assignment::create([
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroom->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'teaching_assignment_id' => $this->teachingAssignment->id,
            'title' => 'Tugas Pemrograman Mobile 1',
            'instructions' => 'Kerjakan modul flutter dan upload jawaban.',
            'status' => 'published',
            'due_date' => now()->addDays(2),
            'max_score' => 100,
        ]);

        $file = UploadedFile::fake()->create('jawaban_tugas.pdf', 500, 'application/pdf');

        $response = $this->postJson("/api/v1/student/assignments/{$assignment->id}/submit", [
            'content' => 'Berikut adalah hasil pengerjaan aplikasi Flutter saya.',
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.assignment_id', $assignment->id)
            ->assertJsonPath('data.status', 'submitted');

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'is_late' => false,
        ]);

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $this->student->id)
            ->first();

        $this->assertNotNull($submission);
        $this->assertCount(1, $submission->attachments);
    }

    public function test_student_cannot_submit_to_different_classroom_assignment(): void
    {
        Sanctum::actingAs($this->student, ['role:student']);

        $otherClassroom = Classroom::factory()->create(['school_id' => $this->school->id]);
        $otherTeachingAssignment = TeachingAssignment::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'classroom_id' => $otherClassroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'is_active' => true,
        ]);

        $assignment = Assignment::create([
            'school_id' => $this->school->id,
            'classroom_id' => $otherClassroom->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'teaching_assignment_id' => $otherTeachingAssignment->id,
            'title' => 'Tugas Kelas Lain',
            'instructions' => 'Instruksi tugas kelas lain.',
            'status' => 'published',
            'due_date' => now()->addDays(2),
            'max_score' => 100,
        ]);

        $response = $this->postJson("/api/v1/student/assignments/{$assignment->id}/submit", [
            'content' => 'Mencoba submit ke kelas lain',
        ]);

        $response->assertStatus(403);
    }

    public function test_student_can_download_material_attachment(): void
    {
        Sanctum::actingAs($this->student, ['role:student']);

        $material = LearningMaterial::create([
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroom->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'teaching_assignment_id' => $this->teachingAssignment->id,
            'title' => 'Modul 1 Flutter REST API',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $filePath = 'materials/attachments/modul1.pdf';
        Storage::disk('local')->put($filePath, 'Dummy PDF content for testing');

        $attachment = LearningMaterialAttachment::create([
            'learning_material_id' => $material->id,
            'file_path' => $filePath,
            'file_name' => 'modul1.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->get("/api/v1/student/materials/{$material->id}/attachments/{$attachment->id}/download");

        $response->assertStatus(200);
        $this->assertDatabaseHas('learning_material_progress', [
            'learning_material_id' => $material->id,
            'student_id' => $this->student->id,
            'status' => 'completed',
        ]);
    }

    public function test_student_can_view_published_report_cards(): void
    {
        Sanctum::actingAs($this->student, ['role:student']);

        $academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);
        $semester = Semester::factory()->create(['academic_year_id' => $academicYear->id]);
        $subject = Subject::factory()->create(['school_id' => $this->school->id, 'name' => 'Matematika']);

        $reportCard = StudentReportCard::create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'homeroom_teacher_id' => $this->teacher->id,
            'status' => StudentReportCard::STATUS_PUBLISHED,
            'homeroom_notes' => 'Prestasi ananda sangat memuaskan semester ini.',
            'attendance_summary' => ['present' => 90, 'sick' => 2, 'permit' => 1, 'absent' => 0],
        ]);

        StudentReportCardItem::create([
            'student_report_card_id' => $reportCard->id,
            'subject_id' => $subject->id,
            'subject_name' => 'Matematika',
            'subject_code' => 'MAT-12',
            'passing_grade' => 75,
            'final_score' => 95,
            'predicate' => 'A',
            'is_passed' => true,
            'order_index' => 1,
        ]);

        $response = $this->getJson('/api/v1/student/reports');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.homeroom_notes', 'Prestasi ananda sangat memuaskan semester ini.')
            ->assertJsonPath('data.0.average_score', 95)
            ->assertJsonPath('data.0.grades.0.grade', 'A');
    }

    public function test_student_can_participate_in_teacher_conversations(): void
    {
        Sanctum::actingAs($this->student, ['role:student']);

        $conversation = ParentTeacherConversation::create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'parent_id' => $this->parent->id,
            'teacher_id' => $this->teacher->id,
            'subject' => 'Konsultasi Belajar & Bimbingan Karir',
            'status' => 'active',
            'last_message_at' => now(),
        ]);

        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->teacher->id,
            'body' => 'Budi, bagaimana progress tugas akhir sekolahmu?',
            'is_read' => false,
        ]);

        // List conversations
        $listRes = $this->getJson('/api/v1/student/conversations');
        $listRes->assertStatus(200)->assertJsonCount(1, 'data');

        // View messages
        $msgRes = $this->getJson("/api/v1/student/conversations/{$conversation->id}/messages");
        $msgRes->assertStatus(200)
            ->assertJsonPath('data.messages.0.body', 'Budi, bagaimana progress tugas akhir sekolahmu?');

        // Reply to conversation
        $replyRes = $this->postJson("/api/v1/student/conversations/{$conversation->id}/messages", [
            'body' => 'Alhamdulillah sudah 90% Bu Siti, tinggal laporan akhir.',
        ]);
        $replyRes->assertStatus(200)->assertJsonPath('success', true);

        $this->assertDatabaseHas('conversation_messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $this->student->id,
            'body' => 'Alhamdulillah sudah 90% Bu Siti, tinggal laporan akhir.',
        ]);
    }

    public function test_parent_can_claim_link_code_to_connect_student(): void
    {
        Sanctum::actingAs($this->parent, ['role:parent']);

        $newStudent = User::factory()->student()->create([
            'school_id' => $this->school->id,
            'name' => 'Siti Nurhaliza',
        ]);

        $linkCode = ParentLinkCode::create([
            'school_id' => $this->school->id,
            'student_id' => $newStudent->id,
            'code' => 'EDUV-KLM9-8877',
            'relationship_type' => 'father',
            'expires_at' => now()->addDays(7),
            'max_uses' => 2,
            'used_count' => 0,
            'created_by' => $this->teacher->id,
        ]);

        $response = $this->postJson('/api/v1/parent/claim-code', [
            'code' => 'EDUV-KLM9-8877',
            'relationship_type' => 'father',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.student_name', 'Siti Nurhaliza');

        $this->assertDatabaseHas('parent_student_relations', [
            'parent_id' => $this->parent->id,
            'student_id' => $newStudent->id,
            'status' => 'active',
        ]);

        $linkCode->refresh();
        $this->assertEquals(1, $linkCode->used_count);
        $this->assertEquals($this->parent->id, $linkCode->used_by);
    }

    public function test_parent_cannot_claim_invalid_or_expired_code(): void
    {
        Sanctum::actingAs($this->parent, ['role:parent']);

        $expiredCode = ParentLinkCode::create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'code' => 'EDUV-EXPD-9999',
            'relationship_type' => 'father',
            'expires_at' => now()->subDay(),
            'max_uses' => 1,
            'used_count' => 0,
            'created_by' => $this->teacher->id,
        ]);

        $response = $this->postJson('/api/v1/parent/claim-code', [
            'code' => 'EDUV-EXPD-9999',
        ]);

        $response->assertStatus(400);

        $fakeResponse = $this->postJson('/api/v1/parent/claim-code', [
            'code' => 'EDUV-NOT-FOUND',
        ]);

        $fakeResponse->assertStatus(404);
    }

    public function test_parent_can_submit_invoice_payment_with_proof(): void
    {
        Sanctum::actingAs($this->parent, ['role:parent']);

        $feeCategory = FeeCategory::factory()->create(['school_id' => $this->school->id]);
        $academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);

        $invoice = Invoice::create([
            'school_id' => $this->school->id,
            'invoice_number' => 'INV-202610-BUDI-01',
            'student_id' => $this->student->id,
            'academic_year_id' => $academicYear->id,
            'fee_category_id' => $feeCategory->id,
            'title' => 'SPP Bulan Oktober 2026',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'subtotal' => 500000,
            'discount_amount' => 0,
            'total_amount' => 500000,
            'paid_amount' => 0,
            'status' => Invoice::STATUS_ISSUED,
            'created_by' => $this->teacher->id,
        ]);

        $proofFile = UploadedFile::fake()->image('bukti_transfer_bca.jpg', 800, 600);

        $response = $this->postJson("/api/v1/parent/invoices/{$invoice->id}/pay", [
            'amount' => 500000,
            'payment_method' => 'bank_transfer',
            'reference_number' => 'BCA-TRX-987654321',
            'proof' => $proofFile,
            'notes' => 'Pembayaran SPP Oktober atas nama Budi Santoso',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', Payment::STATUS_PENDING_VERIFICATION)
            ->assertJsonPath('data.amount', 500000);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'student_id' => $this->student->id,
            'paid_by' => $this->parent->id,
            'amount' => 500000,
            'status' => Payment::STATUS_PENDING_VERIFICATION,
        ]);
    }

    public function test_parent_cannot_pay_invoice_of_unlinked_student(): void
    {
        Sanctum::actingAs($this->parent, ['role:parent']);

        $otherStudent = User::factory()->student()->create(['school_id' => $this->school->id]);
        $feeCategory = FeeCategory::factory()->create(['school_id' => $this->school->id]);
        $academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);

        $otherInvoice = Invoice::create([
            'school_id' => $this->school->id,
            'invoice_number' => 'INV-OTHER-999',
            'student_id' => $otherStudent->id,
            'academic_year_id' => $academicYear->id,
            'fee_category_id' => $feeCategory->id,
            'title' => 'SPP Orang Lain',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(5)->toDateString(),
            'subtotal' => 300000,
            'discount_amount' => 0,
            'total_amount' => 300000,
            'paid_amount' => 0,
            'status' => Invoice::STATUS_ISSUED,
            'created_by' => $this->teacher->id,
        ]);

        $proofFile = UploadedFile::fake()->image('bukti.jpg');

        $response = $this->postJson("/api/v1/parent/invoices/{$otherInvoice->id}/pay", [
            'amount' => 300000,
            'proof' => $proofFile,
        ]);

        $response->assertStatus(403);
    }
}
