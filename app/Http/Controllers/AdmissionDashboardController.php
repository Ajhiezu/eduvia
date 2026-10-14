<?php

namespace App\Http\Controllers;

use App\Models\AdmissionApplicant;
use App\Models\AdmissionPeriod;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdmissionDashboardController extends Controller
{
    /**
     * Display the PPDB / SPMB management analytics dashboard.
     */
    public function index(Request $request): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $periodId = $request->query('period_id');

        $periods = AdmissionPeriod::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->active()
            ->with(['academicYear', 'tracks'])
            ->orderBy('created_at', 'desc')
            ->get();

        $selectedPeriod = $periodId
            ? $periods->firstWhere('id', $periodId)
            : ($periods->firstWhere('status', 'open') ?? $periods->first());

        $applicantsQuery = AdmissionApplicant::when($selectedPeriod, function ($q) use ($selectedPeriod) {
            $q->where('admission_period_id', $selectedPeriod->id);
        });

        $totalApplicants = (clone $applicantsQuery)->count();
        $quota = $selectedPeriod?->quota ?? 0;
        $quotaFulfilledPercent = $quota > 0 ? min(100, round(($totalApplicants / $quota) * 100)) : 0;

        $statusCounts = (clone $applicantsQuery)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $pendingCount = (int) ($statusCounts['pending'] ?? 0);
        $verifiedCount = (int) ($statusCounts['verified'] ?? 0);
        $acceptedCount = (int) ($statusCounts['accepted'] ?? 0);
        $rejectedCount = (int) ($statusCounts['rejected'] ?? 0);
        $reRegisteredCount = (int) ($statusCounts['re_registered'] ?? 0) + (int) ($statusCounts['converted'] ?? 0);

        // Tracks breakdown
        $tracks = $selectedPeriod ? $selectedPeriod->tracks()->withCount('applicants')->get() : collect();

        // Recent applicants
        $recentApplicants = (clone $applicantsQuery)
            ->with(['admissionTrack', 'targetGradeLevel'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return view('admissions.dashboard.index', compact(
            'periods',
            'selectedPeriod',
            'totalApplicants',
            'quota',
            'quotaFulfilledPercent',
            'pendingCount',
            'verifiedCount',
            'acceptedCount',
            'rejectedCount',
            'reRegisteredCount',
            'tracks',
            'recentApplicants'
        ));
    }
}
