<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\GradeLevel;
use App\Models\LessonSchedule;
use App\Models\School;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonScheduleTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private AcademicYear $academicYear;

    private Semester $semester;

    private GradeLevel $gradeLevel;

    private Classroom $classroomA;

    private Classroom $classroomB;

    private Subject $subjectMath;

    private Subject $subjectEnglish;

    private User $admin;

    private User $teacher1;

    private User $teacher2;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create(['name' => 'SMP Eduvia Utama', 'is_active' => true]);

        $this->academicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
            'name' => '2026/2027',
            'is_active' => true,
        ]);

        $this->semester = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Semester Ganjil',
            'semester_type' => 'ganjil',
            'is_active' => true,
        ]);

        $this->gradeLevel = GradeLevel::factory()->create([
            'educational_level' => 'smp',
            'level_number' => 7,
            'name' => 'Kelas 7',
            'is_active' => true,
        ]);

        $this->classroomA = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'name' => 'VII-A',
            'is_active' => true,
        ]);

        $this->classroomB = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'name' => 'VII-B',
            'is_active' => true,
        ]);

        $this->subjectMath = Subject::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Matematika',
            'code' => 'MAT-01',
            'is_active' => true,
        ]);

        $this->subjectEnglish = Subject::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Bahasa Inggris',
            'code' => 'ENG-01',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::SCHOOL_ADMIN,
            'is_active' => true,
        ]);

        $this->teacher1 = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::TEACHER,
            'name' => 'Budi Santoso, S.Pd',
            'is_active' => true,
        ]);

        $this->teacher2 = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::TEACHER,
            'name' => 'Siti Rahma, M.Pd',
            'is_active' => true,
        ]);

        $this->student = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::STUDENT,
            'name' => 'Rian Pratama',
            'is_active' => true,
        ]);
    }

    /**
     * 1. Authorization & Role Access
     */
    public function test_guest_and_unauthorized_roles_cannot_manage_schedules(): void
    {
        $this->get(route('schedules.index'))->assertRedirect(route('login'));
        $this->actingAs($this->student)->get(route('schedules.index'))->assertStatus(403);
        $this->actingAs($this->student)->get(route('schedules.create'))->assertStatus(403);
    }

    /**
     * 2. Admin Index, Views, and Filters
     */
    public function test_admin_can_view_schedules_index_with_views_and_filters(): void
    {
        $ta = TeachingAssignment::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroomA->id,
            'subject_id' => $this->subjectMath->id,
            'teacher_id' => $this->teacher1->id,
        ]);

        LessonSchedule::factory()->create([
            'school_id' => $this->school->id,
            'teaching_assignment_id' => $ta->id,
            'day_of_week' => 1,
            'start_time' => '07:30',
            'end_time' => '09:00',
            'room' => 'Ruang 101',
        ]);

        // Weekly View
        $response = $this->actingAs($this->admin)->get(route('schedules.index', ['view' => 'weekly']));
        $response->assertOk()
            ->assertSee('Matematika')
            ->assertSee('VII-A')
            ->assertSee('Budi Santoso');

        // Daily View
        $dailyResponse = $this->actingAs($this->admin)->get(route('schedules.index', ['view' => 'daily', 'day_of_week' => 1]));
        $dailyResponse->assertOk()
            ->assertSee('07:30 - 09:00');

        // List View with filter
        $listResponse = $this->actingAs($this->admin)->get(route('schedules.index', [
            'view' => 'list',
            'classroom_id' => $this->classroomA->id,
        ]));
        $listResponse->assertOk()
            ->assertSee('Ruang 101');
    }

    /**
     * 3. Admin Create Valid Lesson Schedule
     */
    public function test_admin_can_create_valid_lesson_schedule(): void
    {
        $payload = [
            'classroom_id' => $this->classroomA->id,
            'subject_id' => $this->subjectMath->id,
            'teacher_id' => $this->teacher1->id,
            'day_of_week' => 1,
            'start_time' => '07:30',
            'end_time' => '09:00',
            'room' => 'Ruang 101',
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('schedules.store'), $payload);

        $response->assertRedirect(route('schedules.index', [
            'classroom_id' => $this->classroomA->id,
            'academic_year_id' => $this->academicYear->id,
        ]));

        $this->assertDatabaseHas('lesson_schedules', [
            'day_of_week' => 1,
            'start_time' => '07:30:00',
            'end_time' => '09:00:00',
            'room' => 'Ruang 101',
            'is_active' => true,
        ]);
    }

    /**
     * 4. Consecutive non-overlapping lessons can be stored without conflict
     */
    public function test_two_consecutive_non_overlapping_lessons_can_be_stored(): void
    {
        $ta1 = TeachingAssignment::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroomA->id,
            'subject_id' => $this->subjectMath->id,
            'teacher_id' => $this->teacher1->id,
        ]);

        // Slot 1: 07:30 to 09:00
        LessonSchedule::factory()->create([
            'school_id' => $this->school->id,
            'teaching_assignment_id' => $ta1->id,
            'day_of_week' => 1,
            'start_time' => '07:30',
            'end_time' => '09:00',
            'room' => 'Ruang 101',
        ]);

        // Slot 2: 09:00 to 10:30 (starts exactly when slot 1 ends)
        $payload = [
            'classroom_id' => $this->classroomA->id,
            'subject_id' => $this->subjectEnglish->id,
            'teacher_id' => $this->teacher2->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '10:30',
            'room' => 'Ruang 101',
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('schedules.store'), $payload);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseCount('lesson_schedules', 2);
    }

    /**
     * 5. Start time and end time constraints
     */
    public function test_start_time_must_be_strictly_before_end_time(): void
    {
        // Equal times
        $responseEqual = $this->actingAs($this->admin)->post(route('schedules.store'), [
            'classroom_id' => $this->classroomA->id,
            'subject_id' => $this->subjectMath->id,
            'teacher_id' => $this->teacher1->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '08:00',
        ]);
        $responseEqual->assertSessionHasErrors(['end_time']);

        // Start after end
        $responseReversed = $this->actingAs($this->admin)->post(route('schedules.store'), [
            'classroom_id' => $this->classroomA->id,
            'subject_id' => $this->subjectMath->id,
            'teacher_id' => $this->teacher1->id,
            'day_of_week' => 1,
            'start_time' => '09:30',
            'end_time' => '08:00',
        ]);
        $responseReversed->assertSessionHasErrors(['end_time']);
    }

    /**
     * 6. Teacher Conflict Detection
     */
    public function test_teacher_conflict_detection(): void
    {
        $ta = TeachingAssignment::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroomA->id,
            'subject_id' => $this->subjectMath->id,
            'teacher_id' => $this->teacher1->id,
        ]);

        LessonSchedule::factory()->create([
            'school_id' => $this->school->id,
            'teaching_assignment_id' => $ta->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '09:30',
            'room' => 'Ruang 101',
        ]);

        // Attempt to assign Teacher 1 to Classroom B on Monday 08:30 - 10:00 (overlaps 08:00 - 09:30)
        $response = $this->actingAs($this->admin)->post(route('schedules.store'), [
            'classroom_id' => $this->classroomB->id,
            'subject_id' => $this->subjectMath->id,
            'teacher_id' => $this->teacher1->id,
            'day_of_week' => 1,
            'start_time' => '08:30',
            'end_time' => '10:00',
            'room' => 'Ruang 102',
        ]);

        $response->assertSessionHasErrors(['teacher_id']);
        $errors = session('errors')->get('teacher_id');
        $this->assertStringContainsString('Bentrok Guru', $errors[0]);
    }

    /**
     * 7. Classroom Conflict Detection
     */
    public function test_classroom_conflict_detection(): void
    {
        $ta = TeachingAssignment::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroomA->id,
            'subject_id' => $this->subjectMath->id,
            'teacher_id' => $this->teacher1->id,
        ]);

        LessonSchedule::factory()->create([
            'school_id' => $this->school->id,
            'teaching_assignment_id' => $ta->id,
            'day_of_week' => 2,
            'start_time' => '08:00',
            'end_time' => '09:30',
        ]);

        // Attempt to assign Classroom A with Teacher 2 on Tuesday 09:00 - 10:30 (overlaps 08:00 - 09:30)
        $response = $this->actingAs($this->admin)->post(route('schedules.store'), [
            'classroom_id' => $this->classroomA->id,
            'subject_id' => $this->subjectEnglish->id,
            'teacher_id' => $this->teacher2->id,
            'day_of_week' => 2,
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);

        $response->assertSessionHasErrors(['classroom_id']);
        $errors = session('errors')->get('classroom_id');
        $this->assertStringContainsString('Bentrok Kelas', $errors[0]);
    }

    /**
     * 8. Room Conflict Detection
     */
    public function test_room_conflict_detection(): void
    {
        $ta = TeachingAssignment::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroomA->id,
            'subject_id' => $this->subjectMath->id,
            'teacher_id' => $this->teacher1->id,
        ]);

        LessonSchedule::factory()->create([
            'school_id' => $this->school->id,
            'teaching_assignment_id' => $ta->id,
            'day_of_week' => 3,
            'start_time' => '08:00',
            'end_time' => '09:30',
            'room' => 'Lab Komputer 1',
        ]);

        // Attempt to assign Classroom B with Teacher 2 in same room at overlapping time
        $response = $this->actingAs($this->admin)->post(route('schedules.store'), [
            'classroom_id' => $this->classroomB->id,
            'subject_id' => $this->subjectEnglish->id,
            'teacher_id' => $this->teacher2->id,
            'day_of_week' => 3,
            'start_time' => '08:45',
            'end_time' => '10:15',
            'room' => 'Lab Komputer 1',
        ]);

        $response->assertSessionHasErrors(['room']);
        $errors = session('errors')->get('room');
        $this->assertStringContainsString('Bentrok Ruang', $errors[0]);
    }

    /**
     * 9. Self-Exclusion on Update
     */
    public function test_admin_can_update_schedule_without_self_conflict(): void
    {
        $ta = TeachingAssignment::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroomA->id,
            'subject_id' => $this->subjectMath->id,
            'teacher_id' => $this->teacher1->id,
        ]);

        $schedule = LessonSchedule::factory()->create([
            'school_id' => $this->school->id,
            'teaching_assignment_id' => $ta->id,
            'day_of_week' => 1,
            'start_time' => '07:30',
            'end_time' => '09:00',
            'room' => 'Ruang 101',
        ]);

        // Updating the same schedule maintaining the same time slot & teacher should succeed
        $response = $this->actingAs($this->admin)->put(route('schedules.update', $schedule), [
            'classroom_id' => $this->classroomA->id,
            'subject_id' => $this->subjectMath->id,
            'teacher_id' => $this->teacher1->id,
            'day_of_week' => 1,
            'start_time' => '07:30',
            'end_time' => '09:00',
            'room' => 'Ruang 102 - Diperbarui',
            'is_active' => 1,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('lesson_schedules', [
            'id' => $schedule->id,
            'room' => 'Ruang 102 - Diperbarui',
        ]);
    }

    /**
     * 10. Tenant Isolation (Cross School Security)
     */
    public function test_tenant_isolation_cross_school_access_is_forbidden(): void
    {
        $school2 = School::factory()->create(['name' => 'SMA Luar Kota']);
        $admin2 = User::factory()->create([
            'school_id' => $school2->id,
            'role' => UserRole::SCHOOL_ADMIN,
        ]);

        $classSchool2 = Classroom::factory()->create(['school_id' => $school2->id]);
        $teacherSchool2 = User::factory()->create([
            'school_id' => $school2->id,
            'role' => UserRole::TEACHER,
        ]);

        // Admin 1 cannot submit School 2's classroom or teacher
        $response = $this->actingAs($this->admin)->post(route('schedules.store'), [
            'classroom_id' => $classSchool2->id,
            'subject_id' => $this->subjectMath->id,
            'teacher_id' => $this->teacher1->id,
            'day_of_week' => 1,
            'start_time' => '07:30',
            'end_time' => '09:00',
        ]);
        $response->assertSessionHasErrors(['classroom_id']);

        // Admin 2 cannot edit or delete School 1's schedule
        $ta = TeachingAssignment::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroomA->id,
            'subject_id' => $this->subjectMath->id,
            'teacher_id' => $this->teacher1->id,
        ]);

        $scheduleSchool1 = LessonSchedule::factory()->create([
            'school_id' => $this->school->id,
            'teaching_assignment_id' => $ta->id,
        ]);

        $this->actingAs($admin2)->get(route('schedules.edit', $scheduleSchool1))->assertStatus(403);
        $this->actingAs($admin2)->delete(route('schedules.destroy', $scheduleSchool1))->assertStatus(403);
    }

    /**
     * 11. Schedule Status Toggle and Soft-Deactivate
     */
    public function test_schedule_status_toggle_and_safe_destroy(): void
    {
        $ta = TeachingAssignment::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroomA->id,
            'subject_id' => $this->subjectMath->id,
            'teacher_id' => $this->teacher1->id,
        ]);

        $schedule = LessonSchedule::factory()->create([
            'school_id' => $this->school->id,
            'teaching_assignment_id' => $ta->id,
            'is_active' => true,
        ]);

        // Toggle status
        $this->actingAs($this->admin)->post(route('schedules.toggle-status', $schedule));
        $this->assertFalse($schedule->fresh()->is_active);

        // Link attendance session
        AttendanceSession::create([
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroomA->id,
            'teaching_assignment_id' => $ta->id,
            'academic_year_id' => $this->academicYear->id,
            'teacher_id' => $this->teacher1->id,
            'date' => '2026-08-10',
            'meeting_number' => 1,
            'status' => 'draft',
        ]);

        // Activating back
        $schedule->update(['is_active' => true]);

        // Destroying with linked attendance soft deactivates
        $this->actingAs($this->admin)->delete(route('schedules.destroy', $schedule));
        $this->assertDatabaseHas('lesson_schedules', ['id' => $schedule->id, 'is_active' => false]);
    }

    /**
     * 12. Teacher Can Only View Their Assigned Classes and Homeroom
     */
    public function test_teacher_can_only_view_their_own_schedule_and_homeroom(): void
    {
        $ta1 = TeachingAssignment::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroomA->id,
            'subject_id' => $this->subjectMath->id,
            'teacher_id' => $this->teacher1->id,
        ]);

        LessonSchedule::factory()->create([
            'school_id' => $this->school->id,
            'teaching_assignment_id' => $ta1->id,
            'day_of_week' => 1,
            'start_time' => '07:30',
            'end_time' => '09:00',
        ]);

        $ta2 = TeachingAssignment::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroomB->id,
            'subject_id' => $this->subjectEnglish->id,
            'teacher_id' => $this->teacher2->id,
        ]);

        LessonSchedule::factory()->create([
            'school_id' => $this->school->id,
            'teaching_assignment_id' => $ta2->id,
            'day_of_week' => 1,
            'start_time' => '09:15',
            'end_time' => '10:45',
        ]);

        // Teacher 1 visits /teacher/schedule
        $response = $this->actingAs($this->teacher1)->get(route('teacher.schedule.index'));
        $response->assertOk()
            ->assertSee('Matematika')
            ->assertDontSee('ENG-01');

        // If Teacher 1 is homeroom for Classroom B
        $this->classroomB->update(['homeroom_teacher_id' => $this->teacher1->id]);

        $homeroomResponse = $this->actingAs($this->teacher1)->get(route('teacher.schedule.index', ['view' => 'homeroom']));
        $homeroomResponse->assertOk()
            ->assertSee('Kelas Binaan (VII-B)')
            ->assertSee('Bahasa Inggris');
    }

    /**
     * 13. Student Can Only View Their Enrolled Classroom Schedule
     */
    public function test_student_can_only_view_their_enrolled_class_schedule(): void
    {
        // Enroll student in Classroom A
        ClassroomStudent::factory()->create([
            'classroom_id' => $this->classroomA->id,
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'enrollment_status' => 'active',
        ]);

        $ta1 = TeachingAssignment::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroomA->id,
            'subject_id' => $this->subjectMath->id,
            'teacher_id' => $this->teacher1->id,
        ]);

        LessonSchedule::factory()->create([
            'school_id' => $this->school->id,
            'teaching_assignment_id' => $ta1->id,
            'day_of_week' => 1,
            'start_time' => '07:30',
            'end_time' => '09:00',
            'is_active' => true,
        ]);

        // Inactive schedule in Classroom A (should be hidden)
        LessonSchedule::factory()->create([
            'school_id' => $this->school->id,
            'teaching_assignment_id' => $ta1->id,
            'day_of_week' => 2,
            'start_time' => '07:30',
            'end_time' => '09:00',
            'is_active' => false,
            'room' => 'Ruang Rahasia Nonaktif',
        ]);

        // Schedule in Classroom B (should NOT be visible to student A)
        $ta2 = TeachingAssignment::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroomB->id,
            'subject_id' => $this->subjectEnglish->id,
            'teacher_id' => $this->teacher2->id,
        ]);

        LessonSchedule::factory()->create([
            'school_id' => $this->school->id,
            'teaching_assignment_id' => $ta2->id,
            'day_of_week' => 1,
            'start_time' => '10:00',
            'end_time' => '11:30',
            'room' => 'Ruang Kelas VII-B Khusus',
        ]);

        $response = $this->actingAs($this->student)->get(route('student.schedule.index'));
        $response->assertOk()
            ->assertSee('VII-A')
            ->assertSee('Matematika')
            ->assertDontSee('Ruang Rahasia Nonaktif')
            ->assertDontSee('Ruang Kelas VII-B Khusus');
    }

    /**
     * 14. Unenrolled student gets informative empty state
     */
    public function test_unenrolled_student_gets_informative_empty_state(): void
    {
        $unenrolledStudent = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::STUDENT,
        ]);

        $response = $this->actingAs($unenrolledStudent)->get(route('student.schedule.index'));
        $response->assertOk()
            ->assertSee('Belum Terdaftar di Rombongan Belajar');
    }
}
