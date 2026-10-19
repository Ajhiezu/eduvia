<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected School $schoolA;

    protected School $schoolB;

    protected User $adminA;

    protected User $adminB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->schoolA = School::create(['name' => 'Sekolah Audit A', 'code' => 'AUD-A', 'is_active' => true]);
        $this->schoolB = School::create(['name' => 'Sekolah Audit B', 'code' => 'AUD-B', 'is_active' => true]);

        $this->adminA = User::factory()->schoolAdmin()->create(['school_id' => $this->schoolA->id]);
        $this->adminB = User::factory()->schoolAdmin()->create(['school_id' => $this->schoolB->id]);
    }

    /**
     * Test 1: Siklus hidup model (created, updated, deleted) otomatis tercatat dalam audit_logs.
     */
    public function test_model_lifecycle_automatically_recorded_in_audit_logs(): void
    {
        $this->actingAs($this->adminA);

        // 1. Create a user
        $user = User::create([
            'school_id' => $this->schoolA->id,
            'name' => 'Ahmad Santoso',
            'email' => 'ahmad.audit@eduvia.id',
            'password' => 'Rahasia123!',
            'role' => 'student',
            'is_active' => true,
        ]);

        $createLog = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $user->id)
            ->where('action', 'created')
            ->first();

        $this->assertNotNull($createLog, 'Audit log created tidak ditemukan');
        $this->assertEquals((int) $this->schoolA->id, (int) $createLog->school_id);
        $this->assertEquals((int) $this->adminA->id, (int) $createLog->user_id);
        $this->assertEquals('Ahmad Santoso', $createLog->new_values['name']);

        // 2. Update the user
        $user->update(['name' => 'Ahmad Santoso S.Pd']);

        $updateLog = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $user->id)
            ->where('action', 'updated')
            ->first();

        $this->assertNotNull($updateLog, 'Audit log updated tidak ditemukan');
        $this->assertEquals('Ahmad Santoso', $updateLog->old_values['name']);
        $this->assertEquals('Ahmad Santoso S.Pd', $updateLog->new_values['name']);

        // 3. Delete the user
        $user->delete();

        $deleteLog = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $user->id)
            ->where('action', 'deleted')
            ->first();

        $this->assertNotNull($deleteLog, 'Audit log deleted tidak ditemukan');
    }

    /**
     * Test 2: Data sensitif dan PII (kata sandi, NIK, nomor rekening) dimasking dengan aman.
     */
    public function test_audit_logs_mask_sensitive_data_and_pii(): void
    {
        $this->actingAs($this->adminA);

        $student = User::create([
            'school_id' => $this->schoolA->id,
            'name' => 'Budi PII Test',
            'email' => 'budi.pii@eduvia.id',
            'password' => 'SuperSecretPassword!',
            'role' => 'student',
            'is_active' => true,
        ]);

        $log = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $student->id)
            ->where('action', 'created')
            ->first();

        $this->assertNotNull($log);
        // Password harus dimasking, tidak boleh ada string aslinya
        $this->assertEquals('********', $log->new_values['password']);
        $this->assertStringNotContainsString('SuperSecretPassword!', json_encode($log->new_values));

        // Test NIK masking via helper
        $masked = AuditLog::maskSensitiveData([
            'name' => 'Calon Siswa',
            'nik' => '3201011203050001',
            'bank_account_number' => '123456789012',
            'password' => 'UnmaskedPass',
        ]);

        $this->assertEquals('320101******0001', $masked['nik']);
        $this->assertEquals('********9012', $masked['bank_account_number']);
        $this->assertEquals('********', $masked['password']);
    }

    /**
     * Test 3: Audit log strictly immutable (tidak dapat dimodifikasi atau dihapus oleh siapapun).
     */
    public function test_audit_logs_are_strictly_immutable(): void
    {
        $log = AuditLog::record(
            action: 'custom_security_event',
            description: 'Percobaan akses unauthorized',
            schoolId: $this->schoolA->id
        );

        $this->assertNotNull($log->id);

        // 1. Percobaan Update harus melempar RuntimeException
        try {
            $log->update(['description' => 'Upaya pemalsuan audit']);
            $this->fail('Audit log seharusnya tidak bisa di-update');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('immutable', strtolower($e->getMessage()));
        }

        // 2. Percobaan Delete harus melempar RuntimeException
        try {
            $log->delete();
            $this->fail('Audit log seharusnya tidak bisa di-delete');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('immutable', strtolower($e->getMessage()));
        }

        // Pastikan rekord log tetap utuh di database
        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'action' => 'custom_security_event',
        ]);
    }

    /**
     * Test 4: Audit log terisolasi berdasarkan tenant sekolah (Multi-Tenant Scoping).
     */
    public function test_audit_logs_are_isolated_by_tenant(): void
    {
        // Buat log di Sekolah A
        AuditLog::record(
            action: 'secret_action_a',
            description: 'Audit log rahasia sekolah A',
            schoolId: $this->schoolA->id
        );

        // Buat log di Sekolah B
        AuditLog::record(
            action: 'secret_action_b',
            description: 'Audit log rahasia sekolah B',
            schoolId: $this->schoolB->id
        );

        // Admin Sekolah A hanya dapat melihat log sekolah A
        $this->actingAs($this->adminA);
        $logsA = AuditLog::all();
        $this->assertTrue($logsA->contains('action', 'secret_action_a'));
        $this->assertFalse($logsA->contains('action', 'secret_action_b'));

        // Admin Sekolah B hanya dapat melihat log sekolah B
        $this->actingAs($this->adminB);
        $logsB = AuditLog::all();
        $this->assertTrue($logsB->contains('action', 'secret_action_b'));
        $this->assertFalse($logsB->contains('action', 'secret_action_a'));
    }
}
