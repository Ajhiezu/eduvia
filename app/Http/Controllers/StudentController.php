<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Student\ChangeStudentStatusRequest;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Display a listing of students with filters and metrics.
     */
    public function index(Request $request): View
    {
        $school = School::current();
        $schoolId = $school?->id ?? $request->user()?->school_id;

        $search = $request->input('search');
        $status = $request->input('status');
        $classroomId = $request->input('classroom_id');
        $gradeLevelId = $request->input('grade_level_id');

        $query = User::query()
            ->where('role', UserRole::STUDENT->value)
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with(['studentProfile', 'classroomEnrollments.classroom.gradeLevel'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('studentProfile', function ($p) use ($search) {
                            $p->where('nis', 'like', "%{$search}%")
                                ->orWhere('nisn', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status, function ($q) use ($status) {
                $q->whereHas('studentProfile', fn ($p) => $p->where('student_status', $status));
            })
            ->when($classroomId, function ($q) use ($classroomId) {
                $q->whereHas('classroomEnrollments', function ($ce) use ($classroomId) {
                    $ce->where('classroom_id', $classroomId)
                        ->where('enrollment_status', 'active');
                });
            })
            ->when($gradeLevelId, function ($q) use ($gradeLevelId) {
                $q->whereHas('classroomEnrollments.classroom', function ($c) use ($gradeLevelId) {
                    $c->where('grade_level_id', $gradeLevelId);
                });
            })
            ->latest('id');

        $students = $query->paginate(15)->withQueryString();

        // Metrics calculations
        $baseStudentQuery = User::where('role', UserRole::STUDENT->value)
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));
        $totalActiveCount = (clone $baseStudentQuery)->whereHas('studentProfile', fn ($p) => $p->where('student_status', 'active'))->count();
        $totalTransferredCount = (clone $baseStudentQuery)->whereHas('studentProfile', fn ($p) => $p->where('student_status', 'transferred'))->count();
        $totalGraduatedCount = (clone $baseStudentQuery)->whereHas('studentProfile', fn ($p) => $p->where('student_status', 'graduated'))->count();
        $totalDroppedCount = (clone $baseStudentQuery)->whereHas('studentProfile', fn ($p) => $p->where('student_status', 'dropped'))->count();

        // Filters data
        $classrooms = Classroom::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->active()
            ->orderBy('name')
            ->get();

        $gradeLevels = GradeLevel::active()->orderBy('order')->get();

        return view('students.index', compact(
            'students',
            'search',
            'status',
            'classroomId',
            'gradeLevelId',
            'classrooms',
            'gradeLevels',
            'totalActiveCount',
            'totalTransferredCount',
            'totalGraduatedCount',
            'totalDroppedCount'
        ));
    }

    /**
     * Show the form for creating a new student.
     */
    public function create(): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $classrooms = Classroom::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->active()
            ->with(['academicYear', 'gradeLevel'])
            ->orderBy('name')
            ->get();

        return view('students.create', compact('classrooms'));
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $school = School::current();
        $schoolId = $school?->id;

        $student = DB::transaction(function () use ($request, $schoolId) {
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'role' => UserRole::STUDENT->value,
                'school_id' => $schoolId,
                'is_active' => true,
                'password' => Hash::make($request->input('password') ?? 'Password123!'),
            ]);

            StudentProfile::create([
                'user_id' => $user->id,
                'school_id' => $schoolId,
                'nis' => $request->input('nis'),
                'nisn' => $request->input('nisn'),
                'gender' => $request->input('gender'),
                'birth_place' => $request->input('birth_place'),
                'birth_date' => $request->input('birth_date'),
                'religion' => $request->input('religion'),
                'address' => $request->input('address'),
                'parent_name' => $request->input('parent_name'),
                'parent_phone' => $request->input('parent_phone'),
                'parent_relationship' => $request->input('parent_relationship'),
                'student_status' => $request->input('student_status', 'active'),
                'enrollment_date' => $request->input('enrollment_date') ?? now()->toDateString(),
                'notes' => $request->input('notes'),
            ]);

            // If initial classroom is provided, enroll student
            $classroomId = $request->input('classroom_id');
            if ($classroomId) {
                $classroom = Classroom::findOrFail($classroomId);
                $classroom->addStudent(
                    $user->id,
                    $request->input('enrollment_date') ?? now()->toDateString(),
                    'Penempatan awal pendaftaran siswa'
                );
            }

            return $user;
        });

        return redirect()->route('students.show', $student)
            ->with('status', "Peserta didik '{$student->name}' berhasil didaftarkan.");
    }

    /**
     * Display the specified student profile with history tabs.
     */
    public function show(User $student): View
    {
        if (! $student->isStudent()) {
            abort(404);
        }

        $this->authorizeStudent($student);

        $student->load([
            'studentProfile',
            'classroomEnrollments.classroom.gradeLevel',
            'classroomEnrollments.academicYear',
            'studentStatusHistories.actor',
            'attendanceRecords.session.teachingAssignment.subject',
            'activeParents',
            'examAttempts.exam.subject',
            'studentReportCards.academicYear',
            'studentReportCards.semester',
        ]);

        $profile = $student->studentProfile;

        // Current active classroom
        $currentEnrollment = $student->classroomEnrollments
            ->where('enrollment_status', 'active')
            ->first();

        // Attendance summary
        $records = $student->attendanceRecords;
        $present = $records->where('status', 'present')->count();
        $late = $records->where('status', 'late')->count();
        $sick = $records->where('status', 'sick')->count();
        $permit = $records->where('status', 'permit')->count();
        $absent = $records->where('status', 'absent')->count();
        $totalSessions = $records->count();
        $attendanceRate = $totalSessions > 0 ? round((($present + $late) / $totalSessions) * 100, 1) : 0.0;

        // Available classrooms for mutation if needed
        $school = School::current();
        $schoolId = $school?->id ?? request()->user()?->school_id;
        $otherClassrooms = Classroom::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->active()
            ->when($currentEnrollment, fn ($q) => $q->where('id', '!=', $currentEnrollment->classroom_id))
            ->orderBy('name')
            ->get();

        $linkedParents = $student->activeParents;
        $examAttempts = $student->examAttempts()->with('exam.subject')->latest()->take(10)->get();
        $reportCards = $student->studentReportCards()->with(['academicYear', 'semester'])->latest()->get();

        return view('students.show', compact(
            'student',
            'profile',
            'currentEnrollment',
            'attendanceRate',
            'present',
            'late',
            'sick',
            'permit',
            'absent',
            'totalSessions',
            'otherClassrooms',
            'linkedParents',
            'examAttempts',
            'reportCards'
        ));
    }

    /**
     * Show the form for editing the specified student profile.
     */
    public function edit(User $student): View
    {
        if (! $student->isStudent()) {
            abort(404);
        }

        $this->authorizeStudent($student);

        $profile = $student->studentProfile;

        return view('students.edit', compact('student', 'profile'));
    }

    /**
     * Update the specified student profile in storage.
     */
    public function update(UpdateStudentRequest $request, User $student): RedirectResponse
    {
        $this->authorizeStudent($student);

        DB::transaction(function () use ($request, $student) {
            $student->update([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
            ]);

            $student->studentProfile()->updateOrCreate(
                ['user_id' => $student->id],
                [
                    'nis' => $request->input('nis'),
                    'nisn' => $request->input('nisn'),
                    'gender' => $request->input('gender'),
                    'birth_place' => $request->input('birth_place'),
                    'birth_date' => $request->input('birth_date'),
                    'religion' => $request->input('religion'),
                    'address' => $request->input('address'),
                    'parent_name' => $request->input('parent_name'),
                    'parent_phone' => $request->input('parent_phone'),
                    'parent_relationship' => $request->input('parent_relationship'),
                    'enrollment_date' => $request->input('enrollment_date'),
                    'notes' => $request->input('notes'),
                ]
            );
        });

        return redirect()->route('students.show', $student)
            ->with('status', 'Profil peserta didik berhasil diperbarui.');
    }

    /**
     * Update the student status with audit trail.
     */
    public function updateStatus(ChangeStudentStatusRequest $request, User $student): RedirectResponse
    {
        $this->authorizeStudent($student);

        $profile = $student->studentProfile;
        if (! $profile) {
            $profile = StudentProfile::create([
                'user_id' => $student->id,
                'student_status' => 'active',
            ]);
        }

        $profile->changeStatus(
            $request->input('status'),
            $request->input('effective_date'),
            $request->input('reason'),
            $request->user()
        );

        return redirect()->route('students.show', $student)
            ->with('status', "Status kesiswaan berhasil diperbarui menjadi '{$profile->statusLabel()}'.");
    }

    /**
     * Remove the specified student from storage with safety checks.
     */
    public function destroy(User $student): RedirectResponse
    {
        $this->authorizeStudent($student);

        // Safety check: protect academic and attendance records
        if ($student->classroomEnrollments()->exists() || $student->attendanceRecords()->exists()) {
            return redirect()->back()->withErrors([
                'error' => "Peserta didik '{$student->name}' tidak dapat dihapus karena memiliki riwayat akademik/presensi. Ubah status kesiswaan menjadi 'Non-Aktif' atau 'Keluar' untuk menghentikan akses.",
            ]);
        }

        $student->studentProfile?->delete();
        $student->delete();

        return redirect()->route('students.index')
            ->with('status', 'Akun peserta didik berhasil dihapus.');
    }

    /**
     * Ensure student belongs to the current school scope.
     */
    protected function authorizeStudent(User $student): void
    {
        $user = request()->user();
        if ($user && $user->isSuperAdmin()) {
            return;
        }

        $school = School::current();
        $schoolId = $school?->id ?? $user?->school_id;

        if ($schoolId && $student->school_id && $student->school_id !== $schoolId) {
            abort(403, 'Akses ke data peserta didik sekolah lain tidak diizinkan.');
        }
    }
}
