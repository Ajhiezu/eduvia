<?php

namespace Tests\Feature;

use App\Models\DeviceToken;
use App\Models\NotificationPreference;
use App\Models\School;
use App\Models\User;
use App\Services\Notification\Drivers\FcmNotificationDriver;
use App\Services\Notification\Drivers\MailNotificationDriver;
use App\Services\Notification\Drivers\WhatsAppNotificationDriver;
use App\Services\Notification\NotificationPayload;
use App\Services\Notification\NotificationService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NotificationDeliveryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_missing_credentials_are_logged_as_configuration_errors_and_can_be_retried(): void
    {
        $user = $this->recipient();
        $this->preferences($user);
        config(['services.notifications.simulate' => false, 'services.fcm.project_id' => null, 'services.whatsapp.endpoint' => null]);
        Http::preventStrayRequests();
        $payload = $this->payload($user);

        $result = app(NotificationService::class)->send($payload);
        $retry = app(NotificationService::class)->send($payload);

        $this->assertFalse($result['push']['success']);
        $this->assertFalse($result['whatsapp']['success']);
        $this->assertArrayNotHasKey('deduplicated', $retry);
        $this->assertDatabaseHas('notification_logs', ['user_id' => $user->id, 'channel' => 'push', 'status' => 'configuration_error', 'sent_at' => null]);
        $this->assertDatabaseHas('notification_logs', ['user_id' => $user->id, 'channel' => 'whatsapp', 'status' => 'configuration_error', 'sent_at' => null]);
        Http::assertNothingSent();
    }

    public function test_explicit_simulation_never_reports_delivery_success(): void
    {
        $user = $this->recipient();
        $this->preferences($user);
        config(['services.notifications.simulate' => true]);
        Http::preventStrayRequests();

        $result = app(NotificationService::class)->send($this->payload($user));

        $this->assertFalse($result['push']['success']);
        $this->assertFalse($result['whatsapp']['success']);
        $this->assertDatabaseHas('notification_logs', ['user_id' => $user->id, 'channel' => 'push', 'status' => 'simulated', 'sent_at' => null]);
        Http::assertNothingSent();
    }

    public function test_fcm_uses_signed_oauth_and_http_v1_with_string_data_and_cached_access_token(): void
    {
        $user = $this->recipient();
        $key = $this->configureFirebase();
        Http::preventStrayRequests();
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'oauth-test', 'expires_in' => 3600]),
            'https://fcm.googleapis.com/v1/projects/eduvia-test/messages:send' => Http::response(['name' => 'projects/eduvia-test/messages/123']),
        ]);

        $first = app(FcmNotificationDriver::class)->send($this->payload($user));
        $second = app(FcmNotificationDriver::class)->send($this->payload($user));

        $this->assertTrue($first['success']);
        $this->assertSame('accepted', $second['status']);
        Http::assertSentCount(3);
        Http::assertSent(function (Request $request) use ($key): bool {
            if ($request->url() !== 'https://oauth2.googleapis.com/token') {
                return false;
            }

            [$header, $claims, $signature] = explode('.', $request['assertion']);
            $decodedClaims = json_decode(base64_decode(strtr($claims, '-_', '+/')), true);
            $publicKey = openssl_pkey_get_details($key)['key'];

            return $decodedClaims['iss'] === 'firebase@eduvia-test.iam.gserviceaccount.com'
                && $decodedClaims['scope'] === 'https://www.googleapis.com/auth/firebase.messaging'
                && openssl_verify($header.'.'.$claims, base64_decode(strtr($signature, '-_', '+/')), $publicKey, OPENSSL_ALGO_SHA256) === 1;
        });
        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/messages:send')
            && $request->hasHeader('Authorization', 'Bearer oauth-test')
            && $request['message']['token'] === 'device-token'
            && $request['message']['data']['count'] === '3');
    }

    public function test_unregistered_fcm_device_is_disabled_and_not_reported_as_sent(): void
    {
        $user = $this->recipient();
        $this->configureFirebase();
        Http::preventStrayRequests();
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'oauth-test', 'expires_in' => 3600]),
            'https://fcm.googleapis.com/v1/projects/eduvia-test/messages:send' => Http::response(['error' => ['details' => [['errorCode' => 'UNREGISTERED']]]], 404),
        ]);

        $result = app(FcmNotificationDriver::class)->send($this->payload($user));

        $this->assertFalse($result['success']);
        $this->assertDatabaseHas('device_tokens', ['user_id' => $user->id, 'is_active' => false]);
        Http::assertSentCount(2);
    }

    public function test_oauth_failure_does_not_send_a_push_request(): void
    {
        $user = $this->recipient();
        $this->configureFirebase();
        Http::preventStrayRequests();
        Http::fake(['https://oauth2.googleapis.com/token' => Http::response(['error' => 'invalid_grant'], 400)]);

        $result = app(FcmNotificationDriver::class)->send($this->payload($user));

        $this->assertFalse($result['success']);
        Http::assertSentCount(1);
    }

    public function test_whatsapp_requires_opt_in_even_for_urgent_messages(): void
    {
        $user = $this->recipient();
        $this->preferences($user)->update(['whatsapp_opt_in' => false]);
        Http::preventStrayRequests();

        $result = app(WhatsAppNotificationDriver::class)->send(new NotificationPayload($user->id, $user->school_id, 'urgent', 'Pemberitahuan', 'Buka aplikasi', isUrgent: true));

        $this->assertFalse($result['success']);
        Http::assertNothingSent();
    }

    public function test_whatsapp_provider_acceptance_is_not_claimed_as_device_delivery(): void
    {
        $user = $this->recipient();
        $this->preferences($user);
        config(['services.notifications.simulate' => false, 'services.whatsapp.endpoint' => 'https://graph.facebook.com/test/messages', 'services.whatsapp.token' => 'test-token']);
        Http::preventStrayRequests();
        Http::fake(['https://graph.facebook.com/test/messages' => Http::response(['messages' => [['id' => 'wa-123']]])]);

        $result = app(WhatsAppNotificationDriver::class)->send($this->payload($user));

        $this->assertTrue($result['success']);
        $this->assertSame('accepted', $result['status']);
        Http::assertSent(fn (Request $request): bool => $request['to'] === '628123456789' && $request->hasHeader('Authorization', 'Bearer test-token'));
    }

    public function test_log_mailer_is_simulation_and_not_delivery(): void
    {
        $user = $this->recipient();
        config(['mail.default' => 'log']);

        $result = app(MailNotificationDriver::class)->send($this->payload($user));

        $this->assertFalse($result['success']);
        $this->assertSame('simulated', $result['status']);
    }

    private function recipient(): User
    {
        $user = User::factory()->parent()->create(['school_id' => School::factory(), 'phone' => '628123456789']);
        DeviceToken::create(['school_id' => $user->school_id, 'user_id' => $user->id, 'token' => 'device-token', 'platform' => 'android', 'is_active' => true]);

        return $user;
    }

    private function preferences(User $user): NotificationPreference
    {
        return NotificationPreference::create([
            'school_id' => $user->school_id, 'user_id' => $user->id, 'channel_push' => true,
            'channel_whatsapp' => true, 'channel_email' => false, 'quiet_hours_enabled' => false,
            'whatsapp_opt_in' => true, 'whatsapp_number' => '628123456789',
        ]);
    }

    private function payload(User $user): NotificationPayload
    {
        return new NotificationPayload($user->id, $user->school_id, 'test.notice', 'Pemberitahuan', 'Buka aplikasi untuk detail.', ['count' => 3]);
    }

    private function configureFirebase(): \OpenSSLAsymmetricKey
    {
        $options = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
        $bundledConfig = dirname(PHP_BINARY).'/extras/ssl/openssl.cnf';
        if (is_file($bundledConfig)) {
            $options['config'] = $bundledConfig;
        }
        $key = openssl_pkey_new($options);
        openssl_pkey_export($key, $privateKey, null, $options);
        config([
            'services.notifications.simulate' => false,
            'services.fcm.project_id' => 'eduvia-test',
            'services.fcm.client_email' => 'firebase@eduvia-test.iam.gserviceaccount.com',
            'services.fcm.private_key' => $privateKey,
        ]);

        return $key;
    }
}
