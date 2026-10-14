<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\School;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_system_settings(): void
    {
        $response = $this->get(route('settings.system'));

        $response->assertRedirect(route('login'));
    }

    public function test_student_and_teacher_cannot_access_system_settings(): void
    {
        $student = User::factory()->create(['role' => UserRole::STUDENT]);
        $teacher = User::factory()->create(['role' => UserRole::TEACHER]);

        $this->actingAs($student)->get(route('settings.system'))->assertStatus(403);
        $this->actingAs($teacher)->get(route('settings.system'))->assertStatus(403);
    }

    public function test_school_admin_and_super_admin_can_view_system_settings(): void
    {
        School::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $response = $this->actingAs($admin)->get(route('settings.system'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Dasar Sistem');
        $response->assertSee('Zona Waktu Operasional');
        $response->assertSee('Bahasa Baku Antarmuka');
    }

    public function test_admin_can_update_system_settings_with_valid_data(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $response = $this->actingAs($admin)->put(route('settings.system.update'), [
            'timezone' => 'Asia/Makassar',
            'locale' => 'en',
            'date_format' => 'Y-m-d',
        ]);

        $response->assertRedirect(route('settings.system'));
        $response->assertSessionHas('status', 'Pengaturan dasar sistem berhasil diperbarui.');

        $this->assertSame('Asia/Makassar', SystemSetting::get('timezone', null, $school->id));
        $this->assertSame('en', SystemSetting::get('locale', null, $school->id));
        $this->assertSame('Y-m-d', SystemSetting::get('date_format', null, $school->id));
    }

    public function test_system_settings_validation_rejects_invalid_values(): void
    {
        School::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::SCHOOL_ADMIN]);

        $response = $this->actingAs($admin)->put(route('settings.system.update'), [
            'timezone' => 'Invalid/Timezone',
            'locale' => 'fr',
            'date_format' => 'invalid-format',
        ]);

        $response->assertSessionHasErrors(['timezone', 'locale', 'date_format']);
    }
}
