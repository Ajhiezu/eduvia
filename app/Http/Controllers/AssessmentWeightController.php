<?php

namespace App\Http\Controllers;

use App\Http\Requests\Report\StoreAssessmentWeightRequest;
use App\Models\AcademicYear;
use App\Models\AssessmentWeightConfiguration;
use App\Models\School;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentWeightController extends Controller
{
    /**
     * Resolve the active school ID for current context.
     */
    protected function currentSchoolId(): ?int
    {
        return auth()->user()?->school_id ?? School::current()?->id;
    }

    /**
     * Display a listing of assessment weight configurations.
     */
    public function index(Request $request): View
    {
        $schoolId = $this->currentSchoolId();

        $configurations = AssessmentWeightConfiguration::query()
            ->with(['academicYear', 'semester', 'subject', 'creator'])
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15);

        return view('reports.weights.index', compact('configurations'));
    }

    /**
     * Show form to create a new weight configuration.
     */
    public function create(): View
    {
        $schoolId = $this->currentSchoolId();

        $academicYears = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->get();
        $semesters = Semester::when($schoolId, fn ($q) => $q->whereHas('academicYear', fn ($ay) => $ay->where('school_id', $schoolId)))->get();
        $subjects = Subject::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->orderBy('name')->get();

        return view('reports.weights.create', compact('academicYears', 'semesters', 'subjects'));
    }

    /**
     * Store a newly created weight configuration.
     */
    public function store(StoreAssessmentWeightRequest $request): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();

        $validated = $request->validated();
        $validated['school_id'] = $schoolId;
        $validated['created_by'] = auth()->id();
        $validated['grade_scale'] = AssessmentWeightConfiguration::defaultGradeScale();

        AssessmentWeightConfiguration::create($validated);

        return redirect()->route('reports.weights.index')
            ->with('success', 'Skema konfigurasi bobot penilaian berhasil disimpan.');
    }

    /**
     * Show form to edit an existing weight configuration.
     */
    public function edit(AssessmentWeightConfiguration $weight): View
    {
        $schoolId = $this->currentSchoolId();
        if ($schoolId && (int) $weight->school_id !== (int) $schoolId) {
            abort(403, 'Akses ditolak: Konfigurasi ini milik sekolah lain.');
        }

        $academicYears = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->get();
        $semesters = Semester::when($schoolId, fn ($q) => $q->whereHas('academicYear', fn ($ay) => $ay->where('school_id', $schoolId)))->get();
        $subjects = Subject::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->orderBy('name')->get();

        return view('reports.weights.edit', compact('weight', 'academicYears', 'semesters', 'subjects'));
    }

    /**
     * Update the specified weight configuration.
     */
    public function update(StoreAssessmentWeightRequest $request, AssessmentWeightConfiguration $weight): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();
        if ($schoolId && (int) $weight->school_id !== (int) $schoolId) {
            abort(403, 'Akses ditolak: Konfigurasi ini milik sekolah lain.');
        }

        $validated = $request->validated();
        $weight->update($validated);

        return redirect()->route('reports.weights.index')
            ->with('success', 'Konfigurasi bobot penilaian berhasil diperbarui.');
    }

    /**
     * Delete the specified weight configuration.
     */
    public function destroy(AssessmentWeightConfiguration $weight): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();
        if ($schoolId && (int) $weight->school_id !== (int) $schoolId) {
            abort(403, 'Akses ditolak: Konfigurasi ini milik sekolah lain.');
        }

        // Check if referenced by finalized or existing gradebooks
        if ($weight->subjectGradebooks()->exists()) {
            return redirect()->route('reports.weights.index')
                ->with('error', 'Konfigurasi tidak dapat dihapus karena telah digunakan pada rekap nilai gradebook.');
        }

        $weight->delete();

        return redirect()->route('reports.weights.index')
            ->with('success', 'Konfigurasi bobot penilaian berhasil dihapus.');
    }
}
