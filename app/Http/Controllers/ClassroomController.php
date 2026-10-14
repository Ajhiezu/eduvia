<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Classroom\StoreClassroomRequest;
use App\Http\Requests\Classroom\UpdateClassroomRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    /**
     * Display a listing of classrooms with filters, search, and pagination.
     */
    public function index(Request $request): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $academicYearId = $request->input('academic_year_id');
        $gradeLevelId = $request->input('grade_level_id');
        $status = $request->input('status');
        $search = $request->input('search');

        $query = Classroom::query()
            ->with(['academicYear', 'gradeLevel', 'homeroomTeacher'])
            ->withCount(['activeStudents'])
            ->when($schoolId, fn ($q) => $q->forSchool($schoolId))
            ->when($academicYearId, fn ($q) => $q->forAcademicYear($academicYearId))
            ->when($gradeLevelId, fn ($q) => $q->forGradeLevel($gradeLevelId))
            ->when($status !== null && $status !== '', function ($q) use ($status) {
                $q->where('is_active', (bool) $status);
            })
            ->search($search)
            ->latest('id');

        $classrooms = $query->paginate(15)->withQueryString();

        $academicYears = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get();

        $gradeLevels = GradeLevel::active()->orderBy('order')->get();

        return view('classrooms.index', compact(
            'classrooms',
            'academicYears',
            'gradeLevels',
            'academicYearId',
            'gradeLevelId',
            'status',
            'search'
        ));
    }

    /**
     * Show the form for creating a new classroom.
     */
    public function create(): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $academicYears = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get();

        $gradeLevels = GradeLevel::active()->orderBy('order')->get();

        $teachers = User::where('role', UserRole::TEACHER->value)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('classrooms.create', compact('academicYears', 'gradeLevels', 'teachers'));
    }

    /**
     * Store a newly created classroom in storage.
     */
    public function store(StoreClassroomRequest $request): RedirectResponse
    {
        $school = School::current();
        $validated = $request->validated();
        $validated['school_id'] = $school?->id;
        $validated['is_active'] = $request->boolean('is_active', true);

        $classroom = Classroom::create($validated);

        return redirect()->route('classrooms.show', $classroom)
            ->with('status', "Rombongan belajar '{$classroom->name}' berhasil dibuat.");
    }

    /**
     * Display the specified classroom with active students and placement history.
     */
    public function show(Classroom $classroom): View
    {
        $classroom->load([
            'academicYear',
            'gradeLevel',
            'homeroomTeacher',
            'students.student',
        ]);

        $activeStudents = $classroom->students()
            ->where('enrollment_status', 'active')
            ->with('student')
            ->latest('joined_at')
            ->get();

        $historyStudents = $classroom->students()
            ->where('enrollment_status', '!=', 'active')
            ->with('student')
            ->latest('left_at')
            ->get();

        // Other classrooms in the same academic year for transfer target
        $otherClassrooms = Classroom::where('academic_year_id', $classroom->academic_year_id)
            ->where('id', '!=', $classroom->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Eligible students not yet placed in ANY active classroom for this academic year
        $availableStudents = User::where('role', UserRole::STUDENT->value)
            ->where('is_active', true)
            ->whereDoesntHave('classroomEnrollments', function ($q) use ($classroom) {
                $q->where('academic_year_id', $classroom->academic_year_id)
                    ->where('enrollment_status', 'active');
            })
            ->orderBy('name')
            ->get();

        return view('classrooms.show', compact(
            'classroom',
            'activeStudents',
            'historyStudents',
            'otherClassrooms',
            'availableStudents'
        ));
    }

    /**
     * Show the form for editing the specified classroom.
     */
    public function edit(Classroom $classroom): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $academicYears = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get();

        $gradeLevels = GradeLevel::active()->orderBy('order')->get();

        $teachers = User::where('role', UserRole::TEACHER->value)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('classrooms.edit', compact('classroom', 'academicYears', 'gradeLevels', 'teachers'));
    }

    /**
     * Update the specified classroom in storage.
     */
    public function update(UpdateClassroomRequest $request, Classroom $classroom): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        $classroom->update($validated);

        return redirect()->route('classrooms.show', $classroom)
            ->with('status', "Informasi rombongan belajar '{$classroom->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified classroom from storage.
     */
    public function destroy(Classroom $classroom): RedirectResponse
    {
        if ($classroom->activeStudents()->exists()) {
            return redirect()->back()->withErrors([
                'error' => "Rombongan belajar '{$classroom->name}' tidak dapat dihapus karena masih memiliki siswa aktif. Silakan kosongkan kelas atau ubah status menjadi non-aktif.",
            ]);
        }

        $name = $classroom->name;
        $classroom->delete();

        return redirect()->route('classrooms.index')
            ->with('status', "Rombongan belajar '{$name}' berhasil dihapus.");
    }
}
