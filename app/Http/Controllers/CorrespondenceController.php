<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolLetter;
use App\Models\User;
use App\Services\SchoolOperationsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CorrespondenceController extends Controller
{
    public function index(Request $request): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $type = $request->query('type', 'incoming');
        $category = $request->query('category');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = SchoolLetter::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('reference_number', 'like', "%{$s}%")
                        ->orWhere('subject', 'like', "%{$s}%")
                        ->orWhere('sender_or_recipient', 'like', "%{$s}%");
                });
            });

        $letters = $query->orderBy('letter_date', 'desc')->paginate(15)->withQueryString();

        $incomingCount = SchoolLetter::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->where('type', 'incoming')->count();
        $outgoingCount = SchoolLetter::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->where('type', 'outgoing')->count();
        $actionNeededCount = SchoolLetter::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->where('type', 'incoming')->where('status', 'action_needed')->count();

        $categories = ['Dinas', 'Undangan', 'Edaran', 'Surat Tugas', 'Keterangan', 'Yayasan', 'Lainnya'];

        return view('operations.correspondence.index', compact(
            'letters',
            'type',
            'category',
            'status',
            'search',
            'incomingCount',
            'outgoingCount',
            'actionNeededCount',
            'categories'
        ));
    }

    public function create(Request $request): View
    {
        $type = $request->query('type', 'incoming');
        $categories = ['Dinas', 'Undangan', 'Edaran', 'Surat Tugas', 'Keterangan', 'Yayasan', 'Lainnya'];

        return view('operations.correspondence.create', compact('type', 'categories'));
    }

    public function store(Request $request, SchoolOperationsService $service): RedirectResponse
    {
        $school = School::current();
        abort_if(! $school, 403);

        $validated = $request->validate([
            'type' => ['required', 'in:incoming,outgoing'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'letter_date' => ['required', 'date'],
            'received_or_sent_date' => ['required', 'date'],
            'sender_or_recipient' => ['required', 'string', 'max:200'],
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:draft,sent,received,action_needed,archived'],
            'action_deadline' => ['nullable', 'date'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // max 10MB
            'notes' => ['nullable', 'string'],
        ]);

        $filePath = null;
        $fileOriginalName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filePath = $file->store('private/school_letters', 'local');
            $fileOriginalName = $file->getClientOriginalName();
        }

        // Auto-generate reference number for outgoing if empty
        $refNumber = $validated['reference_number'];
        if (empty($refNumber) && $validated['type'] === 'outgoing') {
            $refNumber = $service->generateLetterReference($school, 'outgoing', $validated['category']);
        } elseif (empty($refNumber)) {
            $refNumber = 'AGENDA/'.date('Ymd').'/'.rand(100, 999);
        }

        $letter = SchoolLetter::create([
            'school_id' => $school->id,
            'type' => $validated['type'],
            'reference_number' => $refNumber,
            'letter_date' => $validated['letter_date'],
            'received_or_sent_date' => $validated['received_or_sent_date'],
            'sender_or_recipient' => $validated['sender_or_recipient'],
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'status' => $validated['status'],
            'action_deadline' => $validated['action_deadline'] ?? null,
            'file_path' => $filePath,
            'file_original_name' => $fileOriginalName,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('operations.correspondence.show', $letter)
            ->with('status', "Surat {$letter->typeLabel()} dengan nomor '{$letter->reference_number}' berhasil diarsipkan.");
    }

    public function show(SchoolLetter $letter): View
    {
        $school = School::current();
        abort_if($letter->school_id !== $school?->id, 403);

        $letter->load('creator');

        return view('operations.correspondence.show', compact('letter'));
    }

    public function edit(SchoolLetter $letter): View
    {
        $school = School::current();
        abort_if($letter->school_id !== $school?->id, 403);

        $categories = ['Dinas', 'Undangan', 'Edaran', 'Surat Tugas', 'Keterangan', 'Yayasan', 'Lainnya'];

        return view('operations.correspondence.edit', compact('letter', 'categories'));
    }

    public function update(Request $request, SchoolLetter $letter): RedirectResponse
    {
        $school = School::current();
        abort_if($letter->school_id !== $school?->id, 403);

        $validated = $request->validate([
            'reference_number' => ['required', 'string', 'max:100'],
            'letter_date' => ['required', 'date'],
            'received_or_sent_date' => ['required', 'date'],
            'sender_or_recipient' => ['required', 'string', 'max:200'],
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:draft,sent,received,action_needed,archived'],
            'action_deadline' => ['nullable', 'date'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $validated['file_path'] = $file->store('private/school_letters', 'local');
            $validated['file_original_name'] = $file->getClientOriginalName();
        }

        $letter->update($validated);

        return redirect()->route('operations.correspondence.show', $letter)
            ->with('status', "Data surat '{$letter->reference_number}' berhasil diperbarui.");
    }

    public function destroy(SchoolLetter $letter): RedirectResponse
    {
        $school = School::current();
        abort_if($letter->school_id !== $school?->id, 403);

        $letter->delete();

        return redirect()->route('operations.correspondence.index')
            ->with('status', 'Arsip surat berhasil dihapus.');
    }

    public function downloadAttachment(SchoolLetter $letter): StreamedResponse|RedirectResponse
    {
        $school = School::current();
        abort_if($letter->school_id !== $school?->id, 403);

        if (! $letter->file_path || ! Storage::disk('local')->exists($letter->file_path)) {
            return redirect()->back()->with('error', 'Berkas lampiran surat tidak ditemukan di penyimpanan server.');
        }

        return Storage::disk('local')->response(
            $letter->file_path,
            $letter->file_original_name ?? 'lampiran_surat.pdf'
        );
    }

    // =========================================================================
    // OFFICIAL TEMPLATES: SURAT KETERANGAN SISWA AKTIF (PDF)
    // =========================================================================

    public function createCertificate(): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $students = User::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('role', 'student')
            ->where('is_active', true)
            ->with('studentProfile')
            ->orderBy('name')
            ->get();

        return view('operations.correspondence.certificate-create', compact('students'));
    }

    public function generateCertificatePdf(Request $request, SchoolOperationsService $service): Response
    {
        $school = School::current();
        abort_if(! $school, 403);

        $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'purpose' => ['required', 'string', 'max:255'],
        ]);

        $student = User::where('school_id', $school->id)->with('studentProfile')->findOrFail($request->input('student_id'));
        $purpose = $request->input('purpose');
        $letterNumber = $service->generateLetterReference($school, 'outgoing', 'Keterangan');

        // Automatically archive outgoing letter record
        SchoolLetter::create([
            'school_id' => $school->id,
            'type' => 'outgoing',
            'reference_number' => $letterNumber,
            'letter_date' => Carbon::today(),
            'received_or_sent_date' => Carbon::today(),
            'sender_or_recipient' => $student->name,
            'subject' => "Surat Keterangan Siswa Aktif: {$student->name} ({$purpose})",
            'category' => 'Keterangan',
            'status' => 'sent',
            'notes' => "Diterbitkan untuk keperluan: {$purpose}",
            'created_by' => $request->user()->id,
        ]);

        $pdf = Pdf::loadView('operations.correspondence.pdf.student-active-certificate', compact(
            'school',
            'student',
            'purpose',
            'letterNumber'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream("Surat_Keterangan_Siswa_Aktif_{$student->name}.pdf");
    }
}
