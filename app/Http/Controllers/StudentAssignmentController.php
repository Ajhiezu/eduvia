<?php

namespace App\Http\Controllers;

use App\Http\Requests\Learning\SubmitAssignmentRequest;
use App\Models\Assignment;
use App\Models\AssignmentAttachment;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentSubmissionAttachment;
use App\Models\ClassroomStudent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentAssignmentController extends Controller
{
    /**
     * Display a listing of published assignments for enrolled student.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $enrolledClassroomIds = ClassroomStudent::where('student_id', $user->id)
            ->where('enrollment_status', 'active')
            ->pluck('classroom_id');

        $query = Assignment::published()
            ->whereIn('classroom_id', $enrolledClassroomIds)
            ->with([
                'subject',
                'teacher',
                'classroom',
                'submissions' => fn ($q) => $q->where('student_id', $user->id),
            ])
            ->latest('due_date');

        if ($request->filled('status_filter')) {
            $filter = $request->status_filter;
            if ($filter === 'pending') {
                $query->whereDoesntHave('submissions', fn ($q) => $q->where('student_id', $user->id));
            } elseif ($filter === 'submitted') {
                $query->whereHas('submissions', fn ($q) => $q->where('student_id', $user->id)->whereIn('status', ['submitted', 'late', 'resubmitted']));
            } elseif ($filter === 'graded') {
                $query->whereHas('submissions', fn ($q) => $q->where('student_id', $user->id)->where('status', 'graded'));
            } elseif ($filter === 'revision') {
                $query->whereHas('submissions', fn ($q) => $q->where('student_id', $user->id)->where('status', 'revision_requested'));
            }
        }

        $assignments = $query->paginate(12)->withQueryString();

        // Calculate student's overall assignment statistics
        $allAssignments = Assignment::published()->whereIn('classroom_id', $enrolledClassroomIds)->get();
        $mySubmissions = AssignmentSubmission::whereIn('assignment_id', $allAssignments->pluck('id'))
            ->where('student_id', $user->id)
            ->get()
            ->keyBy('assignment_id');

        $pendingCount = 0;
        $submittedCount = 0;
        $gradedCount = 0;
        $revisionCount = 0;

        foreach ($allAssignments as $asgn) {
            $sub = $mySubmissions->get($asgn->id);
            if (! $sub || $sub->status === 'draft') {
                $pendingCount++;
            } elseif ($sub->status === 'graded') {
                $gradedCount++;
            } elseif ($sub->status === 'revision_requested') {
                $revisionCount++;
            } else {
                $submittedCount++;
            }
        }

        return view('learning.student.assignments.index', compact(
            'assignments',
            'mySubmissions',
            'pendingCount',
            'submittedCount',
            'gradedCount',
            'revisionCount'
        ));
    }

    /**
     * Display the specified assignment and student's submission workspace.
     */
    public function show(Request $request, Assignment $assignment): View
    {
        $user = $request->user();

        if (! $assignment->isPublished()) {
            abort(404, 'Tugas belum diterbitkan atau tidak tersedia.');
        }

        $isEnrolled = ClassroomStudent::where('classroom_id', $assignment->classroom_id)
            ->where('student_id', $user->id)
            ->where('enrollment_status', 'active')
            ->exists();

        if (! $isEnrolled) {
            abort(403, 'Anda tidak terdaftar dalam rombel tugas pembelajaran ini.');
        }

        $assignment->load(['subject', 'teacher', 'classroom', 'attachments']);

        $submission = AssignmentSubmission::with(['attachments', 'histories.grader', 'grader'])
            ->where('assignment_id', $assignment->id)
            ->where('student_id', $user->id)
            ->first();

        return view('learning.student.assignments.show', compact('assignment', 'submission'));
    }

    /**
     * Submit or save draft of student's assignment answer.
     */
    public function submit(SubmitAssignmentRequest $request, Assignment $assignment): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $action = $validated['action'] ?? ($request->input('status') === 'draft' ? 'draft' : 'submit');
        $content = $validated['content'] ?? $request->input('student_notes');
        $files = $request->file('files') ?? $request->file('attachments') ?? [];

        if (! $assignment->isPublished()) {
            abort(404);
        }

        $isLate = $assignment->isPastDue();

        if ($action === 'submit' && $isLate && ! $assignment->allow_late_submission) {
            return back()->withErrors([
                'submission' => 'Batas waktu pengerjaan tugas telah berakhir dan pengumpulan terlambat tidak diizinkan oleh guru pengampu.',
            ]);
        }

        $submission = AssignmentSubmission::firstOrNew([
            'assignment_id' => $assignment->id,
            'student_id' => $user->id,
        ]);

        // Validation for submission content when finalizing
        if ($action === 'submit') {
            $hasContent = ! empty(trim((string) $content));
            $hasNewFiles = ! empty($files);
            $hasExistingFiles = $submission->exists && $submission->attachments()->exists();

            if ($assignment->submission_type === 'text_only' && ! $hasContent) {
                return back()->withErrors(['content' => 'Teks jawaban wajib diisi sebelum mengirimkan tugas.']);
            }

            if ($assignment->submission_type === 'file_only' && ! $hasNewFiles && ! $hasExistingFiles) {
                return back()->withErrors(['files' => 'Berkas dokumen wajib diunggah sebelum mengirimkan tugas.']);
            }

            if ($assignment->submission_type === 'both' && ! $hasContent && ! $hasNewFiles && ! $hasExistingFiles) {
                return back()->withErrors(['content' => 'Silakan isi teks jawaban atau unggah berkas sebelum mengirimkan tugas.']);
            }
        }

        DB::transaction(function () use ($submission, $content, $action, $isLate, $files) {
            if ($submission->exists && $submission->isRevisionRequested()) {
                // Snapshot revision history before new submission
                $submission->snapshotHistory();
                $submission->attempt_number += 1;
            }

            $status = match ($action) {
                'draft' => 'draft',
                'submit' => $submission->isRevisionRequested() ? 'resubmitted' : 'submitted',
            };

            $submission->content = $content ?? $submission->content;
            $submission->status = $status;
            $submission->is_late = $isLate;

            if ($action === 'submit') {
                $submission->submitted_at = now();
                $submission->revision_notes = null; // Clear revision note once submitted
            }

            $submission->save();

            // Handle newly uploaded attachments
            if (! empty($files)) {
                foreach ($files as $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('submissions', 'local');
                        AssignmentSubmissionAttachment::create([
                            'assignment_submission_id' => $submission->id,
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'file_size' => $file->getSize(),
                            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                        ]);
                    }
                }
            }
        });

        $message = $action === 'draft'
            ? 'Draft jawaban tugas berhasil disimpan.'
            : ($isLate ? 'Jawaban tugas berhasil dikirim (Tercatat Terlambat).' : 'Jawaban tugas berhasil dikirim tepat waktu.');

        return redirect()->route('student.assignments.show', $assignment)
            ->with('status', $message);
    }

    /**
     * Download teacher's assignment attachment.
     */
    public function downloadAttachment(Request $request, AssignmentAttachment $attachment): BinaryFileResponse
    {
        $user = $request->user();
        $assignment = $attachment->assignment;

        if (! $assignment->isPublished()) {
            abort(404);
        }

        $isEnrolled = ClassroomStudent::where('classroom_id', $assignment->classroom_id)
            ->where('student_id', $user->id)
            ->where('enrollment_status', 'active')
            ->exists();

        if (! $isEnrolled) {
            abort(403, 'Akses unduh ditolak.');
        }

        if (! Storage::disk('local')->exists($attachment->file_path)) {
            abort(404, 'Berkas fisik tidak ditemukan di penyimpanan server.');
        }

        return response()->download(
            Storage::disk('local')->path($attachment->file_path),
            $attachment->file_name
        );
    }

    /**
     * Download student's own uploaded attachment.
     */
    public function downloadMySubmissionAttachment(Request $request, AssignmentSubmissionAttachment $attachment): BinaryFileResponse
    {
        $user = $request->user();
        $submission = $attachment->submission;

        if ($submission->student_id !== $user->id) {
            abort(403, 'Akses unduh berkas jawaban ditolak.');
        }

        if (! Storage::disk('local')->exists($attachment->file_path)) {
            abort(404, 'Berkas fisik tidak ditemukan di penyimpanan server.');
        }

        return response()->download(
            Storage::disk('local')->path($attachment->file_path),
            $attachment->file_name
        );
    }
}
