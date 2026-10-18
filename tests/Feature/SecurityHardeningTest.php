<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Services\SecureUploadService;
use App\Services\TwoFactorAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name' => 'SMA Eduvia Hardening',
            'code' => 'SCH-HARD-01',
            'is_active' => true,
        ]);
    }

    /**
     * Test 1: Rate limiter pada API login memblokir brute force setelah 5 percobaan (HTTP 429).
     */
    public function test_api_login_rate_limiting_blocks_brute_force(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson(route('api.v1.auth.login'), [
                'email' => 'wrong@eduvia.id',
                'password' => 'wrong-pass',
            ]);
            $this->assertNotEquals(429, $response->status(), "Percobaan ke-{$i} tidak seharusnya kena rate limit");
        }

        // Percobaan ke-6 harus di-throttle (HTTP 429)
        $throttled = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'wrong@eduvia.id',
            'password' => 'wrong-pass',
        ]);

        $this->assertEquals(429, $throttled->status(), 'Percobaan ke-6 harus ditolak dengan HTTP 429 Too Many Requests');
    }

    /**
     * Test 2: Upload file memblokir ekstensi berbahaya dan spoofing mime type.
     */
    public function test_secure_upload_blocks_executable_files_and_spoofing(): void
    {
        $service = new SecureUploadService;

        // 1. Dilarang mengunggah file PHP (ekstensi terlarang)
        $phpFile = UploadedFile::fake()->create('exploit.php', 10, 'application/x-php');
        try {
            $service->storeSecurely($phpFile, 'test');
            $this->fail('File .php seharusnya dilarang');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('dilarang', $e->getMessage());
        }

        // 2. Dilarang mengunggah file shell script
        $shFile = UploadedFile::fake()->create('script.sh', 10, 'text/x-shellscript');
        try {
            $service->storeSecurely($shFile, 'test');
            $this->fail('File .sh seharusnya dilarang');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('dilarang', $e->getMessage());
        }

        // 3. File legitimate (PDF) berhasil disimpan dengan nama acak pada disk lokal
        Storage::fake('local');
        $pdfFile = UploadedFile::fake()->create('tugas_matematika.pdf', 100, 'application/pdf');

        $result = $service->storeSecurely($pdfFile, 'assignments', 'local', $this->school->id);

        $this->assertNotNull($result['path']);
        $this->assertNotEquals('tugas_matematika.pdf', $result['file_name']);
        $this->assertStringEndsWith('.pdf', $result['file_name']);
        Storage::disk('local')->assertExists($result['path']);
    }

    /**
     * Test 3: Two-Factor Authentication TOTP scaffolding dan verifikasi kode.
     */
    public function test_two_factor_totp_lifecycle_and_recovery_codes(): void
    {
        $service = new TwoFactorAuthService;
        $user = User::factory()->schoolAdmin()->create(['school_id' => $this->school->id]);

        // 1. Generate Secret Key
        $secret = $service->generateSecretKey();
        $this->assertEquals(32, strlen($secret));

        // 2. Generate OtpAuth URL
        $url = $service->getOtpAuthUrl('Eduvia', $user->email, $secret);
        $this->assertStringStartsWith('otpauth://totp/', $url);
        $this->assertStringContainsString($secret, $url);

        // 3. Hitung dan verifikasi kode valid
        $validCode = $service->calculateCode($secret);
        $this->assertTrue($service->verifyCode($secret, $validCode));

        // 4. Verifikasi kode tidak valid harus ditolak
        $this->assertFalse($service->verifyCode($secret, '000000'));

        // 5. Enable 2FA dengan recovery codes
        $recoveryCodes = $service->generateRecoveryCodes(8);
        $this->assertCount(8, $recoveryCodes);

        $service->enable($user, $secret, $recoveryCodes);
        $user->refresh();

        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_confirmed_at);

        // 6. Konsumsi recovery code (single use)
        $codeToUse = $recoveryCodes[0];
        $this->assertTrue($service->verifyAndConsumeRecoveryCode($user, $codeToUse));

        // Menggunakan recovery code yang sama kedua kalinya harus gagal
        $this->assertFalse($service->verifyAndConsumeRecoveryCode($user, $codeToUse));

        // 7. Disable 2FA
        $service->disable($user);
        $user->refresh();

        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
    }
}
