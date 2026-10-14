<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SchoolProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_school_profile(): void
    {
        $response = $this->get(route('school.profile'));

        $response->assertRedirect(route('login'));
    }

    public function test_student_and_teacher_cannot_access_school_profile(): void
    {
        $student = User::factory()->create(['role' => UserRole::STUDENT]);
        $teacher = User::factory()->create(['role' => UserRole::TEACHER]);

        $this->actingAs($student)->get(route('school.profile'))->assertStatus(403);
        $this->actingAs($teacher)->get(route('school.profile'))->assertStatus(403);
    }

    public function test_school_admin_and_super_admin_can_view_school_profile(): void
    {
        $school = School::factory()->create(['name' => 'SMA Eduvia Unggulan']);
        $schoolAdmin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $response = $this->actingAs($schoolAdmin)->get(route('school.profile'));

        $response->assertStatus(200);
        $response->assertSee('SMA Eduvia Unggulan');
        $response->assertSee('Profil Sekolah');
    }

    public function test_admin_can_update_school_profile_with_valid_data(): void
    {
        $school = School::factory()->create([
            'name' => 'Nama Lama',
            'npsn' => '12345678',
        ]);
        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $response = $this->actingAs($admin)->put(route('school.profile.update'), [
            'name' => 'SMP/SMA Eduvia Merdeka',
            'short_name' => 'Eduvia Merdeka',
            'npsn' => '87654321',
            'educational_levels' => ['smp', 'sma'],
            'address' => 'Jl. Pendidikan Merdeka No. 10',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40115',
            'phone' => '022-1234567',
            'email' => 'admin@eduviamerdeka.sch.id',
            'website' => 'https://eduviamerdeka.sch.id',
        ]);

        $response->assertRedirect(route('school.profile'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('schools', [
            'id' => $school->id,
            'name' => 'SMP/SMA Eduvia Merdeka',
            'npsn' => '87654321',
            'city' => 'Bandung',
        ]);
    }

    public function test_school_profile_validation_rejects_invalid_npsn(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        // NPSN must be 8 digits
        $response = $this->actingAs($admin)->put(route('school.profile.update'), [
            'name' => 'Sekolah Test',
            'npsn' => '12345', // only 5 digits
            'educational_levels' => ['sma'],
        ]);

        $response->assertSessionHasErrors('npsn');
    }

    public function test_school_profile_validation_requires_name_and_levels(): void
    {
        School::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $response = $this->actingAs($admin)->put(route('school.profile.update'), [
            'name' => '',
            'educational_levels' => [],
        ]);

        $response->assertSessionHasErrors(['name', 'educational_levels']);
    }

    public function test_school_admin_can_upload_and_remove_logo(): void
    {
        Storage::fake('public');

        $school = School::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $file = UploadedFile::fake()->image('school_logo.png', 400, 400);

        $response = $this->actingAs($admin)->put(route('school.profile.update'), [
            'name' => $school->name,
            'educational_levels' => ['sma'],
            'logo' => $file,
        ]);

        $response->assertRedirect(route('school.profile'));

        $school->refresh();
        $this->assertNotNull($school->logo_path);
        Storage::disk('public')->assertExists($school->logo_path);

        // Test removing logo
        $deleteResponse = $this->actingAs($admin)->delete(route('school.logo.remove'));
        $deleteResponse->assertRedirect(route('school.profile'));

        $school->refresh();
        $this->assertNull($school->logo_path);
    }
}
