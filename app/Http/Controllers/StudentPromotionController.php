<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\BatchPromotionRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentPromotionController extends Controller
{
    /**
     * Display the batch promotion and graduation screen.
     */
    public function index(Request $request): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $sourceClassroomId = $request->input('source_classroom_id');
        $selectedClassroom = null;
        $students = collect();

        $classrooms = Classroom::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->active()
            ->with(['academicYear', 'gradeLevel'])
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get();

        if ($sourceClassroomId) {
            $selectedClassroom = Classroom::with(['activeStudents.student.studentProfile', 'academicYear', 'gradeLevel'])
                ->findOrFail($sourceClassroomId);

            $students = $selectedClassroom->activeStudents->map(fn ($enrollment) => $enrollment->student);
        }

        return view('students.promotion.index', compact(
            'classrooms',
            'academicYears',
            'sourceClassroomId',
            'selectedClassroom',
            'students'
        ));
    }

    /**
     * Process batch promotion or graduation in atomic transaction.
     */
    public function process(BatchPromotionRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $action = $request->input('action');
        $sourceClassroom = Classroom::findOrFail($request->input('source_classroom_id'));
        $studentIds = $request->input('student_ids', []);
        $effectiveDate = $request->input('effective_date');
        $notes = $request->input('notes') ?? 'Proses massal akhir tahun ajaran';

        DB::transaction(function () use ($action, $sourceClassroom, $studentIds, $effectiveDate, $notes, $request, $actor) {
            $students = User::whereIn('id', $studentIds)->with('studentProfile')->get();

            if ($action === 'promote') {
                $targetClassroom = Classroom::findOrFail($request->input('target_classroom_id'));

                foreach ($students as $student) {
                    $oldEnrollment = $sourceClassroom->activeStudents()
                        ->where('student_id', $student->id)
                        ->first();

                    if ($oldEnrollment) {
                        $oldEnrollment->update([
                            'enrollment_status' => 'completed',
                            'left_at' => $effectiveDate,
                            'notes' => trim(($oldEnrollment->notes ?? '')."\n[Kenaikan kelas]: Dipromosikan ke {$targetClassroom->name}. {$notes}"),
                        ]);
                    }

                    ClassroomStudent::create([
                        'classroom_id' => $targetClassroom->id,
                        'student_id' => $student->id,
                        'academic_year_id' => $targetClassroom->academic_year_id,
                        'enrollment_status' => 'active',
                        'joined_at' => $effectiveDate,
                        'notes' => "Kenaikan kelas dari rombel {$sourceClassroom->name}. {$notes}",
                    ]);

                    $profile = $student->studentProfile;
                    if ($profile) {
                        $profile->changeStatus(
                            'active',
                            $effectiveDate,
                            "Promosi kelas {$sourceClassroom->name} ke {$targetClassroom->name}. {$notes}",
                            $actor
                        );
                    }
                }
            } elseif ($action === 'graduate') {
                foreach ($students as $student) {
                    $sourceClassroom->removeStudent(
                        $student,
                        'completed',
                        "Kelulusan akhir jenjang. {$notes}"
                    );

                    $profile = $student->studentProfile;
                    if ($profile) {
                        $profile->changeStatus(
                            'graduated',
                            $effectiveDate,
                            "Kelulusan rombel {$sourceClassroom->name}. {$notes}",
                            $actor
                        );
                    }
                }
            }
        });

        $count = count($studentIds);
        $actionLabel = $action === 'promote' ? 'Kenaikan kelas' : 'Kelulusan';

        return redirect()->route('students.promotion.index', ['source_classroom_id' => $sourceClassroom->id])
            ->with('status', "Berhasil memproses {$actionLabel} untuk {$count} peserta didik.");
    }
}
