<?php

namespace App\Http\Controllers;

use App\Http\Requests\Staff\StoreTeachingAssignmentRequest;
use App\Models\School;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class StaffTeachingAssignmentController extends Controller
{
    /**
     * Assign a teacher to a subject and classroom.
     */
    public function store(StoreTeachingAssignmentRequest $request, User $staff): RedirectResponse
    {
        $school = School::current();
        $schoolId = $school?->id;

        $validated = $request->validated();

        // Check duplicate assignment
        $existing = TeachingAssignment::where('teacher_id', $staff->id)
            ->where('classroom_id', $validated['classroom_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->first();

        if ($existing) {
            return back()->withErrors([
                'error' => 'Penugasan mengajar untuk mata pelajaran dan rombongan belajar ini sudah ada pada tahun ajaran yang dipilih.',
            ]);
        }

        TeachingAssignment::create([
            'school_id' => $schoolId,
            'academic_year_id' => $validated['academic_year_id'],
            'semester_id' => $validated['semester_id'] ?? null,
            'classroom_id' => $validated['classroom_id'],
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $staff->id,
            'hours_per_week' => $validated['hours_per_week'],
            'is_active' => true,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Penugasan mengajar baru untuk {$staff->name} berhasil ditambahkan.");
    }

    /**
     * Remove or deactivate a teaching assignment.
     */
    public function destroy(TeachingAssignment $assignment): RedirectResponse
    {
        $teacherName = $assignment->teacher?->name;

        // Check if there are learning materials or attendance records associated
        if ($assignment->attendanceSessions()->exists() || $assignment->learningMaterials()->exists() || $assignment->assignments()->exists()) {
            // Soft-deactivate instead of hard delete to preserve historical records
            $assignment->update(['is_active' => false]);

            return back()->with('success', 'Penugasan mengajar telah dinonaktifkan (arsip pembelajaran tetap dipertahankan).');
        }

        $assignment->delete();

        return back()->with('success', 'Penugasan mengajar berhasil dihapus.');
    }
}
