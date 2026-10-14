<?php

namespace App\Http\Controllers;

use App\Http\Requests\Setting\UpdateSystemSettingsRequest;
use App\Models\School;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    /**
     * Display the system settings form.
     */
    public function index(): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $settings = [
            'timezone' => SystemSetting::get('timezone', config('app.timezone', 'Asia/Jakarta'), $schoolId),
            'locale' => SystemSetting::get('locale', config('app.locale', 'id'), $schoolId),
            'date_format' => SystemSetting::get('date_format', 'd/m/Y', $schoolId),
        ];

        return view('settings.system', compact('settings', 'school'));
    }

    /**
     * Update system settings.
     */
    public function update(UpdateSystemSettingsRequest $request): RedirectResponse
    {
        $school = School::current();
        $schoolId = $school?->id;
        $validated = $request->validated();

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value, 'string', 'general', $schoolId);
        }

        return redirect()->route('settings.system')
            ->with('status', 'Pengaturan dasar sistem berhasil diperbarui.');
    }
}
