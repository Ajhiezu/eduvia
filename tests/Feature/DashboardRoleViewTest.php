<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRoleViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        School::factory()->create(['is_active' => true]);
    }

    public function test_student_dashboard_only_renders_student_features_and_no_admin_features(): void
    {
        $student = User::factory()->student()->create([
            'name' => 'Siti Siswa',
            'email' => 'siswa@eduvia.test',
        ]);

        $response = $this->actingAs($student)->get('/dashboard');

        $response->assertStatus(200);

        // Student-specific content must be visible
        $response->assertSee('Dashboard Siswa');
        $response->assertSee('Biodata &amp; Status Siswa', false);
        $response->assertSee('Presensi Saya');
        $response->assertSee('Tingkat Kehadiran');
        $response->assertSee('Riwayat Presensi Belajar Terbaru');

        // Admin features must NOT be visible
        $response->assertDontSee('Kelola Profil Sekolah');
        $response->assertDontSee('Fondasi Sistem &amp; Konfigurasi Sekolah (Modul 1)', false);
        $response->assertDontSee('Pengaturan Dasar Sistem');
        $response->assertDontSee('Pengujian Otorisasi Role (Backend Security Gate)');
        $response->assertDontSee('Kenaikan &amp; Kelulusan', false);
        $response->assertDontSee('Bantuan &amp; Desain', false);
    }

    public function test_teacher_dashboard_only_renders_teacher_features_and_no_admin_features(): void
    {
        $teacher = User::factory()->teacher()->create([
            'name' => 'Ahmad Guru',
            'email' => 'guru@eduvia.test',
        ]);

        $response = $this->actingAs($teacher)->get('/dashboard');

        $response->assertStatus(200);

        // Teacher-specific content must be visible
        $response->assertSee('Dashboard Guru');
        $response->assertSee('Buat Sesi Presensi');
        $response->assertSee('Jadwal Mengajar');
        $response->assertSee('Sesi Presensi Pembelajaran Terbaru');
        $response->assertSee('Pintasan Guru');

        // Admin features must NOT be visible
        $response->assertDontSee('Kelola Profil Sekolah');
        $response->assertDontSee('Fondasi Sistem &amp; Konfigurasi Sekolah (Modul 1)', false);
        $response->assertDontSee('Pengaturan Dasar Sistem');
        $response->assertDontSee('Pengujian Otorisasi Role (Backend Security Gate)');
        $response->assertDontSee('Kenaikan &amp; Kelulusan', false);
    }

    public function test_admin_dashboard_renders_administrative_management_cards(): void
    {
        $admin = User::factory()->schoolAdmin()->create([
            'name' => 'Budi Admin',
            'email' => 'admin@eduvia.test',
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);

        // Admin features must be visible
        $response->assertSee('Dashboard Administrator');
        $response->assertSee('Kelola Profil Sekolah');
        $response->assertSee('Fondasi Sistem &amp; Konfigurasi Sekolah (Modul 1)', false);
        $response->assertSee('Tahun Ajaran Akademik');
        $response->assertSee('Rombongan Belajar');
        $response->assertSee('Data Siswa (Kesiswaan)');
        $response->assertSee('Dashboard PPDB / SPMB');
    }

    public function test_super_admin_dashboard_renders_security_gate_verification(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Pengujian Otorisasi Role (Backend Security Gate)');
    }

    public function test_parent_accessing_dashboard_redirects_to_parent_dashboard(): void
    {
        $parent = User::factory()->parentRole()->create();

        $response = $this->actingAs($parent)->get('/dashboard');

        $response->assertRedirect(route('parent.dashboard'));
    }
}
