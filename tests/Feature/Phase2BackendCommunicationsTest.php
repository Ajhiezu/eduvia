<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionItem;
use App\Models\LessonSchedule;
use App\Models\NotificationPreference;
use App\Models\ParentStudentRelation;
use App\Models\School;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\Notification\NotificationPayload;
use App\Services\Notification\NotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Phase2BackendCommunicationsTest extends TestCase
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
        Storage::fake('public');

        $this->school = School::factory()->create(['name' => 'SMA Eduvia Jakarta', 'is_active' => true]);

        $this->academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);
        $this->semester = Semester::factory()->create(['academic_year_id' => $this->academicYear->id]);

        $this->teacher = User::factory()->teacher()->create([
            'school_id' => $this->school->id,
            'name' => 'Bu Siti Guru',
            'email' => 'siti.guru@eduvia.id',
            'phone' => '081234567890',
        ]);

        $this->classroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'homeroom_teacher_id' => $this->teacher->id,
            'name' => 'XII RPL 1',
        ]);

        $this->student = User::factory()->student()->create([
            'school_id' => $this->school->id,
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@eduvia.id',
        ]);

        ClassroomStudent::factory()->create([
            'classroom_id' => $this->classroom->id,
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'enrollment_status' => 'active',
        ]);

        $this->parent = User::factory()->parent()->create([
            'school_id' => $this->school->id,
            'name' => 'Pak Joko Santoso',
            'email' => 'joko.santoso@eduvia.id',
            'phone' => '089876543210',
        ]);

        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship_type' => 'father',
            'status' => 'active',
        ]);

        $this->subject = Subject::factory()->create(['school_id' => $this->school->id, 'name' => 'Rekayasa Perangkat Lunak']);

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

    public function test_public_version_check_endpoint(): void
    {
        $response = $this->getJson('/api/v1/app/version-check');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['min_version', 'latest_version', 'force_update', 'maintenance_mode'],
            ]);
    }

    public function test_device_token_registration_and_revocation(): void
    {
        Sanctum::actingAs($this->student, ['role:student']);

        // Register token
        $response = $this->postJson('/api/v1/notifications/device-token', [
            'token' => 'fcm_sample_token_xyz_123',
            'platform' => 'android',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $this->student->id,
            'token' => 'fcm_sample_token_xyz_123',
            'is_active' => true,
        ]);

        // Revoke token
        $delResponse = $this->deleteJson('/api/v1/notifications/device-token', [
            'token' => 'fcm_sample_token_xyz_123',
        ]);

        $delResponse->assertOk()
            ->assertJsonPath('data.removed', true);

        $this->assertDatabaseMissing('device_tokens', [
            'user_id' => $this->student->id,
            'token' => 'fcm_sample_token_xyz_123',
        ]);
    }

    public function test_notification_preferences_and_quiet_hours_management(): void
    {
        Sanctum::actingAs($this->parent, ['role:parent']);

        $getRes = $this->getJson('/api/v1/notifications/preferences');
        $getRes->assertOk()->assertJsonPath('success', true);

        $putRes = $this->putJson('/api/v1/notifications/preferences', [
            'channel_push' => true,
            'channel_whatsapp' => true,
            'quiet_hours_enabled' => true,
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '05:30',
            'whatsapp_opt_in' => true,
            'whatsapp_number' => '089876543210',
        ]);

        $putRes->assertOk()
            ->assertJsonPath('data.whatsapp_opt_in', true)
            ->assertJsonPath('data.quiet_hours_start', '22:00');

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $this->parent->id,
            'whatsapp_opt_in' => true,
            'quiet_hours_start' => '22:00',
        ]);
    }

    public function test_notification_service_quiet_hours_suppression_and_urgent_bypass(): void
    {
        NotificationPreference::create([
            'school_id' => $this->school->id,
            'user_id' => $this->parent->id,
            'channel_push' => true,
            'quiet_hours_enabled' => true,
            'quiet_hours_start' => '00:00',
            'quiet_hours_end' => '23:59', // All day quiet for testing
        ]);

        $service = app(NotificationService::class);

        // Non-urgent payload should be suppressed during quiet hours
        $normalPayload = new NotificationPayload(
            userId: $this->parent->id,
            schoolId: $this->school->id,
            eventKey: 'general.update',
            title: 'Info Terkini',
            body: 'Ini pesan berkala biasa',
            isUrgent: false
        );
        $res = $service->send($normalPayload);
        $this->assertArrayHasKey('quiet_hours', $res);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->parent->id,
            'status' => 'suppressed_quiet_hours',
        ]);

        // Urgent payload bypasses quiet hours
        $urgentPayload = new NotificationPayload(
            userId: $this->parent->id,
            schoolId: $this->school->id,
            eventKey: 'emergency.alert',
            title: 'Pemberitahuan Darurat',
            body: 'Sekolah diliburkan karena cuaca ekstrem',
            isUrgent: true
        );
        $resUrgent = $service->send($urgentPayload);
        $this->assertArrayNotHasKey('quiet_hours', $resUrgent);
    }

    public function test_digital_leave_request_workflow_from_parent_to_teacher(): void
    {
        Sanctum::actingAs($this->parent, ['role:parent']);

        $startDate = Carbon::today()->toDateString();
        $endDate = Carbon::today()->addDays(2)->toDateString();

        // 1. Parent submits leave request
        $file = UploadedFile::fake()->create('surat_dokter.pdf', 300, 'application/pdf');

        $storeRes = $this->postJson('/api/v1/parent/leaves', [
            'student_id' => $this->student->id,
            'leave_type' => 'sick',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => 'Ananda demam tinggi dan disarankan dokter istirahat.',
            'attachment' => $file,
        ]);

        $storeRes->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending');

        $leaveId = $storeRes->json('data.id');

        // 2. Parent checks leave list
        $indexRes = $this->getJson('/api/v1/parent/leaves');
        $indexRes->assertOk()->assertJsonPath('meta.total', 1);

        // 3. Teacher reviews and approves leave request
        Sanctum::actingAs($this->teacher, ['role:teacher']);

        $teacherLeavesRes = $this->getJson('/api/v1/teacher/leaves');
        $teacherLeavesRes->assertOk()->assertJsonPath('meta.total', 1);

        $reviewRes = $this->postJson("/api/v1/teacher/leaves/{$leaveId}/review", [
            'status' => 'approved',
        ]);

        $reviewRes->assertOk()
            ->assertJsonPath('data.status', 'approved');

        // Verify attendance record automatically synced to 'sick'
        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $this->student->id,
            'status' => 'sick',
        ]);
    }

    public function test_teacher_dashboard_schedules_and_classes(): void
    {
        LessonSchedule::create([
            'school_id' => $this->school->id,
            'teaching_assignment_id' => $this->teachingAssignment->id,
            'day_of_week' => Carbon::now()->dayOfWeekIso,
            'start_time' => '08:00:00',
            'end_time' => '09:30:00',
            'room' => 'Lab RPL 1',
            'is_active' => true,
        ]);

        Sanctum::actingAs($this->teacher, ['role:teacher']);

        $dashRes = $this->getJson('/api/v1/teacher/dashboard');
        $dashRes->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.teacher.name', 'Bu Siti Guru');

        $schedRes = $this->getJson('/api/v1/teacher/schedules');
        $schedRes->assertOk()
            ->assertJsonPath('success', true);

        $classesRes = $this->getJson('/api/v1/teacher/classes');
        $classesRes->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_teacher_attendance_session_and_idempotent_recording(): void
    {
        Sanctum::actingAs($this->teacher, ['role:teacher']);

        $today = Carbon::today()->toDateString();

        $sessionRes = $this->postJson('/api/v1/teacher/attendance/sessions', [
            'classroom_id' => $this->classroom->id,
            'teaching_assignment_id' => $this->teachingAssignment->id,
            'date' => $today,
            'session_type' => 'daily',
            'topic' => 'Pertemuan Basis Data',
        ]);

        $sessionRes->assertCreated()
            ->assertJsonPath('success', true);

        $sessionId = $sessionRes->json('data.id');

        // Save records idempotently
        $saveRes = $this->postJson("/api/v1/teacher/attendance/sessions/{$sessionId}/records", [
            'records' => [
                [
                    'student_id' => $this->student->id,
                    'status' => 'present',
                    'notes' => 'Hadir tepat waktu',
                ],
            ],
            'finalize' => true,
        ]);

        $saveRes->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.summary.present', 1);

        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $sessionId,
            'student_id' => $this->student->id,
            'status' => 'present',
        ]);
    }

    public function test_teacher_assignment_grading(): void
    {
        $assignment = Assignment::create([
            'school_id' => $this->school->id,
            'teacher_id' => $this->teacher->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teaching_assignment_id' => $this->teachingAssignment->id,
            'title' => 'Tugas REST API Laravel',
            'instructions' => 'Kumpulkan link repository tugas.',
            'status' => 'published',
            'due_date' => now()->addDays(2),
            'max_score' => 100,
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
            'content' => 'Link repository tugas selesai.',
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($this->teacher, ['role:teacher']);

        $submissionsListRes = $this->getJson("/api/v1/teacher/assignments/{$assignment->id}/submissions");
        $submissionsListRes->assertOk()->assertJsonPath('meta.total', 1);

        $gradeRes = $this->postJson("/api/v1/teacher/assignments/{$assignment->id}/submissions/{$submission->id}/grade", [
            'score' => 95.5,
            'teacher_feedback' => 'Kerja yang sangat rapi dan struktur endpoint konsisten.',
            'publish_now' => true,
        ]);

        $gradeRes->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $submission->id,
            'score' => 95.50,
            'status' => 'graded',
        ]);
    }

    public function test_teacher_chat_settings_update(): void
    {
        Sanctum::actingAs($this->teacher, ['role:teacher']);

        $response = $this->putJson('/api/v1/teacher/chat-settings', [
            'chat_service_hours_start' => '07:30',
            'chat_service_hours_end' => '16:00',
            'chat_service_days' => [1, 2, 3, 4, 5],
            'chat_auto_reply_enabled' => true,
            'chat_auto_reply_message' => 'Terima kasih telah menghubungi. Pesan akan dibalas saat jam kerja.',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.chat_auto_reply_enabled', true)
            ->assertJsonPath('data.chat_service_hours_start', '07:30');

        $this->assertDatabaseHas('users', [
            'id' => $this->teacher->id,
            'chat_auto_reply_enabled' => true,
            'chat_service_hours_start' => '07:30',
        ]);
    }

    public function test_cbt_batch_sync_conflict_resolution_and_integrity_logs(): void
    {
        $exam = Exam::factory()->create([
            'school_id' => $this->school->id,
            'subject_id' => $this->subject->id,
            'title' => 'Ujian Akhir Semester Pemrograman',
            'status' => 'published',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
            'duration_minutes' => 90,
        ]);

        $question = ExamQuestion::factory()->create([
            'school_id' => $this->school->id,
            'created_by' => $this->teacher->id,
            'subject_id' => $this->subject->id,
        ]);

        ExamQuestionItem::create([
            'exam_id' => $exam->id,
            'question_id' => $question->id,
            'order' => 1,
            'score' => 10.0,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'status' => 'in_progress',
            'started_at' => now()->subMinutes(10),
            'expires_at' => now()->addMinutes(80),
            'remaining_seconds' => 4800,
            'max_duration_seconds' => 5400,
            'questions_snapshot' => [],
        ]);

        Sanctum::actingAs($this->student, ['role:student']);

        // 1. Initial batch sync with sequence 5
        $batchRes1 = $this->postJson("/api/v1/student/cbt/{$exam->id}/save-answers-batch", [
            'attempt_id' => $attempt->id,
            'answers' => [
                [
                    'question_id' => $question->id,
                    'answer' => 'A',
                    'client_sequence_id' => 5,
                    'client_timestamp' => now()->getTimestampMs(),
                ],
            ],
        ]);

        $batchRes1->assertOk()
            ->assertJsonPath('data.synced_count', 1);

        $answerRow1 = ExamAnswer::where('exam_attempt_id', $attempt->id)
            ->where('question_id', $question->id)
            ->first();
        $this->assertNotNull($answerRow1);
        $this->assertEquals('A', $answerRow1->answer);
        $this->assertEquals(5, $answerRow1->client_sequence_id);

        // 2. Stale delayed batch packet arrives with sequence 3 -> should NOT overwrite newer sequence 5!
        $staleBatchRes = $this->postJson("/api/v1/student/cbt/{$exam->id}/save-answers-batch", [
            'attempt_id' => $attempt->id,
            'answers' => [
                [
                    'question_id' => $question->id,
                    'answer' => 'B', // Outdated answer
                    'client_sequence_id' => 3, // Older sequence
                    'client_timestamp' => now()->subMinutes(2)->getTimestampMs(),
                ],
            ],
        ]);

        $staleBatchRes->assertOk();

        // Must still be 'A' with sequence 5
        $answerRow2 = ExamAnswer::where('exam_attempt_id', $attempt->id)
            ->where('question_id', $question->id)
            ->first();
        $this->assertNotNull($answerRow2);
        $this->assertEquals('A', $answerRow2->answer);
        $this->assertEquals(5, $answerRow2->client_sequence_id);

        // 3. Log CBT integrity event (e.g. app backgrounded or connection loss)
        $integrityRes = $this->postJson("/api/v1/student/cbt/{$exam->id}/integrity-events", [
            'attempt_id' => $attempt->id,
            'event_type' => 'app_backgrounded',
            'details' => ['battery' => 78, 'reason' => 'incoming_call'],
        ]);

        $integrityRes->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('exam_integrity_logs', [
            'exam_attempt_id' => $attempt->id,
            'event_type' => 'app_backgrounded',
        ]);
    }

    public function test_student_unified_agenda_endpoint(): void
    {
        Assignment::create([
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'teaching_assignment_id' => $this->teachingAssignment->id,
            'title' => 'Tugas Algoritma',
            'instructions' => 'Kerjakan latihan.',
            'status' => 'published',
            'due_date' => Carbon::now()->addDays(2),
            'max_score' => 100,
        ]);

        Sanctum::actingAs($this->student, ['role:student']);

        $response = $this->getJson('/api/v1/student/agenda');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'type', 'title', 'subject', 'date', 'time', 'datetime', 'is_urgent'],
                ],
            ]);
    }
}
