<?php

namespace App\Http\Controllers;

use App\Models\AdmissionApplicant;
use App\Models\ConsumableItem;
use App\Models\FacilityReservation;
use App\Models\LibraryBook;
use App\Models\LibraryLoan;
use App\Models\School;
use App\Models\SchoolAsset;
use App\Models\SchoolLetter;
use App\Models\SchoolRoom;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationsDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        // 1. PPDB / SPMB metrics
        $applicantsQuery = AdmissionApplicant::when($schoolId, function ($q) use ($schoolId) {
            $q->whereHas('admissionPeriod', fn ($p) => $p->where('school_id', $schoolId));
        });
        $ppdbTotal = (clone $applicantsQuery)->count();
        $ppdbPending = (clone $applicantsQuery)->where('status', 'pending')->count();
        $ppdbAccepted = (clone $applicantsQuery)->where('status', 'accepted')->count();
        $ppdbEnrolled = (clone $applicantsQuery)->whereIn('status', ['re_registered', 'converted'])->count();

        // 2. Library metrics
        $booksCount = LibraryBook::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->count();
        $activeLoansCount = LibraryLoan::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', 'borrowed')
            ->count();
        $overdueLoansCount = LibraryLoan::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', 'borrowed')
            ->where('due_date', '<', Carbon::today())
            ->count();

        // 3. Asset & Inventory metrics
        $assetsCount = SchoolAsset::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->count();
        $damagedAssetsCount = SchoolAsset::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->whereIn('condition', ['minor_damage', 'heavy_damage'])
            ->count();
        $lowStockConsumables = ConsumableItem::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->count();

        // 4. Facility & Lab metrics
        $roomsCount = SchoolRoom::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->count();
        $pendingReservations = FacilityReservation::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', 'pending')
            ->count();
        $todayReservations = FacilityReservation::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('reservation_date', Carbon::today())
            ->where('status', 'approved')
            ->with(['room', 'user'])
            ->get();

        // 5. Correspondence metrics
        $incomingLettersCount = SchoolLetter::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('type', 'incoming')
            ->count();
        $actionNeededLetters = SchoolLetter::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('type', 'incoming')
            ->where('status', 'action_needed')
            ->count();
        $outgoingLettersMonth = SchoolLetter::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('type', 'outgoing')
            ->whereMonth('letter_date', Carbon::today()->month)
            ->whereYear('letter_date', Carbon::today()->year)
            ->count();

        // Recent operational activities / loans / letters
        $recentLoans = LibraryLoan::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with(['copy.book', 'borrower'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentLetters = SchoolLetter::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderBy('letter_date', 'desc')
            ->limit(5)
            ->get();

        return view('operations.dashboard', compact(
            'ppdbTotal',
            'ppdbPending',
            'ppdbAccepted',
            'ppdbEnrolled',
            'booksCount',
            'activeLoansCount',
            'overdueLoansCount',
            'assetsCount',
            'damagedAssetsCount',
            'lowStockConsumables',
            'roomsCount',
            'pendingReservations',
            'todayReservations',
            'incomingLettersCount',
            'actionNeededLetters',
            'outgoingLettersMonth',
            'recentLoans',
            'recentLetters'
        ));
    }
}
