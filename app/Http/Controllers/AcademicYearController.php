<?php

namespace App\Http\Controllers;

use App\Http\Requests\Academic\StoreAcademicYearRequest;
use App\Http\Requests\Academic\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of academic years.
     */
    public function index(): View
    {
        $school = School::current();
        $academicYears = AcademicYear::with('semesters')
            ->when($school, fn ($q) => $q->where('school_id', $school->id))
            ->orderByDesc('start_date')
            ->paginate(10);

        return view('academic-years.index', compact('academicYears', 'school'));
    }

    /**
     * Show the form for creating a new academic year.
     */
    public function create(): View
    {
        $school = School::current();

        return view('academic-years.create', compact('school'));
    }

    /**
     * Store a newly created academic year.
     */
    public function store(StoreAcademicYearRequest $request): RedirectResponse
    {
        $school = School::current();
        $validated = $request->validated();
        $isActive = $request->boolean('is_active');

        $validated['school_id'] = $validated['school_id'] ?? $school?->id;
        $validated['is_active'] = false; // set initial false, then activate atomically if requested

        $academicYear = AcademicYear::create($validated);

        if ($isActive) {
            $academicYear->activate();
        }

        return redirect()->route('academic-years.index')
            ->with('status', "Tahun ajaran {$academicYear->name} berhasil ditambahkan.");
    }

    /**
     * Show the form for editing the specified academic year.
     */
    public function edit(AcademicYear $academicYear): View
    {
        return view('academic-years.edit', compact('academicYear'));
    }

    /**
     * Update the specified academic year.
     */
    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $request->validated();
        $isActive = $request->boolean('is_active');

        unset($validated['is_active']);
        $academicYear->update($validated);

        if ($isActive && ! $academicYear->is_active) {
            $academicYear->activate();
        } elseif (! $isActive && $academicYear->is_active) {
            $academicYear->deactivate();
        }

        return redirect()->route('academic-years.index')
            ->with('status', "Tahun ajaran {$academicYear->name} berhasil diperbarui.");
    }

    /**
     * Atomically activate the specified academic year.
     */
    public function activate(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->activate();

        return redirect()->route('academic-years.index')
            ->with('status', "Tahun ajaran {$academicYear->name} telah diaktifkan.");
    }

    /**
     * Remove the specified academic year from storage.
     */
    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        if ($academicYear->is_active) {
            return back()->withErrors([
                'academic_year' => 'Tidak dapat menghapus tahun ajaran yang sedang aktif. Aktifkan tahun ajaran lain terlebih dahulu.',
            ]);
        }

        $academicYear->delete();

        return redirect()->route('academic-years.index')
            ->with('status', "Tahun ajaran {$academicYear->name} berhasil dihapus.");
    }
}
