<?php

namespace App\Http\Controllers;

use App\Http\Requests\Report\UpdateHomeroomReportRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\School;
use App\Models\Semester;
use App\Models\StudentReportCard;
use App\Services\GradebookCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentReportCardController extends Controller
{
    public function __construct(
        protected GradebookCalculationService $calculationService
    ) {}

    protected function currentSchoolId(): ?int
    {
        return auth()->user()?->school_id ?? School::current()?->id;
    }

    /**
     * Authorize access to report card.
     */
    protected function authorizeReportCardAccess(StudentReportCard $reportCard, bool $requireEditPermission = false): void
    {
        $user = auth()->user();
        $schoolId = $this->currentSchoolId();

        if ($schoolId && (int) $reportCard->school_id !== (int) $schoolId) {
            abort(403, 'Akses ditolak: Buku rapor ini tidak terdaftar pada sekolah Anda.');
        }

        if ($user->isSuperAdmin() || $user->isSchoolAdmin()) {
            return;
        }

        if ($user->isTeacher()) {
            // Must be homeroom teacher of the classroom
            $isHomeroom = (int) $reportCard->homeroom_teacher_id === (int) $user->id ||
                Classroom::where('id', $reportCard->classroom_id)->where('homeroom_teacher_id', $user->id)->exists();

            if (! $isHomeroom) {
                abort(403, 'Akses ditolak: Hanya wali kelas yang berwenang mengelola buku rapor siswa ini.');
            }

            if ($requireEditPermission && ! $reportCard->isEditable()) {
                abort(403, 'Akses ditolak: Buku rapor ini telah diajukan atau diterbitkan dan tidak dapat diubah.');
            }

            return;
        }

        abort(403, 'Akses tidak diizinkan.');
    }

    /**
     * Display a listing of classrooms with report card summaries.
     */
    public function index(Request $request): View
    {
        $schoolId = $this->currentSchoolId();
        $user = auth()->user();

        $academicYearId = $request->input('academic_year_id');
        $activeYear = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->first();

        $selectedYearId = $academicYearId ? (int) $academicYearId : $activeYear?->id;

        $query = Classroom::query()
            ->with(['gradeLevel', 'academicYear', 'homeroomTeacher.staffProfile'])
            ->withCount(['activeStudents'])
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($selectedYearId, fn ($q) => $q->where('academic_year_id', $selectedYearId));

        if ($user->isTeacher() && ! $user->isSchoolAdmin() && ! $user->isSuperAdmin()) {
            $query->where('homeroom_teacher_id', $user->id);
        }

        $classrooms = $query->orderBy('name')->paginate(12);
        $academicYears = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->get();

        return view('reports.cards.index', compact('classrooms', 'academicYears', 'selectedYearId'));
    }

    /**
     * Display report cards for all students in a classroom.
     */
    public function classroomView(Classroom $classroom, Request $request): View
    {
        $schoolId = $this->currentSchoolId();
        $user = auth()->user();

        if ($schoolId && (int) $classroom->school_id !== (int) $schoolId) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->isTeacher() && ! $user->isSchoolAdmin() && ! $user->isSuperAdmin() && (int) $classroom->homeroom_teacher_id !== (int) $user->id) {
            abort(403, 'Anda bukan wali kelas untuk kelas ini.');
        }

        $semesterId = $request->input('semester_id');
        $activeSemester = Semester::where('academic_year_id', $classroom->academic_year_id)
            ->where('is_active', true)
            ->first();

        $selectedSemesterId = $semesterId ? (int) $semesterId : $activeSemester?->id;

        $classroom->load(['gradeLevel', 'academicYear', 'homeroomTeacher', 'activeStudents.student.studentProfile']);

        // Find or associate existing report cards for these students
        $reportCards = StudentReportCard::where('classroom_id', $classroom->id)
            ->where('academic_year_id', $classroom->academic_year_id)
            ->when($selectedSemesterId, fn ($q) => $q->where('semester_id', $selectedSemesterId))
            ->with(['student.studentProfile', 'items'])
            ->get()
            ->keyBy('student_id');

        $semesters = Semester::where('academic_year_id', $classroom->academic_year_id)->get();

        return view('reports.cards.classroom', compact(
            'classroom',
            'reportCards',
            'semesters',
            'selectedSemesterId'
        ));
    }

    /**
     * Show detail preview of a single student report card.
     */
    public function show(StudentReportCard $reportCard): View
    {
        $this->authorizeReportCardAccess($reportCard);

        $reportCard->load([
            'school',
            'student.studentProfile',
            'classroom.gradeLevel',
            'academicYear',
            'semester',
            'homeroomTeacher.staffProfile',
            'approvedByUser',
            'publishedByUser',
            'items.subject',
        ]);

        return view('reports.cards.show', compact('reportCard'));
    }

    /**
     * Edit form for homeroom teacher (attendance, extracurriculars, notes).
     */
    public function edit(StudentReportCard $reportCard): View
    {
        $this->authorizeReportCardAccess($reportCard, true);

        $reportCard->load([
            'student.studentProfile',
            'classroom',
            'academicYear',
            'semester',
            'items',
        ]);

        return view('reports.cards.edit', compact('reportCard'));
    }

    /**
     * Update homeroom teacher evaluations.
     */
    public function update(UpdateHomeroomReportRequest $request, StudentReportCard $reportCard): RedirectResponse
    {
        $this->authorizeReportCardAccess($reportCard, true);

        $validated = $request->validated();

        $reportCard->update([
            'attendance_summary' => $validated['attendance_summary'] ?? $reportCard->attendance_summary,
            'extracurricular_evaluations' => $validated['extracurriculars'] ?? $reportCard->extracurricular_evaluations,
            'student_achievements' => $validated['achievements'] ?? $reportCard->student_achievements,
            'homeroom_notes' => $validated['homeroom_notes'] ?? $reportCard->homeroom_notes,
        ]);

        return redirect()->route('reports.cards.show', $reportCard)
            ->with('success', 'Catatan wali kelas dan data nonakademik berhasil disimpan.');
    }

    /**
     * Submit single report card for school verification.
     */
    public function submit(Request $request, StudentReportCard $reportCard): RedirectResponse
    {
        $this->authorizeReportCardAccess($reportCard, true);

        $reportCard->update([
            'status' => StudentReportCard::STATUS_PENDING_REVIEW,
        ]);

        return redirect()->route('reports.cards.show', $reportCard)
            ->with('success', 'Buku rapor siswa berhasil diajukan untuk verifikasi sekolah.');
    }

    /**
     * Approve report card (school admin only).
     */
    public function approve(Request $request, StudentReportCard $reportCard): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin() && ! $user->isSchoolAdmin()) {
            abort(403, 'Akses persetujuan hanya untuk Administrator Sekolah.');
        }

        $this->authorizeReportCardAccess($reportCard);

        $reportCard->update([
            'status' => StudentReportCard::STATUS_APPROVED,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('reports.cards.show', $reportCard)
            ->with('success', 'Buku rapor siswa telah disetujui.');
    }

    /**
     * Request revision on report card (school admin only).
     */
    public function requestRevision(Request $request, StudentReportCard $reportCard): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin() && ! $user->isSchoolAdmin()) {
            abort(403, 'Akses revisi hanya untuk Administrator Sekolah.');
        }

        $this->authorizeReportCardAccess($reportCard);

        $request->validate([
            'revision_notes' => ['required', 'string', 'max:1000'],
        ]);

        $reportCard->update([
            'status' => StudentReportCard::STATUS_REVISION_REQUESTED,
            'revision_notes' => $request->input('revision_notes'),
        ]);

        return redirect()->route('reports.cards.show', $reportCard)
            ->with('warning', 'Buku rapor dikembalikan ke wali kelas untuk revisi.');
    }

    /**
     * Publish report card (school admin only).
     * Creates an immutable frozen snapshot.
     */
    public function publish(Request $request, StudentReportCard $reportCard): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin() && ! $user->isSchoolAdmin()) {
            abort(403, 'Akses publikasi hanya untuk Administrator Sekolah.');
        }

        $this->authorizeReportCardAccess($reportCard);

        // Generate complete immutable snapshot
        $snapshot = $reportCard->buildSnapshot();

        $reportCard->update([
            'status' => StudentReportCard::STATUS_PUBLISHED,
            'published_by' => $user->id,
            'published_at' => now(),
            'snapshot' => $snapshot,
        ]);

        return redirect()->route('reports.cards.show', $reportCard)
            ->with('success', 'Buku rapor berhasil diterbitkan secara resmi dengan snapshot data permanen.');
    }

    /**
     * Synchronize single report card items from verified gradebooks.
     */
    public function sync(StudentReportCard $reportCard): RedirectResponse
    {
        $this->authorizeReportCardAccess($reportCard, true);

        $this->calculationService->syncReportCardFromGradebooks($reportCard);

        return redirect()->route('reports.cards.show', $reportCard)
            ->with('success', 'Nilai mata pelajaran dan presensi berhasil disinkronkan dari data nilai terverifikasi.');
    }

    /**
     * Bulk compile / synchronize report cards for all students in a classroom.
     */
    public function bulkSync(Classroom $classroom, Request $request): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();
        $user = auth()->user();

        if ($schoolId && (int) $classroom->school_id !== (int) $schoolId) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->isTeacher() && ! $user->isSchoolAdmin() && ! $user->isSuperAdmin() && (int) $classroom->homeroom_teacher_id !== (int) $user->id) {
            abort(403, 'Anda bukan wali kelas untuk kelas ini.');
        }

        $semesterId = $request->input('semester_id');
        if (! $semesterId) {
            $activeSemester = Semester::where('academic_year_id', $classroom->academic_year_id)
                ->where('is_active', true)
                ->first();
            $semesterId = $activeSemester?->id;
        }

        if (! $semesterId) {
            return back()->with('error', 'Semester aktif tidak ditemukan.');
        }

        DB::transaction(function () use ($classroom, $semesterId, $schoolId): void {
            foreach ($classroom->activeStudents as $enrollment) {
                $student = $enrollment->student;
                if (! $student) {
                    continue;
                }

                $reportCard = StudentReportCard::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'classroom_id' => $classroom->id,
                        'academic_year_id' => $classroom->academic_year_id,
                        'semester_id' => $semesterId,
                    ],
                    [
                        'school_id' => $classroom->school_id ?? $schoolId,
                        'homeroom_teacher_id' => $classroom->homeroom_teacher_id,
                        'status' => StudentReportCard::STATUS_DRAFT,
                    ]
                );

                if ($reportCard->isEditable()) {
                    $this->calculationService->syncReportCardFromGradebooks($reportCard);
                }
            }
        });

        return redirect()->route('reports.cards.classroom', ['classroom' => $classroom, 'semester_id' => $semesterId])
            ->with('success', 'Kompilasi nilai E-Rapor seluruh siswa berhasil dilakukan.');
    }

    /**
     * Bulk submit all draft report cards in a classroom.
     */
    public function bulkSubmit(Classroom $classroom, Request $request): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();
        $user = auth()->user();

        if ($schoolId && (int) $classroom->school_id !== (int) $schoolId) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->isTeacher() && ! $user->isSchoolAdmin() && ! $user->isSuperAdmin() && (int) $classroom->homeroom_teacher_id !== (int) $user->id) {
            abort(403, 'Anda bukan wali kelas untuk kelas ini.');
        }

        $semesterId = $request->input('semester_id');

        StudentReportCard::where('classroom_id', $classroom->id)
            ->where('academic_year_id', $classroom->academic_year_id)
            ->when($semesterId, fn ($q) => $q->where('semester_id', $semesterId))
            ->whereIn('status', [StudentReportCard::STATUS_DRAFT, StudentReportCard::STATUS_REVISION_REQUESTED])
            ->update([
                'status' => StudentReportCard::STATUS_PENDING_REVIEW,
            ]);

        return back()->with('success', 'Seluruh buku rapor yang telah lengkap diajukan untuk verifikasi sekolah.');
    }

    /**
     * Bulk publish all approved report cards in a classroom (Admin only).
     */
    public function bulkPublish(Classroom $classroom, Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin() && ! $user->isSchoolAdmin()) {
            abort(403, 'Akses publikasi hanya untuk Administrator Sekolah.');
        }

        $semesterId = $request->input('semester_id');

        $reports = StudentReportCard::where('classroom_id', $classroom->id)
            ->where('academic_year_id', $classroom->academic_year_id)
            ->when($semesterId, fn ($q) => $q->where('semester_id', $semesterId))
            ->whereIn('status', [StudentReportCard::STATUS_APPROVED, StudentReportCard::STATUS_PENDING_REVIEW])
            ->get();

        DB::transaction(function () use ($reports, $user): void {
            foreach ($reports as $report) {
                $snapshot = $report->buildSnapshot();
                $report->update([
                    'status' => StudentReportCard::STATUS_PUBLISHED,
                    'published_by' => $user->id,
                    'published_at' => now(),
                    'snapshot' => $snapshot,
                ]);
            }
        });

        return back()->with('success', count($reports).' buku rapor berhasil diterbitkan secara massal.');
    }
}
