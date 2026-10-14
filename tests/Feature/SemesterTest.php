<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SemesterTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_unauthorized_roles_cannot_manage_semesters(): void
    {
        $student = User::factory()->create(['role' => UserRole::STUDENT]);

        $this->get(route('semesters.index'))->assertRedirect(route('login'));
        $this->actingAs($student)->get(route('semesters.index'))->assertStatus(403);
    }

    public function test_admin_can_view_semesters_list(): void
    {
        $school = School::factory()->create();
        $year = AcademicYear::factory()->create(['school_id' => $school->id]);
        Semester::factory()->create([
            'academic_year_id' => $year->id,
            'name' => 'Semester Ganjil',
            'is_active' => true,
        ]);

        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $response = $this->actingAs($admin)->get(route('semesters.index'));

        $response->assertStatus(200);
        $response->assertSee('Semester Ganjil');
        $response->assertSee('Daftar Semester');
    }

    public function test_admin_can_create_semester(): void
    {
        $school = School::factory()->create();
        $year = AcademicYear::factory()->create(['school_id' => $school->id]);
        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $response = $this->actingAs($admin)->post(route('semesters.store'), [
            'academic_year_id' => $year->id,
            'name' => 'Semester Ganjil',
            'semester_type' => 'ganjil',
            'start_date' => '2025-07-15',
            'end_date' => '2025-12-20',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('semesters.index', ['academic_year_id' => $year->id]));

        $this->assertDatabaseHas('semesters', [
            'academic_year_id' => $year->id,
            'semester_type' => 'ganjil',
            'is_active' => true,
        ]);
    }

    public function test_cannot_create_duplicate_semester_type_in_same_academic_year(): void
    {
        $school = School::factory()->create();
        $year = AcademicYear::factory()->create(['school_id' => $school->id]);
        Semester::factory()->create([
            'academic_year_id' => $year->id,
            'semester_type' => 'ganjil',
        ]);

        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $response = $this->actingAs($admin)->post(route('semesters.store'), [
            'academic_year_id' => $year->id,
            'name' => 'Semester Ganjil Cadangan',
            'semester_type' => 'ganjil',
            'start_date' => '2025-07-15',
            'end_date' => '2025-12-20',
        ]);

        $response->assertSessionHasErrors('semester_type');
    }

    public function test_activating_semester_activates_parent_academic_year_and_deactivates_others(): void
    {
        $school = School::factory()->create();
        $year1 = AcademicYear::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $sem1 = Semester::factory()->create([
            'academic_year_id' => $year1->id,
            'semester_type' => 'ganjil',
            'is_active' => true,
        ]);

        $year2 = AcademicYear::factory()->create([
            'school_id' => $school->id,
            'is_active' => false,
        ]);
        $sem2 = Semester::factory()->create([
            'academic_year_id' => $year2->id,
            'semester_type' => 'ganjil',
            'is_active' => false,
        ]);

        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $this->actingAs($admin)->post(route('semesters.activate', $sem2));

        $year1->refresh();
        $sem1->refresh();
        $year2->refresh();
        $sem2->refresh();

        $this->assertFalse($sem1->is_active);
        $this->assertTrue($sem2->is_active);
        $this->assertTrue($year2->is_active);
        $this->assertFalse($year1->is_active);
    }

    public function test_cannot_delete_active_semester(): void
    {
        $school = School::factory()->create();
        $year = AcademicYear::factory()->create(['school_id' => $school->id]);
        $activeSem = Semester::factory()->create([
            'academic_year_id' => $year->id,
            'is_active' => true,
        ]);

        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $response = $this->actingAs($admin)->delete(route('semesters.destroy', $activeSem));

        $response->assertSessionHasErrors('semester');
        $this->assertDatabaseHas('semesters', ['id' => $activeSem->id]);
    }
}
