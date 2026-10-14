<?php

namespace App\Http\Controllers;

use App\Http\Requests\Learning\GradeSubmissionRequest;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentSubmissionAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AssignmentGradingController extends Controller
{
    /**
     * Show a student submission for review and grading by teacher.
     */
    public function showSubmission(Request $request, Assignment $assignment, AssignmentSubmission $submission): View
    {
        $user = $request->user();

        if ($user->isTeacher() && $assignment->teacher_id !== $user->id) {
            abort(403, 'Anda tidak berwenang memeriksa tugas ini.');
        }

        if ($submission->assignment_id !== $assignment->id) {
            abort(404, 'Data pengumpulan tidak sesuai dengan tugas yang dipilih.');
        }

        $submission->load(['student.studentProfile', 'attachments', 'histories.grader', 'grader']);

        return view('learning.assignments.grade', compact('assignment', 'submission'));
    }

    /**
     * Save grade and feedback for a student submission.
     */
    public function grade(GradeSubmissionRequest $request, Assignment $assignment, AssignmentSubmission $submission): RedirectResponse
    {
        if ($submission->assignment_id !== $assignment->id) {
            abort(404, 'Data pengumpulan tidak sesuai.');
        }

        $validated = $request->validated();
        $user = $request->user();

        // Snapshot current state to history before saving new grade
        $submission->snapshotHistory();

        $isPublished = $request->boolean('is_grade_published', false) || $request->boolean('publish_grade', false);
        $feedback = $validated['feedback'] ?? $request->input('teacher_feedback');

        $submission->update([
            'score' => $validated['score'],
            'feedback' => $feedback,
            'status' => 'graded',
            'graded_at' => now(),
            'graded_by' => $user->id,
            'is_grade_published' => $isPublished,
            'grade_published_at' => $isPublished ? now() : null,
        ]);

        return redirect()->route('assignments.submissions.show', [$assignment, $submission])
            ->with('status', 'Penilaian dan umpan balik berhasil disimpan.');
    }

    /**
     * Request revision from student with guidance notes.
     */
    public function requestRevision(Request $request, Assignment $assignment, AssignmentSubmission $submission): RedirectResponse
    {
        $user = $request->user();

        if ($user->isTeacher() && $assignment->teacher_id !== $user->id) {
            abort(403, 'Anda tidak berwenang meminta revisi.');
        }

        $notes = $request->input('revision_notes') ?? $request->input('teacher_feedback');

        if (empty($notes)) {
            return back()->withErrors(['revision_notes' => 'Catatan arahan revisi wajib diisi untuk membimbing siswa.']);
        }

        $submission->update([
            'status' => 'revision_requested',
            'revision_requested_at' => now(),
            'revision_notes' => $notes,
            'feedback' => $notes,
        ]);

        return redirect()->route('assignments.submissions.show', [$assignment, $submission])
            ->with('status', 'Permintaan revisi berhasil dikirimkan kepada siswa.');
    }

    /**
     * Publish grade to student.
     */
    public function publishGrade(Request $request, Assignment $assignment, AssignmentSubmission $submission): RedirectResponse
    {
        $user = $request->user();

        if ($user->isTeacher() && $assignment->teacher_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($submission->score === null) {
            return back()->withErrors(['score' => 'Tugas belum dinilai, tidak dapat dipublikasikan.']);
        }

        $submission->update([
            'is_grade_published' => true,
            'grade_published_at' => now(),
        ]);

        return redirect()->route('assignments.submissions.show', [$assignment, $submission])
            ->with('status', 'Nilai tugas telah berhasil dipublikasikan kepada siswa.');
    }

    /**
     * Download student submission attachment by teacher.
     */
    public function downloadSubmissionAttachment(Request $request, AssignmentSubmissionAttachment $attachment): BinaryFileResponse
    {
        $user = $request->user();
        $submission = $attachment->submission;
        $assignment = $submission->assignment;

        if ($user->isTeacher() && $assignment->teacher_id !== $user->id) {
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
}
