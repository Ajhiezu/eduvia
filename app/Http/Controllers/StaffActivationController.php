<?php

namespace App\Http\Controllers;

use App\Http\Requests\Staff\ActivateStaffAccountRequest;
use App\Models\StaffProfile;
use App\Models\StaffStatusHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffActivationController extends Controller
{
    /**
     * Show the staff account activation form.
     */
    public function show(string $token): View|RedirectResponse
    {
        $profile = StaffProfile::where('invitation_token', $token)
            ->with(['user', 'school'])
            ->first();

        if (! $profile) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Tautan aktivasi tidak valid atau telah kadaluarsa. Silakan hubungi admin sekolah Anda.']);
        }

        if ($profile->activated_at) {
            return redirect()->route('login')
                ->with('success', 'Akun Anda sudah pernah diaktifkan sebelumnya. Silakan masuk menggunakan kata sandi Anda.');
        }

        return view('auth.staff-activate', [
            'profile' => $profile,
            'user' => $profile->user,
            'school' => $profile->school,
            'token' => $token,
        ]);
    }

    /**
     * Process staff account activation.
     */
    public function activate(ActivateStaffAccountRequest $request, string $token): RedirectResponse
    {
        $profile = StaffProfile::where('invitation_token', $token)
            ->with('user')
            ->first();

        if (! $profile) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Tautan aktivasi tidak valid atau telah digunakan.']);
        }

        $user = $profile->user;

        DB::transaction(function () use ($user, $profile, $request) {
            $user->forceFill([
                'password' => Hash::make($request->password),
                'is_active' => true,
                'email_verified_at' => now(),
            ])->save();

            $profile->update([
                'activated_at' => now(),
                'status' => 'active',
                'invitation_token' => null,
            ]);

            StaffStatusHistory::create([
                'user_id' => $user->id,
                'changed_by' => $user->id,
                'action' => 'activated',
                'previous_value' => 'Menunggu Aktivasi',
                'new_value' => 'Aktif Mandiri',
                'reason' => 'Aktivasi akun mandiri melalui tautan undangan',
            ]);
        });

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', "Selamat datang di Eduvia, {$user->name}! Akun Anda telah aktif dan siap digunakan.");
    }
}
