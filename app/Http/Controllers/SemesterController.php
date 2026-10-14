<?php

namespace App\Http\Controllers;

use App\Http\Requests\Academic\StoreSemesterRequest;
use App\Http\Requests\Academic\UpdateSemesterRequest;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SemesterController extends Controller
{
    /**
     * Display a listing of semesters.
     */
    public function index(Request $request): View
    {
        $school = School::current();
        $academicYearId = $request->query('academic_year_id');

        $academicYears = AcademicYear::when($school, fn ($q) => $q->where('school_id', $school->id))
            ->orderByDesc('start_date')
            ->get();

        $semesters = Semester::with('academicYear')
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->when(! $academicYearId && $school, function ($q) use ($school): void {
                $q->whereHas('academicYear', fn ($ay) => $ay->where('school_id', $school->id));
            })
            ->orderByDesc('start_date')
            ->paginate(10);

        return view('semesters.index', compact('semesters', 'academicYears', 'academicYearId'));
    }

    /**
     * Show the form for creating a new semester.
     */
    public function create(Request $request): View
    {
        $school = School::current();
        $selectedYearId = $request->query('academic_year_id');
        $academicYears = AcademicYear::when($school, fn ($q) => $q->where('school_id', $school->id))
            ->orderByDesc('start_date')
            ->get();

        return view('semesters.create', compact('academicYears', 'selectedYearId'));
    }

    /**
     * Store a newly created semester.
     */
    public function store(StoreSemesterRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $isActive = $request->boolean('is_active');
        $validated['is_active'] = false;

        $semester = Semester::create($validated);

        if ($isActive) {
            $semester->activate();
        }

        return redirect()->route('semesters.index', ['academic_year_id' => $semester->academic_year_id])
            ->with('status', "Semester {$semester->name} berhasil ditambahkan.");
    }

    /**
     * Show the form for editing the specified semester.
     */
    public function edit(Semester $semester): View
    {
        $school = School::current();
        $academicYears = AcademicYear::when($school, fn ($q) => $q->where('school_id', $school->id))
            ->orderByDesc('start_date')
            ->get();

        return view('semesters.edit', compact('semester', 'academicYears'));
    }

    /**
     * Update the specified semester.
     */
    public function update(UpdateSemesterRequest $request, Semester $semester): RedirectResponse
    {
        $validated = $request->validated();
        $isActive = $request->boolean('is_active');

        unset($validated['is_active']);
        $semester->update($validated);

        if ($isActive && ! $semester->is_active) {
            $semester->activate();
        } elseif (! $isActive && $semester->is_active) {
            $semester->deactivate();
        }

        return redirect()->route('semesters.index', ['academic_year_id' => $semester->academic_year_id])
            ->with('status', "Semester {$semester->name} berhasil diperbarui.");
    }

    /**
     * Atomically activate the specified semester.
     */
    public function activate(Semester $semester): RedirectResponse
    {
        $semester->activate();

        return redirect()->route('semesters.index', ['academic_year_id' => $semester->academic_year_id])
            ->with('status', "Semester {$semester->name} ({$semester->academicYear->name}) telah diaktifkan.");
    }

    /**
     * Remove the specified semester from storage.
     */
    public function destroy(Semester $semester): RedirectResponse
    {
        if ($semester->is_active) {
            return back()->withErrors([
                'semester' => 'Tidak dapat menghapus semester yang sedang aktif. Aktifkan semester lain terlebih dahulu.',
            ]);
        }

        $yearId = $semester->academic_year_id;
        $semester->delete();

        return redirect()->route('semesters.index', ['academic_year_id' => $yearId])
            ->with('status', "Semester {$semester->name} berhasil dihapus.");
    }
}
