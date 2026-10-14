<?php

namespace App\Http\Controllers;

use App\Http\Requests\Learning\StoreAssignmentRequest;
use App\Http\Requests\Learning\UpdateAssignmentRequest;
use App\Models\Assignment;
use App\Models\AssignmentAttachment;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\TeachingAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AssignmentController extends Controller
{
    /**
     * Display a listing of assignments for teacher / admin.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Assignment::with(['classroom', 'subject', 'teacher', 'submissions'])
            ->withCount('submissions')
            ->latest('id');

        if ($user->isTeacher()) {
            $query->where('teacher_id', $user->id);
            $teachingAssignments = TeachingAssignment::where('teacher_id', $user->id)
                ->where('is_active', true)
                ->with(['classroom', 'subject'])
                ->get();
        } else {
            $teachingAssignments = TeachingAssignment::where('is_active', true)
                ->with(['classroom', 'subject', 'teacher'])
                ->get();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('instructions', 'like', "%{$search}%");
            });
        }

        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('teaching_assignment_id')) {
            $query->where('teaching_assignment_id', $request->teaching_assignment_id);
        }

        $statsQuery = Assignment::query();
        if ($user->isTeacher()) {
            $statsQuery->where('teacher_id', $user->id);
        }

        $totalAssignmentsCount = (clone $statsQuery)->count();
        $activeCount = (clone $statsQuery)->where('status', 'published')->where(function ($q) {
            $q->whereNull('due_date')->orWhere('due_date', '>=', now());
        })->count();
        $pastDueCount = (clone $statsQuery)->where('status', 'published')->where('due_date', '<', now())->count();

        $assignments = $query->paginate(15)->withQueryString();

        $classrooms = Classroom::where('is_active', true)->orderBy('name')->get();

        return view('learning.assignments.index', compact(
            'assignments',
            'teachingAssignments',
            'classrooms',
            'totalAssignmentsCount',
            'activeCount',
            'pastDueCount'
        ));
    }

    /**
     * Show the form for creating a new assignment.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        $assignmentsQuery = TeachingAssignment::where('is_active', true)
            ->with(['classroom', 'subject']);

        if ($user->isTeacher()) {
            $assignmentsQuery->where('teacher_id', $user->id);
        }

        $teachingAssignments = $assignmentsQuery->get();

        return view('learning.assignments.create', compact('teachingAssignments'));
    }

    /**
     * Store a newly created assignment in storage.
     */
    public function store(StoreAssignmentRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $teachingAssignment = TeachingAssignment::findOrFail($validated['teaching_assignment_id']);

        DB::transaction(function () use ($user, $validated, $teachingAssignment, $request) {
            $assignment = Assignment::create([
                'school_id' => $teachingAssignment->school_id,
                'teaching_assignment_id' => $teachingAssignment->id,
                'classroom_id' => $teachingAssignment->classroom_id,
                'subject_id' => $teachingAssignment->subject_id,
                'teacher_id' => $user->isTeacher() ? $user->id : $teachingAssignment->teacher_id,
                'title' => $validated['title'],
                'topic' => $validated['topic'] ?? null,
                'instructions' => $validated['instructions'],
                'submission_type' => $validated['submission_type'],
                'max_file_size_mb' => $validated['max_file_size_mb'],
                'allowed_file_types' => $validated['allowed_file_types'] ?? 'pdf,docx,jpg,jpeg,png,zip',
                'due_date' => $validated['due_date'],
                'max_score' => $validated['max_score'],
                'status' => $validated['status'],
                'published_at' => $validated['status'] === 'published' ? now() : null,
                'allow_late_submission' => $request->boolean('allow_late_submission', true),
                'allow_resubmission' => $request->boolean('allow_resubmission', true),
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('assignments', 'local');
                        AssignmentAttachment::create([
                            'assignment_id' => $assignment->id,
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'file_size' => $file->getSize(),
                            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                        ]);
                    }
                }
            }
        });

        return redirect()->route('assignments.index')
            ->with('status', 'Tugas pembelajaran berhasil dibuat.');
    }

    /**
     * Display the specified assignment with student submissions summary.
     */
    public function show(Request $request, Assignment $assignment): View
    {
        $user = $request->user();

        if ($user->isTeacher() && $assignment->teacher_id !== $user->id) {
            abort(403, 'Anda tidak berwenang melihat tugas ini.');
        }

        $assignment->load(['classroom', 'subject', 'teacher', 'attachments', 'submissions.student']);

        $stats = $assignment->submissionStats();

        // Get all active enrolled students in this classroom
        $enrolledStudents = ClassroomStudent::with(['student.studentProfile'])
            ->where('classroom_id', $assignment->classroom_id)
            ->where('enrollment_status', 'active')
            ->get();

        // Map submissions keyed by student_id
        $submissionsByStudent = $assignment->submissions->keyBy('student_id');

        return view('learning.assignments.show', compact(
            'assignment',
            'stats',
            'enrolledStudents',
            'submissionsByStudent'
        ));
    }

    /**
     * Show the form for editing the specified assignment.
     */
    public function edit(Request $request, Assignment $assignment): View
    {
        $user = $request->user();

        if ($user->isTeacher() && $assignment->teacher_id !== $user->id) {
            abort(403, 'Anda tidak berwenang mengedit tugas ini.');
        }

        $assignment->load(['attachments', 'classroom', 'subject']);

        return view('learning.assignments.edit', compact('assignment'));
    }

    /**
     * Update the specified assignment in storage.
     */
    public function update(UpdateAssignmentRequest $request, Assignment $assignment): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $assignment, $request) {
            $isBecomingPublished = $validated['status'] === 'published' && $assignment->status !== 'published';

            $assignment->update([
                'title' => $validated['title'],
                'topic' => $validated['topic'] ?? null,
                'instructions' => $validated['instructions'],
                'submission_type' => $validated['submission_type'],
                'max_file_size_mb' => $validated['max_file_size_mb'],
                'allowed_file_types' => $validated['allowed_file_types'] ?? $assignment->allowed_file_types,
                'due_date' => $validated['due_date'],
                'max_score' => $validated['max_score'],
                'status' => $validated['status'],
                'published_at' => $isBecomingPublished ? now() : $assignment->published_at,
                'allow_late_submission' => $request->boolean('allow_late_submission', true),
                'allow_resubmission' => $request->boolean('allow_resubmission', true),
            ]);

            // Handle deleted attachments
            if (! empty($validated['delete_attachments'])) {
                $attachmentsToDelete = AssignmentAttachment::where('assignment_id', $assignment->id)
                    ->whereIn('id', $validated['delete_attachments'])
                    ->get();

                foreach ($attachmentsToDelete as $att) {
                    $att->delete();
                }
            }

            // Handle newly uploaded attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('assignments', 'local');
                        AssignmentAttachment::create([
                            'assignment_id' => $assignment->id,
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'file_size' => $file->getSize(),
                            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                        ]);
                    }
                }
            }
        });

        return redirect()->route('assignments.show', $assignment)
            ->with('status', 'Tugas pembelajaran berhasil diperbarui.');
    }

    /**
     * Remove the specified assignment from storage.
     */
    public function destroy(Request $request, Assignment $assignment): RedirectResponse
    {
        $user = $request->user();

        if ($user->isTeacher() && $assignment->teacher_id !== $user->id) {
            abort(403, 'Anda tidak berwenang menghapus tugas ini.');
        }

        $assignment->delete();

        return redirect()->route('assignments.index')
            ->with('status', 'Tugas pembelajaran berhasil dihapus.');
    }

    /**
     * Download assignment attachment.
     */
    public function downloadAttachment(Request $request, AssignmentAttachment $attachment): BinaryFileResponse
    {
        $user = $request->user();
        $assignment = $attachment->assignment;

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
