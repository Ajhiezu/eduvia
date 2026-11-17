<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_security_settings_require_authentication(): void
    {
        $this->get(route('account.security'))->assertRedirect(route('login'));
        $this->post(route('account.two-factor.setup'), ['password' => 'password'])->assertRedirect(route('login'));
    }

    public function test_setup_requires_the_current_password(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->post(route('account.two-factor.setup'), ['password' => 'incorrect'])
            ->assertSessionHasErrors('password')->assertSessionMissing('two_factor_setup');
        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_activation_requires_a_valid_code_and_displays_recovery_codes_once(): void
    {
        $user = $this->createUser();
        $user->createToken('existing-device');
        $user->forceFill(['remember_token' => 'old-remember-token'])->save();

        $this->actingAs($user)->post(route('account.two-factor.setup'), ['password' => 'password'])
            ->assertRedirect(route('account.security'));
        $pending = session('two_factor_setup');
        $secret = decrypt($pending['secret']);
        $this->assertNull($user->fresh()->two_factor_confirmed_at);
        $this->get(route('account.security'))->assertOk()->assertSee($secret);

        $this->post(route('account.two-factor.confirm'), ['two_factor_code' => 'invalid'])
            ->assertSessionHasErrors('two_factor_code');
        $this->assertNull($user->fresh()->two_factor_confirmed_at);

        $this->post(route('account.two-factor.confirm'), [
            'two_factor_code' => app(TwoFactorAuthService::class)->calculateCode($secret),
        ])->assertRedirect(route('account.security'))->assertSessionHas('recovery_codes')->assertSessionMissing('two_factor_setup');

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
        $this->assertSame(0, $user->tokens()->count());
        $this->assertNotSame('old-remember-token', $user->fresh()->remember_token);
        $recoveryCode = session('recovery_codes')[0];
        $this->get(route('account.security'))->assertOk()->assertSee($recoveryCode);
        $this->get(route('account.security'))->assertOk()->assertDontSee($recoveryCode);
    }

    public function test_expired_setup_cannot_activate_two_factor(): void
    {
        $user = $this->createUser();
        $this->actingAs($user)->post(route('account.two-factor.setup'), ['password' => 'password']);
        $secret = decrypt(session('two_factor_setup.secret'));
        $this->travel(11)->minutes();

        $this->post(route('account.two-factor.confirm'), [
            'two_factor_code' => app(TwoFactorAuthService::class)->calculateCode($secret),
        ])->assertSessionHasErrors('two_factor_code')->assertSessionMissing('two_factor_setup');
        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_password_alone_cannot_authenticate_a_two_factor_account_on_web_or_api(): void
    {
        $user = $this->createUser();
        $this->enableTwoFactor($user);
        $credentials = ['email' => $user->email, 'password' => 'password'];

        $this->post('/login', $credentials)->assertSessionHasErrors('two_factor_code');
        $this->assertGuest();
        $this->postJson('/api/v1/auth/login', $credentials)
            ->assertUnprocessable()->assertJsonPath('errors.two_factor_code.0', 'Verifikasi dua faktor diperlukan.');
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_valid_authenticator_code_logs_in_and_cannot_be_reused_for_an_api_token(): void
    {
        $user = $this->createUser();
        $secret = $this->enableTwoFactor($user);
        $credentials = [
            'email' => $user->email,
            'password' => 'password',
            'two_factor_code' => app(TwoFactorAuthService::class)->calculateCode($secret),
        ];

        $this->post('/login', $credentials)->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->post('/logout');
        $this->postJson('/api/v1/auth/login', $credentials)->assertUnprocessable();
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_valid_authenticator_code_can_issue_a_mobile_token(): void
    {
        $user = $this->createUser();
        $secret = $this->enableTwoFactor($user);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'two_factor_code' => app(TwoFactorAuthService::class)->calculateCode($secret),
        ])->assertOk()->assertJsonStructure(['data' => ['token']]);
        $this->assertSame(1, $user->tokens()->count());
    }

    public function test_recovery_code_is_consumed_once_and_not_flashed_into_old_input(): void
    {
        $user = $this->createUser();
        $this->enableTwoFactor($user);
        $credentials = ['email' => $user->email, 'password' => 'password', 'two_factor_code' => 'RECOVERY01'];

        $this->post('/login', $credentials)->assertRedirect(route('dashboard'));
        $this->post('/logout');
        $this->post('/login', $credentials)->assertSessionHasErrors('two_factor_code')
            ->assertSessionMissing('_old_input.two_factor_code');
        $this->assertGuest();
    }

    public function test_disabling_requires_password_and_second_factor_and_revokes_mobile_tokens(): void
    {
        $user = $this->createUser();
        $this->enableTwoFactor($user);
        $user->createToken('old-device');

        $this->actingAs($user)->delete(route('account.two-factor.destroy'), [
            'password' => 'password', 'two_factor_code' => 'wrong-code',
        ])->assertSessionHasErrors('two_factor_code');
        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);

        $this->delete(route('account.two-factor.destroy'), [
            'password' => 'password', 'two_factor_code' => 'RECOVERY01',
        ])->assertRedirect(route('account.security'));

        $this->assertNull($user->fresh()->two_factor_secret);
        $this->assertSame(0, $user->tokens()->count());
    }

    private function createUser(): User
    {
        return User::factory()->schoolAdmin()->create(['school_id' => School::factory(), 'password' => 'password']);
    }

    private function enableTwoFactor(User $user): string
    {
        $secret = app(TwoFactorAuthService::class)->generateSecretKey();
        app(TwoFactorAuthService::class)->enable($user, $secret, ['RECOVERY01', 'RECOVERY02']);

        return $secret;
    }
}
