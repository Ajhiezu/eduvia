<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\ParentLinkCode;
use App\Models\ParentStudentRelation;
use App\Models\ParentTeacherConversation;
use App\Models\School;
use App\Models\SchoolAnnouncement;
use App\Models\Semester;
use App\Models\StudentProfile;
use App\Models\StudentReportCard;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Notifications\NewSchoolAnnouncementNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentPortalTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected School $otherSchool;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected Classroom $classroom;

    protected Subject $subject;

    protected User $teacher;

    protected User $otherTeacher;

    protected User $homeroomTeacher;

    protected User $student;

    protected User $studentTwo;

    protected User $otherSchoolStudent;

    protected User $parent;

    protected User $otherParent;

    protected User $schoolAdmin;

    protected TeachingAssignment $teachingAssignment;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Schools
        $this->school = School::factory()->create([
            'name' => 'SMA Eduvia Utama',
            'city' => 'Surabaya',
            'npsn' => '20198765',
        ]);

        $this->otherSchool = School::factory()->create([
            'name' => 'SMA Eduvia Jakarta',
            'city' => 'Jakarta',
            'npsn' => '20199999',
        ]);

        // 2. Academic Period
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

        // 3. Admin & Teachers
        $this->schoolAdmin = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::SCHOOL_ADMIN,
        ]);

        $this->teacher = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::TEACHER,
            'name' => 'Guru Matematika',
        ]);

        $this->homeroomTeacher = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::TEACHER,
            'name' => 'Wali Kelas X-1',
        ]);

        $this->otherTeacher = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::TEACHER,
            'name' => 'Guru Lainnya',
        ]);

        // 4. Students
        $this->student = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::STUDENT,
            'name' => 'Anak Pertama',
        ]);

        StudentProfile::create([
            'user_id' => $this->student->id,
            'nis' => '10001',
            'nisn' => '0010000001',
            'gender' => 'L',
        ]);

        $this->studentTwo = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::STUDENT,
            'name' => 'Anak Kedua',
        ]);

        StudentProfile::create([
            'user_id' => $this->studentTwo->id,
            'nis' => '10002',
            'nisn' => '0010000002',
            'gender' => 'P',
        ]);

        $this->otherSchoolStudent = User::factory()->create([
            'school_id' => $this->otherSchool->id,
            'role' => UserRole::STUDENT,
            'name' => 'Siswa Sekolah Lain',
        ]);

        // 5. Classroom & Enrollment
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

        ClassroomStudent::create([
            'classroom_id' => $this->classroom->id,
            'student_id' => $this->studentTwo->id,
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
        ]);

        // 6. Parents
        $this->parent = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::PARENT,
            'name' => 'Orang Tua Utama',
            'email' => 'orangtua@eduvia.test',
        ]);

        $this->otherParent = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::PARENT,
            'name' => 'Orang Tua Lain',
            'email' => 'orangtualain@eduvia.test',
        ]);
    }

    /**
     * Test parent dashboard renders with verified child and active child switcher.
     */
    public function test_parent_dashboard_displays_verified_child_information(): void
    {
        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship_type' => 'father',
            'status' => 'active',
            'verified_at' => now(),
        ]);

        $response = $this->actingAs($this->parent)->get(route('parent.dashboard'));

        $response->assertOk();
        $response->assertSee('Anak Pertama');
        $response->assertSee('X-MIPA-1');
        $response->assertSee('Wali Murid Resmi');
    }

    /**
     * Test parent with multiple verified children can switch between children.
     */
    public function test_parent_can_switch_active_child_safely(): void
    {
        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship_type' => 'father',
            'status' => 'active',
            'verified_at' => now(),
        ]);

        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->studentTwo->id,
            'relationship_type' => 'father',
            'status' => 'active',
            'verified_at' => now(),
        ]);

        $response = $this->actingAs($this->parent)
            ->get(route('parent.switch-child', ['child' => $this->studentTwo->id]));

        $response->assertRedirect(route('parent.dashboard'));
        $this->assertEquals($this->studentTwo->id, session('parent_active_child_id'));

        $dashboardResponse = $this->actingAs($this->parent)->get(route('parent.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Anak Kedua');
    }

    /**
     * Test parent cannot switch to or view a child belonging to another parent (IDOR prevention).
     */
    public function test_parent_cannot_switch_to_unlinked_child(): void
    {
        // Unlinked student belongs to another parent
        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->otherParent->id,
            'student_id' => $this->studentTwo->id,
            'relationship_type' => 'mother',
            'status' => 'active',
            'verified_at' => now(),
        ]);

        // Only student 1 is linked to $this->parent
        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship_type' => 'father',
            'status' => 'active',
            'verified_at' => now(),
        ]);

        $response = $this->actingAs($this->parent)
            ->get(route('parent.switch-child', ['child' => $this->studentTwo->id]));

        $response->assertRedirect(route('parent.dashboard'));
        // Session should NOT have switched to studentTwo because they are not linked
        $this->assertNotEquals($this->studentTwo->id, session('parent_active_child_id'));
    }

    /**
     * Test parent with pending/rejected relation cannot access child academic data.
     */
    public function test_pending_relation_does_not_grant_academic_access(): void
    {
        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship_type' => 'father',
            'status' => 'pending',
            'verified_at' => null,
        ]);

        $response = $this->actingAs($this->parent)->get(route('parent.dashboard'));

        $response->assertOk();
        $response->assertSee('Belum Ada Akun Anak yang Terhubung');
        $response->assertDontSee('X-MIPA-1');
    }

    /**
     * Test parent can claim an active link code created by school admin.
     */
    public function test_parent_can_claim_linking_code_for_their_child(): void
    {
        $code = ParentLinkCode::create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'code' => 'LINK-1234-ABCD',
            'created_by' => $this->schoolAdmin->id,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($this->parent)->post(route('parent.children.claim-code'), [
            'code' => 'link-1234-abcd', // test case-insensitivity
            'relationship_type' => 'father',
        ]);

        $response->assertRedirect(route('parent.children.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('parent_student_relations', [
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship_type' => 'father',
            'status' => 'active',
        ]);

        $this->assertNotNull($code->fresh()->used_at);
        $this->assertEquals($this->parent->id, $code->fresh()->used_by);
    }

    /**
     * Test expired or invalid link code is rejected.
     */
    public function test_expired_link_code_cannot_be_claimed(): void
    {
        ParentLinkCode::create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'code' => 'EXPIRED-CODE-01',
            'created_by' => $this->schoolAdmin->id,
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->parent)->post(route('parent.children.claim-code'), [
            'code' => 'EXPIRED-CODE-01',
            'relationship_type' => 'father',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('code');

        $this->assertDatabaseMissing('parent_student_relations', [
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
        ]);
    }

    /**
     * Test parent attendance view displays accurate records.
     */
    public function test_parent_can_view_child_attendance(): void
    {
        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship_type' => 'father',
            'status' => 'active',
            'verified_at' => now(),
        ]);

        $session = AttendanceSession::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'classroom_id' => $this->classroom->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Pertemuan 1',
            'date' => now()->toDateString(),
            'status' => 'closed',
        ]);

        AttendanceRecord::create([
            'attendance_session_id' => $session->id,
            'student_id' => $this->student->id,
            'status' => 'present',
            'notes' => 'Tepat waktu',
        ]);

        $response = $this->actingAs($this->parent)->get(route('parent.attendance'));

        $response->assertOk();
        $response->assertSee('Hadir');
        $response->assertSee('Tepat waktu');
        $response->assertSee('Guru Matematika');
    }

    /**
     * Test parent grades view only displays published report cards.
     */
    public function test_parent_reports_only_shows_published_report_cards(): void
    {
        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship_type' => 'father',
            'status' => 'active',
            'verified_at' => now(),
        ]);

        $secondSemester = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Semester Genap',
            'semester_type' => 'genap',
            'is_active' => false,
        ]);

        // Published report card
        $publishedReport = StudentReportCard::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'classroom_id' => $this->classroom->id,
            'student_id' => $this->student->id,
            'status' => 'published',
        ]);

        // Draft report card for future semester
        $draftReport = StudentReportCard::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $secondSemester->id,
            'classroom_id' => $this->classroom->id,
            'student_id' => $this->student->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->parent)->get(route('parent.reports'));

        $response->assertOk();
        $response->assertSee('Diterbitkan');
        $response->assertSee(route('parent.reports.show', $publishedReport));
        $response->assertDontSee(route('parent.reports.show', $draftReport));
    }

    /**
     * Test parent cannot inspect draft report card directly (IDOR / unauthorized state check).
     */
    public function test_parent_cannot_view_draft_report_card_detail(): void
    {
        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship_type' => 'father',
            'status' => 'active',
            'verified_at' => now(),
        ]);

        $draftReport = StudentReportCard::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'classroom_id' => $this->classroom->id,
            'student_id' => $this->student->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->parent)->get(route('parent.reports.show', $draftReport));

        $response->assertNotFound();
    }

    /**
     * Test parent cannot view report card of a student not linked to them.
     */
    public function test_parent_cannot_view_other_student_report_card(): void
    {
        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship_type' => 'father',
            'status' => 'active',
            'verified_at' => now(),
        ]);

        $otherStudentReport = StudentReportCard::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'classroom_id' => $this->classroom->id,
            'student_id' => $this->studentTwo->id,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->parent)->get(route('parent.reports.show', $otherStudentReport));

        $response->assertForbidden();
    }

    /**
     * Test school announcements audience scoping.
     */
    public function test_announcements_respect_target_audience(): void
    {
        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship_type' => 'father',
            'status' => 'active',
            'verified_at' => now(),
        ]);

        // 1. Announcement for all
        $allAnnouncement = SchoolAnnouncement::factory()->create([
            'school_id' => $this->school->id,
            'created_by' => $this->schoolAdmin->id,
            'title' => 'Pengumuman Libur Nasional',
            'target_audience' => 'all',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        // 2. Announcement for parents only
        $parentAnnouncement = SchoolAnnouncement::factory()->create([
            'school_id' => $this->school->id,
            'created_by' => $this->schoolAdmin->id,
            'title' => 'Undangan Rapat Komite Orang Tua',
            'target_audience' => 'parents',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        // 3. Announcement for teachers only
        $teacherAnnouncement = SchoolAnnouncement::factory()->create([
            'school_id' => $this->school->id,
            'created_by' => $this->schoolAdmin->id,
            'title' => 'Rapat Rencana Kerja Guru Internal',
            'target_audience' => 'teachers',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->parent)->get(route('announcements.index'));

        $response->assertOk();
        $response->assertSee('Pengumuman Libur Nasional');
        $response->assertSee('Undangan Rapat Komite Orang Tua');
        $response->assertDontSee('Rapat Rencana Kerja Guru Internal');
    }

    /**
     * Test authorized roles can view create announcement form without schema errors.
     */
    public function test_teacher_and_admin_can_access_announcement_create_page(): void
    {
        $response = $this->actingAs($this->schoolAdmin)->get(route('announcements.create'));

        $response->assertOk();
        $response->assertSee('Buat Pengumuman Baru');
        $response->assertSee('Target Jenjang');

        $teacherResponse = $this->actingAs($this->teacher)->get(route('announcements.create'));
        $teacherResponse->assertOk();
    }

    /**
     * Test parent can start consultation with teacher teaching their child.
     */
    public function test_parent_can_start_conversation_with_child_teacher(): void
    {
        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship_type' => 'father',
            'status' => 'active',
            'verified_at' => now(),
        ]);

        $response = $this->actingAs($this->parent)->post(route('conversations.store'), [
            'student_id' => $this->student->id,
            'recipient_id' => $this->teacher->id,
            'subject' => 'Konsultasi Belajar Matematika',
            'body' => 'Selamat siang Pak Guru, bagaimana perkembangan belajar Ahmad Fauzi?',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('parent_teacher_conversations', [
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'teacher_id' => $this->teacher->id,
            'student_id' => $this->student->id,
            'subject' => 'Konsultasi Belajar Matematika',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('conversation_messages', [
            'sender_id' => $this->parent->id,
            'body' => 'Selamat siang Pak Guru, bagaimana perkembangan belajar Ahmad Fauzi?',
        ]);
    }

    /**
     * Test parent cannot start consultation with teacher who does NOT teach their child.
     */
    public function test_parent_cannot_contact_unauthorized_teacher(): void
    {
        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship_type' => 'father',
            'status' => 'active',
            'verified_at' => now(),
        ]);

        // $this->otherTeacher is not homeroom teacher and has no teaching assignment for $this->classroom
        $response = $this->actingAs($this->parent)->post(route('conversations.store'), [
            'student_id' => $this->student->id,
            'recipient_id' => $this->otherTeacher->id,
            'subject' => 'Konsultasi Ilegal',
            'body' => 'Halo guru yang tidak mengajar anak saya',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('parent_teacher_conversations', [
            'subject' => 'Konsultasi Ilegal',
        ]);
    }

    /**
     * Test conversation participants can reply, but unauthorized 3rd party is forbidden (IDOR).
     */
    public function test_unauthorized_user_cannot_access_or_reply_to_conversation(): void
    {
        $conversation = ParentTeacherConversation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'teacher_id' => $this->teacher->id,
            'student_id' => $this->student->id,
            'subject' => 'Konsultasi Rahasia',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        // Other parent tries to view
        $response = $this->actingAs($this->otherParent)->get(route('conversations.show', $conversation));
        $response->assertForbidden();

        // Other parent tries to reply
        $replyResponse = $this->actingAs($this->otherParent)
            ->post(route('conversations.reply', $conversation), [
                'body' => 'Mencoba membalas percakapan orang lain',
            ]);
        $replyResponse->assertForbidden();

        // Legitimate teacher can reply
        $teacherReply = $this->actingAs($this->teacher)
            ->post(route('conversations.reply', $conversation), [
                'body' => 'Terima kasih Bapak, Ahmad berkembang sangat baik.',
            ]);
        $teacherReply->assertRedirect(route('conversations.show', $conversation));

        $this->assertDatabaseHas('conversation_messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $this->teacher->id,
            'body' => 'Terima kasih Bapak, Ahmad berkembang sangat baik.',
        ]);
    }

    /**
     * Test notification center and marking notifications as read.
     */
    public function test_notification_flow_mark_read_and_mark_all_read(): void
    {
        // Create an unread notification for parent
        $announcement = SchoolAnnouncement::factory()->create([
            'school_id' => $this->school->id,
            'created_by' => $this->schoolAdmin->id,
            'title' => 'Pengumuman Penting',
        ]);

        $this->parent->notify(new NewSchoolAnnouncementNotification($announcement));

        $this->assertEquals(1, $this->parent->unreadNotifications()->count());
        $notification = $this->parent->unreadNotifications()->first();

        // Visit notification center
        $indexResponse = $this->actingAs($this->parent)->get(route('notifications.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Pengumuman Penting');

        // Click on notification to read and redirect
        $readResponse = $this->actingAs($this->parent)->get(route('notifications.read', $notification->id));
        $readResponse->assertRedirect(route('announcements.show', $announcement));

        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertEquals(0, $this->parent->fresh()->unreadNotifications()->count());

        // Test Mark All As Read
        $this->parent->notify(new NewSchoolAnnouncementNotification($announcement));
        $this->assertEquals(1, $this->parent->fresh()->unreadNotifications()->count());

        $markAllResponse = $this->actingAs($this->parent)->post(route('notifications.mark-all-read'));
        $markAllResponse->assertRedirect();
        $this->assertEquals(0, $this->parent->fresh()->unreadNotifications()->count());
    }

    /**
     * Test admin can view parent-student relations and generate link code.
     */
    public function test_admin_can_generate_link_code_for_student(): void
    {
        $response = $this->actingAs($this->schoolAdmin)
            ->from(route('admin.parents.index'))
            ->post(route('admin.parents.generate-code'), [
                'student_id' => $this->student->id,
                'relationship_type' => 'father',
                'valid_days' => 7,
            ]);

        $response->assertRedirect(route('admin.parents.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('parent_link_codes', [
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'created_by' => $this->schoolAdmin->id,
        ]);
    }
}
