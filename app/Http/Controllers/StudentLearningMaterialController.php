<?php

namespace App\Http\Controllers;

use App\Models\ClassroomStudent;
use App\Models\LearningMaterial;
use App\Models\LearningMaterialAttachment;
use App\Models\LearningMaterialProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentLearningMaterialController extends Controller
{
    /**
     * Display a listing of learning materials for enrolled student.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $enrolledClassroomIds = ClassroomStudent::where('student_id', $user->id)
            ->where('enrollment_status', 'active')
            ->pluck('classroom_id');

        $query = LearningMaterial::published()
            ->whereIn('classroom_id', $enrolledClassroomIds)
            ->with([
                'subject',
                'teacher',
                'classroom',
                'attachments',
                'progressRecords' => fn ($q) => $q->where('student_id', $user->id),
            ])
            ->latest('id');

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $materials = $query->paginate(12)->withQueryString();

        // Calculate student's overall learning stats
        $allEnrolledMaterials = LearningMaterial::published()
            ->whereIn('classroom_id', $enrolledClassroomIds)
            ->pluck('id');

        $totalMaterialsCount = $allEnrolledMaterials->count();
        $completedCount = LearningMaterialProgress::whereIn('learning_material_id', $allEnrolledMaterials)
            ->where('student_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $totalCount = $totalMaterialsCount;
        $uncompletedCount = max(0, $totalMaterialsCount - $completedCount);
        $overallProgressRate = $totalMaterialsCount > 0
            ? round(($completedCount / $totalMaterialsCount) * 100, 1)
            : 0;
        $progressMap = LearningMaterialProgress::whereIn('learning_material_id', $allEnrolledMaterials)
            ->where('student_id', $user->id)
            ->get()
            ->keyBy('learning_material_id');

        return view('learning.student.materials.index', compact(
            'materials',
            'totalMaterialsCount',
            'totalCount',
            'completedCount',
            'uncompletedCount',
            'progressMap',
            'overallProgressRate'
        ));
    }

    /**
     * Display the specified learning material for student to study.
     */
    public function show(Request $request, LearningMaterial $material): View
    {
        $user = $request->user();

        if (! $material->isPublished()) {
            abort(404, 'Materi pembelajaran belum diterbitkan atau tidak tersedia.');
        }

        $isEnrolled = ClassroomStudent::where('classroom_id', $material->classroom_id)
            ->where('student_id', $user->id)
            ->where('enrollment_status', 'active')
            ->exists();

        if (! $isEnrolled) {
            abort(403, 'Anda tidak terdaftar dalam kelas materi pembelajaran ini.');
        }

        $material->load(['subject', 'teacher', 'classroom', 'attachments']);

        // Update or create progress
        $progress = LearningMaterialProgress::firstOrCreate(
            [
                'learning_material_id' => $material->id,
                'student_id' => $user->id,
            ],
            [
                'status' => 'in_progress',
                'last_accessed_at' => now(),
            ]
        );

        $progress->update(['last_accessed_at' => now()]);

        return view('learning.student.materials.show', compact('material', 'progress'));
    }

    /**
     * Mark material as completed by student.
     */
    public function complete(Request $request, LearningMaterial $material): RedirectResponse
    {
        $user = $request->user();

        if (! $material->isPublished()) {
            abort(404);
        }

        $isEnrolled = ClassroomStudent::where('classroom_id', $material->classroom_id)
            ->where('student_id', $user->id)
            ->where('enrollment_status', 'active')
            ->exists();

        if (! $isEnrolled) {
            abort(403, 'Akses ditolak.');
        }

        $progress = LearningMaterialProgress::firstOrCreate(
            [
                'learning_material_id' => $material->id,
                'student_id' => $user->id,
            ]
        );

        $progress->markAsCompleted();

        return back()->with('status', 'Materi telah berhasil ditandai sebagai selesai dipelajari.');
    }

    /**
     * Download material attachment by enrolled student.
     */
    public function downloadAttachment(Request $request, LearningMaterialAttachment $attachment): BinaryFileResponse
    {
        $user = $request->user();
        $material = $attachment->learningMaterial;

        if (! $material->isPublished()) {
            abort(404);
        }

        $isEnrolled = ClassroomStudent::where('classroom_id', $material->classroom_id)
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
}
