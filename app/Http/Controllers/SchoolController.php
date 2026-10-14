<?php

namespace App\Http\Controllers;

use App\Http\Requests\School\UpdateSchoolProfileRequest;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SchoolController extends Controller
{
    /**
     * Display the school profile page.
     */
    public function show(): View
    {
        $school = School::current();

        return view('school.show', compact('school'));
    }

    /**
     * Display the edit form for school profile.
     */
    public function edit(): View
    {
        $school = School::current();

        return view('school.edit', compact('school'));
    }

    /**
     * Update the school profile.
     */
    public function update(UpdateSchoolProfileRequest $request): RedirectResponse
    {
        $school = School::current() ?? new School;
        $validated = $request->validated();

        if ($request->hasFile('logo')) {
            // Remove previous logo if exists
            if ($school->logo_path && Storage::disk('public')->exists($school->logo_path)) {
                Storage::disk('public')->delete($school->logo_path);
            }

            $path = $request->file('logo')->store('schools/logos', 'public');
            $validated['logo_path'] = $path;
        }

        unset($validated['logo']);

        $school->fill($validated);
        $school->save();

        return redirect()->route('school.profile')
            ->with('status', 'Profil identitas sekolah berhasil diperbarui.');
    }

    /**
     * Remove custom school logo.
     */
    public function removeLogo(): RedirectResponse
    {
        $school = School::current();

        if ($school && $school->logo_path) {
            if (Storage::disk('public')->exists($school->logo_path)) {
                Storage::disk('public')->delete($school->logo_path);
            }
            $school->update(['logo_path' => null]);
        }

        return redirect()->route('school.profile')
            ->with('status', 'Logo sekolah berhasil dihapus.');
    }
}
