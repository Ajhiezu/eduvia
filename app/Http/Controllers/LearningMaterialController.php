<?php

namespace App\Http\Controllers;

use App\Http\Requests\Learning\StoreLearningMaterialRequest;
use App\Http\Requests\Learning\UpdateLearningMaterialRequest;
use App\Models\Classroom;
use App\Models\LearningMaterial;
use App\Models\LearningMaterialAttachment;
use App\Models\TeachingAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LearningMaterialController extends Controller
{
    /**
     * Display a listing of learning materials for teacher / admin.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = LearningMaterial::with(['classroom', 'subject', 'teacher', 'attachments'])
            ->withCount('attachments')
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
                    ->orWhere('topic', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
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

        $statsQuery = LearningMaterial::query();
        if ($user->isTeacher()) {
            $statsQuery->where('teacher_id', $user->id);
        }

        $totalMaterialsCount = (clone $statsQuery)->count();
        $publishedCount = (clone $statsQuery)->where('status', 'published')->count();
        $draftCount = (clone $statsQuery)->whereIn('status', ['draft', 'archived'])->count();

        $materials = $query->paginate(15)->withQueryString();

        $classrooms = Classroom::where('is_active', true)->orderBy('name')->get();

        return view('learning.materials.index', compact(
            'materials',
            'teachingAssignments',
            'classrooms',
            'totalMaterialsCount',
            'publishedCount',
            'draftCount'
        ));
    }

    /**
     * Show the form for creating a new learning material.
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

        return view('learning.materials.create', compact('teachingAssignments'));
    }

    /**
     * Store a newly created learning material in storage.
     */
    public function store(StoreLearningMaterialRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $assignment = TeachingAssignment::findOrFail($validated['teaching_assignment_id']);

        DB::transaction(function () use ($user, $validated, $assignment, $request) {
            $material = LearningMaterial::create([
                'school_id' => $assignment->school_id,
                'teaching_assignment_id' => $assignment->id,
                'classroom_id' => $assignment->classroom_id,
                'subject_id' => $assignment->subject_id,
                'teacher_id' => $user->isTeacher() ? $user->id : $assignment->teacher_id,
                'title' => $validated['title'],
                'topic' => $validated['topic'] ?? null,
                'description' => $validated['description'] ?? null,
                'content' => $validated['content'] ?? null,
                'video_url' => $validated['video_url'] ?? null,
                'status' => $validated['status'],
                'published_at' => $validated['status'] === 'published' ? now() : null,
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('materials', 'local');
                        LearningMaterialAttachment::create([
                            'learning_material_id' => $material->id,
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'file_size' => $file->getSize(),
                            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                        ]);
                    }
                }
            }
        });

        return redirect()->route('learning-materials.index')
            ->with('status', 'Materi pembelajaran berhasil dibuat.');
    }

    /**
     * Display the specified learning material.
     */
    public function show(Request $request, LearningMaterial $material): View
    {
        $user = $request->user();

        if ($user->isTeacher() && $material->teacher_id !== $user->id) {
            abort(403, 'Anda tidak berwenang melihat materi dari guru lain.');
        }

        $material->load(['classroom.activeStudents', 'subject', 'teacher', 'attachments', 'progressRecords.student']);

        // Calculate class progress
        $totalStudents = $material->classroom?->activeStudents()->count() ?? 0;
        $completedCount = $material->progressRecords()->where('status', 'completed')->count();
        $completionRate = $totalStudents > 0 ? round(($completedCount / $totalStudents) * 100, 1) : 0;

        return view('learning.materials.show', compact('material', 'totalStudents', 'completedCount', 'completionRate'));
    }

    /**
     * Show the form for editing the specified learning material.
     */
    public function edit(Request $request, LearningMaterial $material): View
    {
        $user = $request->user();

        if ($user->isTeacher() && $material->teacher_id !== $user->id) {
            abort(403, 'Anda tidak berwenang mengedit materi ini.');
        }

        $material->load(['attachments', 'classroom', 'subject']);

        return view('learning.materials.edit', compact('material'));
    }

    /**
     * Update the specified learning material in storage.
     */
    public function update(UpdateLearningMaterialRequest $request, LearningMaterial $material): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $material, $request) {
            $isBecomingPublished = $validated['status'] === 'published' && $material->status !== 'published';

            $material->update([
                'title' => $validated['title'],
                'topic' => $validated['topic'] ?? null,
                'description' => $validated['description'] ?? null,
                'content' => $validated['content'] ?? null,
                'video_url' => $validated['video_url'] ?? null,
                'status' => $validated['status'],
                'published_at' => $isBecomingPublished ? now() : $material->published_at,
            ]);

            // Handle deleted attachments
            if (! empty($validated['delete_attachments'])) {
                $attachmentsToDelete = LearningMaterialAttachment::where('learning_material_id', $material->id)
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
                        $path = $file->store('materials', 'local');
                        LearningMaterialAttachment::create([
                            'learning_material_id' => $material->id,
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'file_size' => $file->getSize(),
                            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                        ]);
                    }
                }
            }
        });

        return redirect()->route('learning-materials.show', $material)
            ->with('status', 'Materi pembelajaran berhasil diperbarui.');
    }

    /**
     * Remove the specified learning material from storage.
     */
    public function destroy(Request $request, LearningMaterial $material): RedirectResponse
    {
        $user = $request->user();

        if ($user->isTeacher() && $material->teacher_id !== $user->id) {
            abort(403, 'Anda tidak berwenang menghapus materi ini.');
        }

        $material->delete();

        return redirect()->route('learning-materials.index')
            ->with('status', 'Materi pembelajaran berhasil dihapus.');
    }

    /**
     * Archive the specified learning material.
     */
    public function archive(Request $request, LearningMaterial $material): RedirectResponse
    {
        $user = $request->user();

        if ($user->isTeacher() && $material->teacher_id !== $user->id) {
            abort(403, 'Anda tidak berwenang mengarsipkan materi ini.');
        }

        $material->update(['status' => 'archived']);

        return back()->with('status', 'Materi pembelajaran berhasil diarsipkan.');
    }

    /**
     * Download an attachment with authorization check.
     */
    public function downloadAttachment(Request $request, LearningMaterialAttachment $attachment): BinaryFileResponse
    {
        $user = $request->user();
        $material = $attachment->learningMaterial;

        if ($user->isTeacher() && $material->teacher_id !== $user->id) {
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
