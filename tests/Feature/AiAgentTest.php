<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\School;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\AI\AiAgentService;
use App\Services\AI\AiAgentSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AiAgentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gemini.api_key' => null]);
        Http::preventStrayRequests();
    }

    public static function staffRoles(): array
    {
        return [['super_admin'], ['school_admin'], ['teacher'], ['staff']];
    }

    public static function excludedRoles(): array
    {
        return [['student'], ['parent']];
    }

    #[DataProvider('staffRoles')]
    public function test_school_team_can_open_agent_and_read_status(string $role): void
    {
        $user = $this->user($role);
        $this->actingAs($user)->get(route('ai-agent.index'))->assertOk()->assertSee('AI Agent')->assertSee('data-ai-agent', false);
        $this->getJson(route('ai-agent.status'))->assertOk()->assertExactJson(['school_id' => $user->school_id, 'enabled' => false, 'configured' => false, 'model' => 'gemini-3.1-flash-lite']);
        if (in_array($role, ['teacher', 'staff'], true)) {
            $this->get(route('ai-agent.settings'))->assertForbidden();
            $this->putJson(route('ai-agent.settings.update'), [])->assertForbidden();
        }
        Http::assertNothingSent();
    }

    #[DataProvider('excludedRoles')]
    public function test_students_and_parents_cannot_access_any_agent_endpoint(string $role): void
    {
        $this->actingAs($this->user($role));
        foreach (['index', 'status', 'settings'] as $route) {
            $this->getJson(route('ai-agent.'.$route))->assertForbidden();
        }
        $this->postJson(route('ai-agent.generate'), ['prompt' => 'Buat soal'])->assertForbidden();
        $this->putJson(route('ai-agent.settings.update'), [])->assertForbidden();
        $this->followingRedirects()->get('/dashboard')->assertOk()->assertDontSee('data-ai-agent', false)->assertDontSee('Buka AI Agent');
        Http::assertNothingSent();
    }

    public function test_guest_cannot_generate_or_configure_agent(): void
    {
        $this->postJson(route('ai-agent.generate'), ['prompt' => 'Buat soal'])->assertUnauthorized();
        $this->putJson(route('ai-agent.settings.update'), [])->assertUnauthorized();
        Http::assertNothingSent();
    }

    public function test_global_super_admin_uses_dashboard_school_without_assigned_school_id(): void
    {
        $school = School::factory()->create(['is_active' => true]);
        $user = User::factory()->create(['school_id' => null, 'role' => UserRole::SUPER_ADMIN]);
        app(AiAgentSettings::class)->update($school->id, 'school-test-key-1234567890', true);
        $this->actingAs($user)->getJson(route('ai-agent.status'))->assertOk()->assertJsonPath('school_id', $school->id)->assertJsonPath('enabled', true)->assertJsonPath('configured', true);
        $this->get(route('ai-agent.index'))->assertOk();
        Http::assertNothingSent();
    }

    public function test_global_super_admin_respects_explicit_school_header(): void
    {
        School::factory()->create(['is_active' => true]);
        $selected = School::factory()->create(['is_active' => true]);
        $user = User::factory()->create(['school_id' => null, 'role' => UserRole::SUPER_ADMIN]);
        app(AiAgentSettings::class)->update($selected->id, 'selected-school-key-1234567890', true);
        $this->actingAs($user)->getJson(route('ai-agent.status'), ['X-School-ID' => (string) $selected->id])->assertOk()->assertJsonPath('school_id', $selected->id)->assertJsonPath('configured', true);
        Http::assertNothingSent();
    }

    public function test_admin_saves_encrypted_key_without_exposing_it_in_responses(): void
    {
        $user = $this->user('school_admin');
        $key = 'school-test-key-not-a-real-secret-12345';
        $this->actingAs($user)->putJson(route('ai-agent.settings.update'), ['api_key' => $key, 'enabled' => true])->assertOk()->assertJsonPath('configured', true)->assertDontSee($key);
        $encrypted = SystemSetting::where('school_id', $user->school_id)->where('key', 'ai_agent_api_key')->value('value');
        $this->assertNotSame($key, $encrypted);
        $this->assertSame($key, Crypt::decryptString($encrypted));
        $this->get(route('ai-agent.settings'))->assertOk()->assertDontSee($key)->assertDontSee($encrypted);
        $this->getJson(route('ai-agent.status'))->assertOk()->assertDontSee($key)->assertJsonPath('enabled', true);
        $this->putJson(route('ai-agent.settings.update'), ['api_key' => null, 'enabled' => false])->assertOk()->assertJsonPath('enabled', false);
        $this->assertSame($key, app(AiAgentSettings::class)->apiKey($user->school_id));
        Http::assertNothingSent();
    }

    public function test_settings_require_key_to_enable(): void
    {
        $this->actingAs($this->user('school_admin'));
        $this->putJson(route('ai-agent.settings.update'), ['enabled' => true])->assertUnprocessable()->assertJsonValidationErrors('api_key');
        $this->assertDatabaseMissing('system_settings', ['key' => 'ai_agent_api_key']);
        Http::assertNothingSent();
    }

    public function test_server_key_enables_agent_unless_school_admin_disables_it(): void
    {
        $user = $this->user('school_admin');
        config(['services.gemini.api_key' => 'server-school-key-1234567890']);
        $this->actingAs($user)->getJson(route('ai-agent.status'))->assertOk()->assertJsonPath('configured', true)->assertJsonPath('enabled', true);
        $this->putJson(route('ai-agent.settings.update'), ['enabled' => false])->assertOk()->assertJsonPath('enabled', false);
        $this->getJson(route('ai-agent.status'))->assertOk()->assertJsonPath('enabled', false);
        Http::assertNothingSent();
    }

    public function test_validation_does_not_flash_secret_back_to_session(): void
    {
        $key = 'invalid secret with spaces';
        $this->actingAs($this->user('school_admin'))->put(route('ai-agent.settings.update'), ['api_key' => $key, 'enabled' => true])->assertSessionHasErrors('api_key');
        $this->assertNull(session()->getOldInput('api_key'));
        Http::assertNothingSent();
    }

    public function test_other_school_configuration_is_not_used_or_overwritten(): void
    {
        $owner = $this->user('school_admin');
        app(AiAgentSettings::class)->update($owner->school_id, 'owner-school-test-key-12345678', true);
        $outsider = $this->user('school_admin');
        $this->actingAs($outsider)->withSession(['active_school_id' => $owner->school_id])->getJson(route('ai-agent.status'))->assertOk()->assertJsonPath('configured', false)->assertJsonPath('enabled', false);
        $this->putJson(route('ai-agent.settings.update'), ['school_id' => $owner->school_id, 'enabled' => false])->assertUnprocessable()->assertJsonValidationErrors('school_id');
        $this->assertTrue(app(AiAgentSettings::class)->enabled($owner->school_id));
        Http::assertNothingSent();
    }

    public function test_disabled_or_unconfigured_agent_never_calls_provider(): void
    {
        $user = $this->user('teacher');
        $this->actingAs($user)->postJson(route('ai-agent.generate'), ['prompt' => 'Buat soal'])->assertStatus(503)->assertJsonPath('message', 'AI Agent belum diaktifkan. Hubungi administrator sekolah.');
        app(AiAgentSettings::class)->update($user->school_id, null, true);
        $this->postJson(route('ai-agent.generate'), ['prompt' => 'Buat soal'])->assertStatus(503)->assertSee('API key Gemini belum diatur');
        Http::assertNothingSent();
    }

    public function test_text_draft_accepts_empty_fields_and_keeps_school_data_unchanged(): void
    {
        $user = $this->readyUser();
        $this->fakeDraft(['reply' => 'Berikut tiga contoh soal pecahan.', 'fields' => []]);
        $this->actingAs($user)->postJson(route('ai-agent.generate'), ['prompt' => 'Buat tiga soal pecahan'])->assertOk()->assertJsonPath('reply', 'Berikut tiga contoh soal pecahan.')->assertJsonPath('fields', []);
        $this->assertSame($user->name, $user->fresh()->name);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request): bool => $request->hasHeader('x-goog-api-key', 'school-test-key-1234567890') && ! str_contains($request->url(), 'key='));
    }

    public function test_form_draft_filters_sensitive_unknown_and_invalid_option_fields(): void
    {
        $user = $this->readyUser();
        $this->fakeDraft(['reply' => 'Tinjau draf berikut.', 'fields' => [
            ['name' => 'question', 'value' => 'Berapa hasil 1/2 + 1/4?'],
            ['name' => 'school_id', 'value' => '999'],
            ['name' => 'role', 'value' => 'super_admin'],
            ['name' => 'unknown', 'value' => 'Tidak boleh'],
            ['name' => 'type', 'value' => 'unknown-type'],
        ]]);
        $this->actingAs($user)->postJson(route('ai-agent.generate'), ['prompt' => 'Buat soal', 'form_fields' => [
            ['name' => 'question', 'label' => 'Pertanyaan', 'type' => 'textarea'],
            ['name' => 'school_id', 'label' => 'Sekolah', 'type' => 'text'],
            ['name' => 'role', 'label' => 'Peran', 'type' => 'text'],
            ['name' => 'type', 'label' => 'Jenis', 'type' => 'select', 'options' => [['value' => 'essay', 'label' => 'Esai']]],
        ]])->assertOk()->assertExactJson(['reply' => 'Tinjau draf berikut.', 'fields' => [['name' => 'question', 'label' => 'Pertanyaan', 'value' => 'Berapa hasil 1/2 + 1/4?']], 'model' => 'gemini-3.1-flash-lite']);
        Http::assertSent(fn ($request): bool => ! str_contains($request['contents'][0]['parts'][0]['text'], 'school_id'));
        Http::assertSentCount(1);
    }

    public function test_request_cannot_override_school_model_or_send_existing_form_values(): void
    {
        $this->actingAs($this->readyUser())->postJson(route('ai-agent.generate'), ['prompt' => 'Buat soal', 'school_id' => 5, 'model' => 'other-model', 'form_fields' => [['name' => 'question', 'label' => 'Pertanyaan', 'type' => 'textarea', 'value' => 'private current value']]])->assertUnprocessable()->assertJsonValidationErrors(['school_id', 'model', 'form_fields.0']);
        Http::assertNothingSent();
    }

    public function test_provider_quota_error_returns_429_without_retry_or_secret_leak(): void
    {
        $user = $this->readyUser();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'school-test-key-1234567890']], 429)]);
        $this->actingAs($user)->postJson(route('ai-agent.generate'), ['prompt' => 'Buat soal'])->assertStatus(429)->assertSee('Kuota')->assertDontSee('school-test-key-1234567890');
        Http::assertSentCount(1);
    }

    public function test_malformed_provider_response_is_rejected_with_502(): void
    {
        $user = $this->readyUser();
        $this->fakeDraft(['reply' => 'Invalid shape', 'fields' => 'not an array']);
        $this->actingAs($user)->postJson(route('ai-agent.generate'), ['prompt' => 'Buat soal'])->assertStatus(502)->assertSee('Respons AI belum sesuai format');
        Http::assertSentCount(1);
    }

    public function test_local_rate_limit_stops_sixth_request_before_provider(): void
    {
        $user = $this->readyUser();
        $this->fakeDraft(['reply' => 'Contoh draf', 'fields' => []]);
        $this->actingAs($user);
        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('ai-agent.generate'), ['prompt' => 'Buat soal'])->assertOk();
        }
        $this->postJson(route('ai-agent.generate'), ['prompt' => 'Buat soal'])->assertTooManyRequests();
        Http::assertSentCount(5);
    }

    public function test_unexpected_service_exception_does_not_expose_internal_details(): void
    {
        $user = $this->readyUser();
        $this->mock(AiAgentService::class)->shouldReceive('generate')->once()->andThrow(new \RuntimeException('Internal database credentials'));
        $this->actingAs($user)->postJson(route('ai-agent.generate'), ['prompt' => 'Buat soal'])->assertStatus(503)->assertDontSee('Internal database credentials');
        Http::assertNothingSent();
    }

    public function test_invalid_json_from_provider_is_rejected_with_502(): void
    {
        $user = $this->readyUser();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => 'not JSON']]]]]])]);
        $this->actingAs($user)->postJson(route('ai-agent.generate'), ['prompt' => 'Buat soal'])->assertStatus(502)->assertSee('format respons yang tidak valid');
        Http::assertSentCount(1);
    }

    public function test_contact_information_is_scrubbed_before_sending_prompt(): void
    {
        $user = $this->readyUser();
        $this->fakeDraft(['reply' => 'Draf surat', 'fields' => []]);
        $this->actingAs($user)->postJson(route('ai-agent.generate'), ['prompt' => 'Buat surat untuk '.$user->name.' dengan email pribadi@example.com'])->assertOk();
        Http::assertSent(function ($request) use ($user): bool {
            $context = $request['contents'][0]['parts'][0]['text'];

            return ! str_contains($context, $user->name) && ! str_contains($context, 'pribadi@example.com') && str_contains($context, '[EMAIL_DISAMARKAN]');
        });
        Http::assertSentCount(1);
    }

    private function user(string $role): User
    {
        return User::factory()->create(['school_id' => School::factory()->create()->id, 'role' => UserRole::from($role)]);
    }

    private function readyUser(): User
    {
        $user = $this->user('teacher');
        app(AiAgentSettings::class)->update($user->school_id, 'school-test-key-1234567890', true);

        return $user;
    }

    private function fakeDraft(array $draft): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode($draft)]]]]]])]);
    }
}
