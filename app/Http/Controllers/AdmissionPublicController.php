<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admission\PublicApplicantRegistrationRequest;
use App\Models\AdmissionApplicant;
use App\Models\AdmissionPeriod;
use App\Models\ApplicantDocument;
use App\Models\GradeLevel;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdmissionPublicController extends Controller
{
    /**
     * Display public admissions home page with open periods.
     */
    public function index(): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $openPeriods = AdmissionPeriod::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->active()
            ->with(['academicYear', 'tracks'])
            ->withCount('applicants')
            ->orderBy('start_date', 'desc')
            ->get();

        return view('admissions.public.index', compact('openPeriods', 'school'));
    }

    /**
     * Display registration form for a specific open admission period.
     */
    public function showRegister(AdmissionPeriod $period): View|RedirectResponse
    {
        if (! $period->isOpen()) {
            return redirect()->route('ppdb.index')
                ->with('error', "Pendaftaran untuk periode '{$period->name}' saat ini sedang ditutup atau belum dibuka.");
        }

        $period->load(['tracks' => fn ($q) => $q->where('is_active', true), 'academicYear']);
        $gradeLevels = GradeLevel::active()->orderBy('order')->get();

        return view('admissions.public.register', compact('period', 'gradeLevels'));
    }

    /**
     * Submit public registration form and upload requirement documents.
     */
    public function submitRegister(PublicApplicantRegistrationRequest $request, AdmissionPeriod $period): RedirectResponse
    {
        if (! $period->isOpen()) {
            return redirect()->route('ppdb.index')
                ->with('error', 'Pendaftaran telah ditutup. Berkas tidak dapat diproses.');
        }

        $applicant = DB::transaction(function () use ($request, $period) {
            $credentials = AdmissionApplicant::generateCredentials($period);

            $applicant = AdmissionApplicant::create([
                'admission_period_id' => $period->id,
                'admission_track_id' => $request->input('admission_track_id'),
                'target_grade_level_id' => $request->input('target_grade_level_id'),
                'registration_number' => $credentials['registration_number'],
                'tracking_code' => $credentials['tracking_code'],
                'full_name' => $request->input('full_name'),
                'nisn' => $request->input('nisn'),
                'nik' => $request->input('nik'),
                'gender' => $request->input('gender'),
                'birth_place' => $request->input('birth_place'),
                'birth_date' => $request->input('birth_date'),
                'religion' => $request->input('religion'),
                'address' => $request->input('address'),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'previous_school' => $request->input('previous_school'),
                'parent_name' => $request->input('parent_name'),
                'parent_phone' => $request->input('parent_phone'),
                'parent_relationship' => $request->input('parent_relationship'),
                'status' => 'pending',
            ]);

            // Handle file uploads to private storage
            $documentMappings = [
                'document_kk' => 'kartu_keluarga',
                'document_akta' => 'akta_kelahiran',
                'document_ijazah' => 'ijazah_skhun',
                'document_rapor' => 'rapor',
                'document_sertifikat' => 'sertifikat',
                'document_foto' => 'foto',
            ];

            foreach ($documentMappings as $inputKey => $docType) {
                if ($request->hasFile($inputKey)) {
                    $file = $request->file($inputKey);
                    $path = $file->store("private/admission_documents/{$applicant->id}", 'local');

                    ApplicantDocument::create([
                        'admission_applicant_id' => $applicant->id,
                        'document_type' => $docType,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getClientMimeType(),
                        'status' => 'pending',
                    ]);
                }
            }

            return $applicant;
        });

        return redirect()->route('ppdb.success', [
            'registration_number' => $applicant->registration_number,
            'tracking_code' => $applicant->tracking_code,
        ]);
    }

    /**
     * Registration success screen displaying credentials.
     */
    public function registrationSuccess(Request $request): View|RedirectResponse
    {
        $regNumber = $request->query('registration_number');
        $trackingCode = $request->query('tracking_code');

        if (! $regNumber || ! $trackingCode) {
            return redirect()->route('ppdb.index');
        }

        $applicant = AdmissionApplicant::where('registration_number', $regNumber)
            ->where('tracking_code', $trackingCode)
            ->with(['admissionPeriod', 'admissionTrack'])
            ->firstOrFail();

        return view('admissions.public.success', compact('applicant'));
    }

    /**
     * Show status check lookup form.
     */
    public function showCheckStatus(): View
    {
        return view('admissions.public.check-status');
    }

    /**
     * Process status check with dual credentials (registration number + tracking code).
     */
    public function processCheckStatus(Request $request): View|RedirectResponse
    {
        $request->validate([
            'registration_number' => ['required', 'string'],
            'tracking_code' => ['required', 'string'],
        ], [
            'registration_number.required' => 'Nomor pendaftaran wajib diisi.',
            'tracking_code.required' => 'Kode tracking rahasia wajib diisi.',
        ]);

        $applicant = AdmissionApplicant::where('registration_number', trim($request->input('registration_number')))
            ->where('tracking_code', trim($request->input('tracking_code')))
            ->with(['admissionPeriod', 'admissionTrack', 'documents'])
            ->first();

        if (! $applicant) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['registration_number' => 'Data pendaftaran tidak ditemukan. Pastikan Nomor Pendaftaran dan Kode Tracking sesuai dengan bukti pendaftaran Anda.']);
        }

        return view('admissions.public.status-result', compact('applicant'));
    }
}
