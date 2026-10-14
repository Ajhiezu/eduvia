<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private AcademicYear $academicYear;

    private Semester $semester;

    private Classroom $classroom;

    private Subject $subject;

    private User $teacher;

    private User $otherTeacher;

    private User $student1;

    private User $student2;

    private TeachingAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create();
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
        $this->subject = Subject::create([
            'school_id' => $this->school->id,
            'code' => 'MAT',
            'name' => 'Matematika',
            'is_active' => true,
        ]);

        $this->teacher = User::factory()->create(['role' => UserRole::TEACHER]);
        $this->otherTeacher = User::factory()->create(['role' => UserRole::TEACHER]);
        $this->student1 = User::factory()->create(['role' => UserRole::STUDENT]);
        $this->student2 = User::factory()->create(['role' => UserRole::STUDENT]);

        // Place students into classroom
        $this->classroom->addStudent($this->student1);
        $this->classroom->addStudent($this->student2);

        // Teaching assignment for teacher
        $this->assignment = TeachingAssignment::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'hours_per_week' => 4,
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_attendance_sessions(): void
    {
        $this->get(route('attendance-sessions.index'))->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_teacher_attendance_management(): void
    {
        $this->actingAs($this->student1)->get(route('attendance-sessions.index'))->assertStatus(403);
        $this->actingAs($this->student1)->get(route('attendance-sessions.create'))->assertStatus(403);
    }

    public function test_student_can_view_own_attendance_records(): void
    {
        $session = AttendanceSession::create([
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroom->id,
            'teaching_assignment_id' => $this->assignment->id,
            'academic_year_id' => $this->academicYear->id,
            'teacher_id' => $this->teacher->id,
            'meeting_number' => 1,
            'date' => '2026-07-20',
            'status' => 'finalized',
        ]);

        AttendanceRecord::create([
            'attendance_session_id' => $session->id,
            'student_id' => $this->student1->id,
            'status' => 'present',
        ]);

        $response = $this->actingAs($this->student1)->get(route('attendance.my'));

        $response->assertStatus(200);
        $response->assertSee('Rekapitulasi Presensi Saya');
        $response->assertSee('Matematika');
        $response->assertSee('Hadir');
    }

    public function test_teacher_can_view_attendance_sessions_list(): void
    {
        AttendanceSession::create([
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroom->id,
            'teaching_assignment_id' => $this->assignment->id,
            'academic_year_id' => $this->academicYear->id,
            'teacher_id' => $this->teacher->id,
            'meeting_number' => 1,
            'date' => '2026-07-20',
            'topic' => 'Aljabar Dasar',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->teacher)->get(route('attendance-sessions.index'));

        $response->assertStatus(200);
        $response->assertSee('Pertemuan Ke-1');
        $response->assertSee('Aljabar Dasar');
    }

    public function test_teacher_can_create_attendance_session(): void
    {
        $response = $this->actingAs($this->teacher)->post(route('attendance-sessions.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'meeting_number' => 1,
            'date' => '2026-07-20',
            'start_time' => '07:30',
            'end_time' => '09:00',
            'topic' => 'Persamaan Linier',
            'notes' => 'Sesi awal',
        ]);

        $session = AttendanceSession::where('meeting_number', 1)->first();
        $this->assertNotNull($session);
        $response->assertRedirect(route('attendance-sessions.show', $session));

        $this->assertDatabaseHas('attendance_sessions', [
            'teaching_assignment_id' => $this->assignment->id,
            'meeting_number' => 1,
            'topic' => 'Persamaan Linier',
            'status' => 'draft',
        ]);

        // Verify active students in the classroom were pre-populated with attendance records
        $this->assertEquals(2, $session->records()->count());
        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->id,
            'student_id' => $this->student1->id,
            'status' => 'present',
        ]);
    }

    public function test_cannot_create_duplicate_attendance_session_for_same_assignment_and_meeting_date(): void
    {
        AttendanceSession::create([
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroom->id,
            'teaching_assignment_id' => $this->assignment->id,
            'academic_year_id' => $this->academicYear->id,
            'teacher_id' => $this->teacher->id,
            'meeting_number' => 1,
            'date' => '2026-07-20',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->teacher)->post(route('attendance-sessions.store'), [
            'teaching_assignment_id' => $this->assignment->id,
            'meeting_number' => 1,
            'date' => '2026-07-20',
        ]);

        $response->assertSessionHasErrors('meeting_number');
    }

    public function test_teacher_can_update_attendance_records_as_draft(): void
    {
        $session = AttendanceSession::create([
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroom->id,
            'teaching_assignment_id' => $this->assignment->id,
            'academic_year_id' => $this->academicYear->id,
            'teacher_id' => $this->teacher->id,
            'meeting_number' => 1,
            'date' => '2026-07-20',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->teacher)->put(route('attendance-sessions.update', $session), [
            'topic' => 'Geometri Dasar',
            'action' => 'save_draft',
            'records' => [
                [
                    'student_id' => $this->student1->id,
                    'status' => 'present',
                    'notes' => 'Tepat waktu',
                ],
                [
                    'student_id' => $this->student2->id,
                    'status' => 'sick',
                    'notes' => 'Surat dokter',
                ],
            ],
        ]);

        $response->assertRedirect(route('attendance-sessions.show', $session));

        $this->assertEquals('draft', $session->fresh()->status);
        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->id,
            'student_id' => $this->student2->id,
            'status' => 'sick',
            'notes' => 'Surat dokter',
        ]);
    }

    public function test_teacher_can_finalize_attendance_session(): void
    {
        $session = AttendanceSession::create([
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroom->id,
            'teaching_assignment_id' => $this->assignment->id,
            'academic_year_id' => $this->academicYear->id,
            'teacher_id' => $this->teacher->id,
            'meeting_number' => 1,
            'date' => '2026-07-20',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->teacher)->post(route('attendance-sessions.finalize', $session));

        $response->assertRedirect(route('attendance-sessions.show', $session));

        $this->assertEquals('finalized', $session->fresh()->status);
        $this->assertNotNull($session->fresh()->finalized_at);
        $this->assertEquals($this->teacher->id, $session->fresh()->finalized_by);
    }

    public function test_teacher_correction_on_finalized_session_creates_audit_log(): void
    {
        $session = AttendanceSession::create([
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroom->id,
            'teaching_assignment_id' => $this->assignment->id,
            'academic_year_id' => $this->academicYear->id,
            'teacher_id' => $this->teacher->id,
            'meeting_number' => 1,
            'date' => '2026-07-20',
            'status' => 'finalized',
        ]);

        $record = AttendanceRecord::create([
            'attendance_session_id' => $session->id,
            'student_id' => $this->student1->id,
            'status' => 'absent',
        ]);

        $response = $this->actingAs($this->teacher)->put(route('attendance-sessions.update', $session), [
            'action' => 'finalize',
            'correction_reason' => 'Siswa ternyata sakit ada surat dokter susulan',
            'records' => [
                [
                    'student_id' => $this->student1->id,
                    'status' => 'sick',
                    'notes' => 'Ralat surat dokter',
                ],
            ],
        ]);

        $response->assertRedirect(route('attendance-sessions.show', $session));

        $this->assertDatabaseHas('attendance_records', [
            'id' => $record->id,
            'status' => 'sick',
        ]);

        $this->assertDatabaseHas('attendance_audit_logs', [
            'attendance_record_id' => $record->id,
            'user_id' => $this->teacher->id,
            'previous_status' => 'absent',
            'new_status' => 'sick',
            'reason' => 'Siswa ternyata sakit ada surat dokter susulan',
        ]);
    }

    public function test_teacher_can_reopen_finalized_session(): void
    {
        $session = AttendanceSession::create([
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroom->id,
            'teaching_assignment_id' => $this->assignment->id,
            'academic_year_id' => $this->academicYear->id,
            'teacher_id' => $this->teacher->id,
            'meeting_number' => 1,
            'date' => '2026-07-20',
            'status' => 'finalized',
        ]);

        $response = $this->actingAs($this->teacher)->post(route('attendance-sessions.reopen', $session), [
            'reason' => 'Perlu pengecekan ulang absensi',
        ]);

        $response->assertRedirect(route('attendance-sessions.show', $session));
        $this->assertEquals('draft', $session->fresh()->status);
        $this->assertStringContainsString('Perlu pengecekan ulang absensi', $session->fresh()->notes);
    }

    public function test_teacher_cannot_access_another_teachers_attendance_session(): void
    {
        $session = AttendanceSession::create([
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroom->id,
            'teaching_assignment_id' => $this->assignment->id,
            'academic_year_id' => $this->academicYear->id,
            'teacher_id' => $this->teacher->id,
            'meeting_number' => 1,
            'date' => '2026-07-20',
            'status' => 'draft',
        ]);

        $this->actingAs($this->otherTeacher)->get(route('attendance-sessions.show', $session))->assertStatus(403);
    }

    public function test_attendance_summary_and_report_calculates_correct_percentages(): void
    {
        $session = AttendanceSession::create([
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroom->id,
            'teaching_assignment_id' => $this->assignment->id,
            'academic_year_id' => $this->academicYear->id,
            'teacher_id' => $this->teacher->id,
            'meeting_number' => 1,
            'date' => '2026-07-20',
            'status' => 'finalized',
        ]);

        // Student 1: present, Student 2: late -> both count as attendance
        AttendanceRecord::create([
            'attendance_session_id' => $session->id,
            'student_id' => $this->student1->id,
            'status' => 'present',
        ]);
        AttendanceRecord::create([
            'attendance_session_id' => $session->id,
            'student_id' => $this->student2->id,
            'status' => 'late',
        ]);

        $summary = $session->attendanceSummary();
        $this->assertEquals(1, $summary['present']);
        $this->assertEquals(1, $summary['late']);
        $this->assertEquals(2, $summary['total']);
        $this->assertEquals(100.0, $summary['rate']);

        // View attendance report
        $response = $this->actingAs($this->teacher)->get(route('attendance.report', [
            'teaching_assignment_id' => $this->assignment->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Laporan Kehadiran Kelas');
        $response->assertSee('100%');
    }
}
