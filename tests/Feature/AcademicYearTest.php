<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicYearTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_unauthorized_roles_cannot_manage_academic_years(): void
    {
        $student = User::factory()->create(['role' => UserRole::STUDENT]);

        $this->get(route('academic-years.index'))->assertRedirect(route('login'));
        $this->actingAs($student)->get(route('academic-years.index'))->assertStatus(403);
    }

    public function test_admin_can_view_academic_years_list(): void
    {
        $school = School::factory()->create();
        AcademicYear::factory()->create([
            'school_id' => $school->id,
            'name' => '2025/2026',
            'is_active' => true,
        ]);

        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $response = $this->actingAs($admin)->get(route('academic-years.index'));

        $response->assertStatus(200);
        $response->assertSee('2025/2026');
        $response->assertSee('Daftar Tahun Ajaran');
    }

    public function test_admin_can_create_academic_year(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $response = $this->actingAs($admin)->post(route('academic-years.store'), [
            'school_id' => $school->id,
            'name' => '2026/2027',
            'start_date' => '2026-07-15',
            'end_date' => '2027-06-20',
            'is_active' => true,
            'description' => 'Tahun Ajaran Baru',
        ]);

        $response->assertRedirect(route('academic-years.index'));
        $this->assertDatabaseHas('academic_years', [
            'school_id' => $school->id,
            'name' => '2026/2027',
            'is_active' => true,
        ]);
    }

    public function test_end_date_must_be_after_start_date(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $response = $this->actingAs($admin)->post(route('academic-years.store'), [
            'school_id' => $school->id,
            'name' => '2026/2027',
            'start_date' => '2026-07-15',
            'end_date' => '2026-07-10', // invalid: before start_date
        ]);

        $response->assertSessionHasErrors('end_date');
    }

    public function test_academic_year_name_must_be_unique_within_school(): void
    {
        $school = School::factory()->create();
        AcademicYear::factory()->create([
            'school_id' => $school->id,
            'name' => '2025/2026',
        ]);

        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $response = $this->actingAs($admin)->post(route('academic-years.store'), [
            'school_id' => $school->id,
            'name' => '2025/2026',
            'start_date' => '2025-07-15',
            'end_date' => '2026-06-20',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_atomic_activation_deactivates_other_academic_years_in_same_school(): void
    {
        $school = School::factory()->create();
        $year1 = AcademicYear::factory()->create([
            'school_id' => $school->id,
            'name' => '2024/2025',
            'is_active' => true,
        ]);
        $year2 = AcademicYear::factory()->create([
            'school_id' => $school->id,
            'name' => '2025/2026',
            'is_active' => false,
        ]);

        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $response = $this->actingAs($admin)->post(route('academic-years.activate', $year2));

        $response->assertRedirect(route('academic-years.index'));

        $year1->refresh();
        $year2->refresh();

        $this->assertFalse($year1->is_active);
        $this->assertTrue($year2->is_active);
    }

    public function test_cannot_delete_active_academic_year(): void
    {
        $school = School::factory()->create();
        $activeYear = AcademicYear::factory()->create([
            'school_id' => $school->id,
            'name' => '2025/2026',
            'is_active' => true,
        ]);

        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $response = $this->actingAs($admin)->delete(route('academic-years.destroy', $activeYear));

        $response->assertSessionHasErrors('academic_year');
        $this->assertDatabaseHas('academic_years', ['id' => $activeYear->id]);
    }

    public function test_can_delete_inactive_academic_year(): void
    {
        $school = School::factory()->create();
        $inactiveYear = AcademicYear::factory()->create([
            'school_id' => $school->id,
            'name' => '2024/2025',
            'is_active' => false,
        ]);

        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $response = $this->actingAs($admin)->delete(route('academic-years.destroy', $inactiveYear));

        $response->assertRedirect(route('academic-years.index'));
        $this->assertDatabaseMissing('academic_years', ['id' => $inactiveYear->id]);
    }
}
