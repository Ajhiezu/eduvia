<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admission\StoreAdmissionPeriodRequest;
use App\Http\Requests\Admission\UpdateAdmissionPeriodRequest;
use App\Models\AcademicYear;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionTrack;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdmissionPeriodController extends Controller
{
    /**
     * Display a listing of admission periods.
     */
    public function index(Request $request): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $periods = AdmissionPeriod::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with(['academicYear', 'tracks'])
            ->withCount(['applicants'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admissions.periods.index', compact('periods'));
    }

    /**
     * Show form for creating a new admission period.
     */
    public function create(): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $academicYears = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admissions.periods.create', compact('academicYears'));
    }

    /**
     * Store a newly created admission period with tracks in storage.
     */
    public function store(StoreAdmissionPeriodRequest $request): RedirectResponse
    {
        $school = School::current();
        $schoolId = $school?->id;

        $period = DB::transaction(function () use ($request, $schoolId) {
            $period = AdmissionPeriod::create([
                'school_id' => $schoolId,
                'academic_year_id' => $request->input('academic_year_id'),
                'name' => $request->input('name'),
                'code' => $request->input('code'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'announcement_date' => $request->input('announcement_date'),
                're_registration_start_date' => $request->input('re_registration_start_date'),
                're_registration_end_date' => $request->input('re_registration_end_date'),
                'quota' => $request->input('quota'),
                'status' => $request->input('status', 'draft'),
                'description' => $request->input('description'),
                'requirements' => $request->input('requirements'),
                'is_active' => true,
            ]);

            // Create tracks
            $tracks = $request->input('tracks', []);
            foreach ($tracks as $trackData) {
                AdmissionTrack::create([
                    'admission_period_id' => $period->id,
                    'name' => $trackData['name'],
                    'code' => strtoupper($trackData['code']),
                    'quota' => $trackData['quota'],
                    'description' => $trackData['description'] ?? null,
                    'is_active' => true,
                ]);
            }

            return $period;
        });

        return redirect()->route('admissions.periods.show', $period)
            ->with('status', "Periode PPDB '{$period->name}' berhasil dibuat.");
    }

    /**
     * Display the specified admission period with tracks and statistics.
     */
    public function show(AdmissionPeriod $period): View
    {
        $period->load(['academicYear', 'tracks' => fn ($q) => $q->withCount('applicants')]);

        $totalApplicants = $period->applicants()->count();
        $verifiedCount = $period->applicants()->where('status', 'verified')->count();
        $acceptedCount = $period->applicants()->where('status', 'accepted')->count();
        $reRegisteredCount = $period->applicants()->whereIn('status', ['re_registered', 'converted'])->count();

        return view('admissions.periods.show', compact(
            'period',
            'totalApplicants',
            'verifiedCount',
            'acceptedCount',
            'reRegisteredCount'
        ));
    }

    /**
     * Show form for editing the admission period.
     */
    public function edit(AdmissionPeriod $period): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $academicYears = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admissions.periods.edit', compact('period', 'academicYears'));
    }

    /**
     * Update the admission period.
     */
    public function update(UpdateAdmissionPeriodRequest $request, AdmissionPeriod $period): RedirectResponse
    {
        $period->update([
            'academic_year_id' => $request->input('academic_year_id'),
            'name' => $request->input('name'),
            'code' => $request->input('code'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'announcement_date' => $request->input('announcement_date'),
            're_registration_start_date' => $request->input('re_registration_start_date'),
            're_registration_end_date' => $request->input('re_registration_end_date'),
            'quota' => $request->input('quota'),
            'status' => $request->input('status'),
            'description' => $request->input('description'),
            'requirements' => $request->input('requirements'),
        ]);

        return redirect()->route('admissions.periods.show', $period)
            ->with('status', 'Konfigurasi periode PPDB berhasil diperbarui.');
    }
}
