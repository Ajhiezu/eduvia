<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassroomTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private AcademicYear $academicYear;

    private GradeLevel $gradeLevel;

    private User $admin;

    private User $teacher;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

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
        $this->admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);
        $this->teacher = User::factory()->create(['role' => UserRole::TEACHER]);
        $this->student = User::factory()->create(['role' => UserRole::STUDENT]);
    }

    public function test_guest_and_unauthorized_roles_cannot_access_classrooms(): void
    {
        $this->get(route('classrooms.index'))->assertRedirect(route('login'));
        $this->actingAs($this->student)->get(route('classrooms.index'))->assertStatus(403);
        $this->actingAs($this->teacher)->get(route('classrooms.index'))->assertStatus(403);
    }

    public function test_admin_can_view_classrooms_list(): void
    {
        $classroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'name' => 'VII-A',
        ]);

        $response = $this->actingAs($this->admin)->get(route('classrooms.index'));

        $response->assertStatus(200);
        $response->assertSee('VII-A');
        $response->assertSee('Rombongan Belajar');
    }

    public function test_admin_can_create_classroom(): void
    {
        $response = $this->actingAs($this->admin)->post(route('classrooms.store'), [
            'name' => 'VII-B',
            'code' => 'CLS-7B',
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'homeroom_teacher_id' => $this->teacher->id,
            'capacity' => 32,
            'notes' => 'Rombel Bilingual',
            'is_active' => '1',
        ]);

        $classroom = Classroom::where('name', 'VII-B')->first();
        $this->assertNotNull($classroom);
        $response->assertRedirect(route('classrooms.show', $classroom));

        $this->assertDatabaseHas('classrooms', [
            'name' => 'VII-B',
            'code' => 'CLS-7B',
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'homeroom_teacher_id' => $this->teacher->id,
            'capacity' => 32,
            'is_active' => true,
        ]);
    }

    public function test_cannot_create_duplicate_classroom_name_in_same_academic_year(): void
    {
        Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'name' => 'VII-A',
        ]);

        $response = $this->actingAs($this->admin)->post(route('classrooms.store'), [
            'name' => 'VII-A',
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_admin_can_view_classroom_detail(): void
    {
        $classroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'homeroom_teacher_id' => $this->teacher->id,
            'name' => 'VII-A',
        ]);

        $response = $this->actingAs($this->admin)->get(route('classrooms.show', $classroom));

        $response->assertStatus(200);
        $response->assertSee('VII-A');
        $response->assertSee($this->teacher->name);
        $response->assertSee('Daftar Peserta Didik Aktif');
    }

    public function test_admin_can_update_classroom(): void
    {
        $classroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'name' => 'VII-A',
            'capacity' => 30,
        ]);

        $newTeacher = User::factory()->create(['role' => UserRole::TEACHER]);

        $response = $this->actingAs($this->admin)->put(route('classrooms.update', $classroom), [
            'name' => 'VII-A Unggulan',
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'homeroom_teacher_id' => $newTeacher->id,
            'capacity' => 36,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('classrooms.show', $classroom));

        $this->assertDatabaseHas('classrooms', [
            'id' => $classroom->id,
            'name' => 'VII-A Unggulan',
            'homeroom_teacher_id' => $newTeacher->id,
            'capacity' => 36,
        ]);
    }

    public function test_admin_cannot_delete_classroom_with_active_students(): void
    {
        $classroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'name' => 'VII-A',
        ]);

        $classroom->addStudent($this->student);

        $response = $this->actingAs($this->admin)->delete(route('classrooms.destroy', $classroom));

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('classrooms', ['id' => $classroom->id]);
    }

    public function test_admin_can_delete_empty_classroom(): void
    {
        $classroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'name' => 'VII-A',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('classrooms.destroy', $classroom));

        $response->assertRedirect(route('classrooms.index'));
        $this->assertDatabaseMissing('classrooms', ['id' => $classroom->id]);
    }

    public function test_admin_can_place_student_into_classroom(): void
    {
        $classroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('classrooms.students.store', $classroom), [
            'student_id' => $this->student->id,
            'joined_at' => '2026-07-20',
            'notes' => 'Penempatan awal',
        ]);

        $response->assertRedirect(route('classrooms.show', $classroom));

        $this->assertDatabaseHas('classroom_students', [
            'classroom_id' => $classroom->id,
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'enrollment_status' => 'active',
            'notes' => 'Penempatan awal',
        ]);

        $this->assertEquals(1, $classroom->fresh()->activeStudents()->count());
    }

    public function test_cannot_place_student_who_already_has_active_enrollment_in_same_year(): void
    {
        $classA = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'name' => 'VII-A',
        ]);

        $classB = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'name' => 'VII-B',
        ]);

        $classA->addStudent($this->student);

        // Attempt placing into Class B without transferring
        $response = $this->actingAs($this->admin)->post(route('classrooms.students.store', $classB), [
            'student_id' => $this->student->id,
            'joined_at' => '2026-07-20',
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertEquals(0, $classB->activeStudents()->count());
    }

    public function test_admin_can_transfer_student_to_another_classroom(): void
    {
        $classA = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'name' => 'VII-A',
        ]);

        $classB = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'name' => 'VII-B',
        ]);

        $classA->addStudent($this->student, '2026-07-20');

        $response = $this->actingAs($this->admin)->post(route('classrooms.students.transfer', [$classA, $this->student]), [
            'target_classroom_id' => $classB->id,
            'effective_date' => '2026-08-01',
            'notes' => 'Pindah ke kelas bilingual',
        ]);

        $response->assertRedirect(route('classrooms.show', $classA));

        // Class A record should now be marked transferred
        $this->assertDatabaseHas('classroom_students', [
            'classroom_id' => $classA->id,
            'student_id' => $this->student->id,
            'enrollment_status' => 'transferred',
        ]);

        // Class B record should now be active
        $this->assertDatabaseHas('classroom_students', [
            'classroom_id' => $classB->id,
            'student_id' => $this->student->id,
            'enrollment_status' => 'active',
            'notes' => 'Pindahan dari rombel VII-A. Pindah ke kelas bilingual',
        ]);

        $this->assertEquals(0, $classA->fresh()->activeStudents()->count());
        $this->assertEquals(1, $classB->fresh()->activeStudents()->count());
    }

    public function test_admin_can_remove_student_from_classroom(): void
    {
        $classroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
        ]);

        $classroom->addStudent($this->student);

        $response = $this->actingAs($this->admin)->delete(route('classrooms.students.destroy', [$classroom, $this->student]), [
            'status' => 'dropped',
            'notes' => 'Pindah ke luar kota',
        ]);

        $response->assertRedirect(route('classrooms.show', $classroom));

        $this->assertDatabaseHas('classroom_students', [
            'classroom_id' => $classroom->id,
            'student_id' => $this->student->id,
            'enrollment_status' => 'dropped',
        ]);

        $record = ClassroomStudent::where('classroom_id', $classroom->id)
            ->where('student_id', $this->student->id)
            ->first();
        $this->assertStringContainsString('Pindah ke luar kota', $record->notes);

        $this->assertEquals(0, $classroom->fresh()->activeStudents()->count());
    }
}
