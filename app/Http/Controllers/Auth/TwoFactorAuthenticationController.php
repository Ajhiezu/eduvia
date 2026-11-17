<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class TwoFactorAuthenticationController extends Controller
{
    public function __construct(protected TwoFactorAuthService $twoFactor) {}

    public function show(Request $request): Response
    {
        $pending = $request->session()->get('two_factor_setup');
        $secret = $pending && $pending['user_id'] === $request->user()->id && $pending['expires_at'] > now()->timestamp
            ? decrypt($pending['secret']) : null;

        return response()->view('auth.security', [
            'isEnabled' => (bool) $request->user()->two_factor_confirmed_at,
            'secret' => $secret,
            'otpAuthUrl' => $secret ? $this->twoFactor->getOtpAuthUrl(config('app.name'), $request->user()->email, $secret) : null,
        ])->header('Cache-Control', 'no-store, private');
    }

    public function setup(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'string', 'current_password:web']]);
        abort_if((bool) $request->user()->two_factor_confirmed_at, 409, 'Autentikasi dua faktor sudah aktif.');

        $request->session()->put('two_factor_setup', [
            'user_id' => $request->user()->id,
            'secret' => encrypt($this->twoFactor->generateSecretKey()),
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        return to_route('account.security');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $validated = $request->validate(['two_factor_code' => ['required', 'string', 'regex:/^\\d{6}$/D']]);
        $pending = $request->session()->get('two_factor_setup');

        if (! $pending || $pending['user_id'] !== $request->user()->id || $pending['expires_at'] <= now()->timestamp || $request->user()->two_factor_confirmed_at) {
            $request->session()->forget('two_factor_setup');

            throw ValidationException::withMessages(['two_factor_code' => 'Pengaturan kedaluwarsa. Mulai aktivasi kembali.']);
        }

        $secret = decrypt($pending['secret']);
        if (! $this->twoFactor->verifyCode($secret, $validated['two_factor_code'])) {
            throw ValidationException::withMessages(['two_factor_code' => 'Kode autentikator tidak valid.']);
        }

        $recoveryCodes = $this->twoFactor->generateRecoveryCodes();
        $this->twoFactor->enable($request->user(), $secret, $recoveryCodes);
        $request->user()->tokens()->delete();
        $request->session()->forget('two_factor_setup');
        $request->session()->regenerate();

        return to_route('account.security')
            ->with('status', 'Autentikasi dua faktor aktif. Simpan kode pemulihan sebelum meninggalkan halaman ini.')
            ->with('recovery_codes', $recoveryCodes);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'current_password:web'],
            'two_factor_code' => ['required', 'string', 'max:64'],
        ]);

        if (! $this->twoFactor->verifyForUser($request->user(), $validated['two_factor_code'])) {
            throw ValidationException::withMessages(['two_factor_code' => 'Kode autentikator atau kode pemulihan tidak valid.']);
        }

        $this->twoFactor->disable($request->user());
        $request->user()->tokens()->delete();
        $request->session()->forget('two_factor_setup');
        $request->session()->regenerate();

        return to_route('account.security')->with('status', 'Autentikasi dua faktor dinonaktifkan.');
    }
}
