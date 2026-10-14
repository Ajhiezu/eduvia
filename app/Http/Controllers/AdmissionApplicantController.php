<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admission\ConvertApplicantRequest;
use App\Http\Requests\Admission\UpdateApplicantStatusRequest;
use App\Http\Requests\Admission\VerifyApplicantDocumentRequest;
use App\Models\AdmissionApplicant;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionTrack;
use App\Models\ApplicantDocument;
use App\Models\Classroom;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdmissionApplicantController extends Controller
{
    /**
     * Display a listing of admission applicants with multi-parameter filtering.
     */
    public function index(Request $request): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $periodId = $request->query('period_id');
        $trackId = $request->query('track_id');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = AdmissionApplicant::query()
            ->with(['admissionPeriod', 'admissionTrack', 'targetGradeLevel', 'documents'])
            ->when($schoolId, function ($q) use ($schoolId) {
                $q->whereHas('admissionPeriod', fn ($p) => $p->where('school_id', $schoolId));
            })
            ->when($periodId, fn ($q) => $q->where('admission_period_id', $periodId))
            ->when($trackId, fn ($q) => $q->where('admission_track_id', $trackId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('full_name', 'like', "%{$s}%")
                        ->orWhere('registration_number', 'like', "%{$s}%")
                        ->orWhere('nisn', 'like', "%{$s}%")
                        ->orWhere('phone', 'like', "%{$s}%");
                });
            });

        // Metric strip counts
        $baseCountQuery = AdmissionApplicant::when($schoolId, function ($q) use ($schoolId) {
            $q->whereHas('admissionPeriod', fn ($p) => $p->where('school_id', $schoolId));
        });

        $totalApplicantsCount = (clone $baseCountQuery)->count();
        $totalVerifiedCount = (clone $baseCountQuery)->where('status', 'verified')->count();
        $totalAcceptedCount = (clone $baseCountQuery)->where('status', 'accepted')->count();
        $totalReRegisteredCount = (clone $baseCountQuery)->whereIn('status', ['re_registered', 'converted'])->count();

        $applicants = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $periods = AdmissionPeriod::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->active()->get();
        $tracks = AdmissionTrack::when($periodId, fn ($q) => $q->where('admission_period_id', $periodId))->get();

        return view('admissions.applicants.index', compact(
            'applicants',
            'periods',
            'tracks',
            'periodId',
            'trackId',
            'status',
            'search',
            'totalApplicantsCount',
            'totalVerifiedCount',
            'totalAcceptedCount',
            'totalReRegisteredCount'
        ));
    }

    /**
     * Display detailed dossier of a specific applicant.
     */
    public function show(AdmissionApplicant $applicant): View
    {
        $applicant->load([
            'admissionPeriod.academicYear',
            'admissionTrack',
            'targetGradeLevel',
            'documents.verifier',
            'convertedUser',
        ]);

        $school = School::current();
        $schoolId = $school?->id;

        $targetAcademicYearId = $applicant->admissionPeriod?->academic_year_id;

        $classrooms = Classroom::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($targetAcademicYearId, fn ($q) => $q->where('academic_year_id', $targetAcademicYearId))
            ->active()
            ->orderBy('name')
            ->get();

        return view('admissions.applicants.show', compact('applicant', 'classrooms'));
    }

    /**
     * Verify or reject an applicant requirement document.
     */
    public function verifyDocument(
        VerifyApplicantDocumentRequest $request,
        AdmissionApplicant $applicant,
        ApplicantDocument $document
    ): RedirectResponse {
        $document->update([
            'status' => $request->input('status'),
            'review_notes' => $request->input('review_notes'),
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        // Auto-update applicant status to 'revision_needed' if any doc is invalid
        if ($request->input('status') === 'invalid' && $applicant->status !== 'converted') {
            $applicant->update(['status' => 'revision_needed']);
        }

        return redirect()->route('admissions.applicants.show', $applicant)
            ->with('status', "Status berkas '{$document->documentTypeLabel()}' berhasil diperbarui.");
    }

    /**
     * Update applicant selection status (verified, accepted, rejected).
     */
    public function updateStatus(
        UpdateApplicantStatusRequest $request,
        AdmissionApplicant $applicant
    ): RedirectResponse {
        $applicant->update([
            'status' => $request->input('status'),
            'selection_score' => $request->input('selection_score'),
            'selection_notes' => $request->input('selection_notes'),
            'rejection_reason' => $request->input('status') === 'rejected' ? $request->input('rejection_reason') : null,
        ]);

        return redirect()->route('admissions.applicants.show', $applicant)
            ->with('status', "Status kelulusan calon siswa berhasil diubah menjadi '{$applicant->statusLabel()}'.");
    }

    /**
     * Record applicant re-registration (daftar ulang).
     */
    public function reRegister(Request $request, AdmissionApplicant $applicant): RedirectResponse
    {
        if (! in_array($applicant->status, ['accepted', 're_registered'], true)) {
            return redirect()->back()
                ->with('error', 'Hanya calon siswa yang dinyatakan diterima yang dapat melakukan daftar ulang.');
        }

        $applicant->update([
            'status' => 're_registered',
            're_registered_at' => now(),
        ]);

        return redirect()->route('admissions.applicants.show', $applicant)
            ->with('status', "Calon siswa '{$applicant->full_name}' telah berhasil menyelesaikan daftar ulang.");
    }

    /**
     * Convert accepted & re-registered applicant into active student.
     */
    public function convert(ConvertApplicantRequest $request, AdmissionApplicant $applicant): RedirectResponse
    {
        $classroomId = $request->input('classroom_id');

        $studentUser = $applicant->convertToActiveStudent($classroomId, $request->user());

        return redirect()->route('students.show', $studentUser)
            ->with('status', "Calon siswa '{$applicant->full_name}' berhasil dikonversi menjadi peserta didik aktif.");
    }

    /**
     * Download or stream private applicant document.
     */
    public function downloadDocument(ApplicantDocument $document): StreamedResponse|RedirectResponse
    {
        if (! Storage::disk('local')->exists($document->file_path)) {
            return redirect()->back()->with('error', 'File berkas tidak ditemukan di penyimpanan server.');
        }

        return Storage::disk('local')->response(
            $document->file_path,
            $document->original_name,
            ['Content-Type' => $document->mime_type]
        );
    }
}
