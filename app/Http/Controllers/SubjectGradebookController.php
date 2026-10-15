<?php

namespace App\Http\Controllers;

use App\Http\Requests\Report\UpdateGradebookRequest;
use App\Models\AcademicYear;
use App\Models\AssessmentWeightConfiguration;
use App\Models\Classroom;
use App\Models\School;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\SubjectGradebook;
use App\Models\SubjectGradebookItem;
use App\Models\TeachingAssignment;
use App\Services\GradebookCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SubjectGradebookController extends Controller
{
    public function __construct(
        protected GradebookCalculationService $calculationService
    ) {}

    protected function currentSchoolId(): ?int
    {
        return auth()->user()?->school_id ?? School::current()?->id;
    }

    /**
     * Check if user is authorized to manage this gradebook.
     */
    protected function authorizeGradebookAccess(SubjectGradebook $gradebook, bool $requireEditPermission = false): void
    {
        $user = auth()->user();
        $schoolId = $this->currentSchoolId();

        if ($schoolId && (int) $gradebook->school_id !== (int) $schoolId) {
            abort(403, 'Akses ditolak: Rekap nilai ini tidak terdaftar pada sekolah Anda.');
        }

        if ($user->isSuperAdmin() || $user->isSchoolAdmin()) {
            return;
        }

        if ($user->isTeacher()) {
            // Must be the assigned teacher
            if ((int) $gradebook->teacher_id !== (int) $user->id) {
                // Or homeroom teacher of the class
                $isHomeroom = Classroom::where('id', $gradebook->classroom_id)
                    ->where('homeroom_teacher_id', $user->id)
                    ->exists();

                if (! $isHomeroom) {
                    abort(403, 'Akses ditolak: Anda tidak memiliki kewenangan atas rekap nilai mata pelajaran ini.');
                }

                if ($requireEditPermission) {
                    abort(403, 'Akses ditolak: Hanya guru pengampu yang dapat mengubah nilai mata pelajaran ini.');
                }
            }

            if ($requireEditPermission && ! $gradebook->isEditable()) {
                abort(403, 'Akses ditolak: Rekap nilai ini telah dikunci dan tidak dapat diubah tanpa persetujuan revisi.');
            }

            return;
        }

        abort(403, 'Akses tidak diizinkan.');
    }

    /**
     * Display a listing of subject gradebooks.
     */
    public function index(Request $request): View
    {
        $schoolId = $this->currentSchoolId();
        $user = auth()->user();

        $classroomId = $request->input('classroom_id');
        $subjectId = $request->input('subject_id');
        $academicYearId = $request->input('academic_year_id');
        $semesterId = $request->input('semester_id');
        $status = $request->input('status');

        $query = SubjectGradebook::query()
            ->with(['classroom', 'subject', 'teacher', 'academicYear', 'semester'])
            ->withCount(['items'])
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($classroomId, fn ($q) => $q->where('classroom_id', (int) $classroomId))
            ->when($subjectId, fn ($q) => $q->where('subject_id', (int) $subjectId))
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', (int) $academicYearId))
            ->when($semesterId, fn ($q) => $q->where('semester_id', (int) $semesterId))
            ->when($status, fn ($q) => $q->where('status', $status));

        if ($user->isTeacher() && ! $user->isSchoolAdmin() && ! $user->isSuperAdmin()) {
            $query->where(function ($q) use ($user): void {
                $q->where('teacher_id', $user->id)
                    ->orWhereHas('classroom', fn ($cq) => $cq->where('homeroom_teacher_id', $user->id));
            });
        }

        $gradebooks = $query->latest('id')->paginate(15);

        // Teaching assignments available to initialize if teacher
        $teachingAssignments = collect();
        if ($user->isTeacher() || $user->isSchoolAdmin() || $user->isSuperAdmin()) {
            $taQuery = TeachingAssignment::query()
                ->with(['classroom', 'subject', 'teacher', 'academicYear', 'semester'])
                ->where('is_active', true)
                ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));

            if ($user->isTeacher() && ! $user->isSchoolAdmin() && ! $user->isSuperAdmin()) {
                $taQuery->where('teacher_id', $user->id);
            }

            $teachingAssignments = $taQuery->get();
        }

        $classrooms = Classroom::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->orderBy('name')->get();
        $subjects = Subject::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->orderBy('name')->get();
        $academicYears = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->get();
        $semesters = Semester::when($schoolId, fn ($q) => $q->whereHas('academicYear', fn ($ay) => $ay->where('school_id', $schoolId)))->get();

        return view('reports.gradebook.index', compact(
            'gradebooks',
            'teachingAssignments',
            'classrooms',
            'subjects',
            'academicYears',
            'semesters'
        ));
    }

    /**
     * Show form or preview to initialize a subject gradebook.
     */
    public function create(Request $request): View
    {
        $schoolId = $this->currentSchoolId();
        $user = auth()->user();

        $teachingAssignmentId = $request->input('teaching_assignment_id');
        $teachingAssignment = null;

        if ($teachingAssignmentId) {
            $teachingAssignment = TeachingAssignment::with(['classroom.students.student.studentProfile', 'subject', 'teacher', 'academicYear', 'semester'])
                ->findOrFail($teachingAssignmentId);

            if ($schoolId && (int) $teachingAssignment->school_id !== (int) $schoolId) {
                abort(403, 'Akses ditolak.');
            }

            if ($user->isTeacher() && ! $user->isSchoolAdmin() && ! $user->isSuperAdmin() && (int) $teachingAssignment->teacher_id !== (int) $user->id) {
                abort(403, 'Anda bukan pengampu penugasan mengajar ini.');
            }
        }

        $teachingAssignments = TeachingAssignment::with(['classroom', 'subject', 'academicYear', 'semester'])
            ->where('is_active', true)
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($user->isTeacher() && ! $user->isSchoolAdmin() && ! $user->isSuperAdmin(), fn ($q) => $q->where('teacher_id', $user->id))
            ->get();

        return view('reports.gradebook.create', compact('teachingAssignment', 'teachingAssignments'));
    }

    /**
     * Store (initialize) a subject gradebook and compile initial student grades.
     */
    public function store(Request $request): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();
        $user = auth()->user();

        $request->validate([
            'teaching_assignment_id' => ['required', 'exists:teaching_assignments,id'],
        ]);

        $ta = TeachingAssignment::with(['classroom.activeStudents.student', 'subject', 'teacher'])
            ->findOrFail($request->input('teaching_assignment_id'));

        if ($schoolId && (int) $ta->school_id !== (int) $schoolId) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->isTeacher() && ! $user->isSchoolAdmin() && ! $user->isSuperAdmin() && (int) $ta->teacher_id !== (int) $user->id) {
            abort(403, 'Anda bukan pengampu penugasan mengajar ini.');
        }

        // Check if gradebook already exists for this exact tuple
        $existing = SubjectGradebook::where('classroom_id', $ta->classroom_id)
            ->where('subject_id', $ta->subject_id)
            ->where('academic_year_id', $ta->academic_year_id)
            ->where('semester_id', $ta->semester_id)
            ->first();

        if ($existing) {
            return redirect()->route('reports.gradebook.show', $existing)
                ->with('info', 'Rekap nilai untuk mata pelajaran dan kelas ini sudah ada.');
        }

        // Resolve weight configuration
        $weightConfig = AssessmentWeightConfiguration::resolveFor(
            $ta->school_id ?? $schoolId,
            $ta->academic_year_id,
            $ta->semester_id,
            $ta->subject_id
        );

        $weights = [
            'assignment_weight' => (float) ($weightConfig?->assignment_weight ?? 30.0),
            'quiz_weight' => (float) ($weightConfig?->quiz_weight ?? 20.0),
            'midterm_weight' => (float) ($weightConfig?->midterm_weight ?? 25.0),
            'final_weight' => (float) ($weightConfig?->final_weight ?? 25.0),
            'passing_grade' => (float) ($weightConfig?->passing_grade ?? 75.0),
            'grade_scale' => $weightConfig?->grade_scale ?? AssessmentWeightConfiguration::defaultGradeScale(),
        ];

        DB::transaction(function () use ($ta, $schoolId, $weightConfig, $weights, &$gradebook): void {
            $gradebook = SubjectGradebook::create([
                'school_id' => $ta->school_id ?? $schoolId,
                'classroom_id' => $ta->classroom_id,
                'subject_id' => $ta->subject_id,
                'teacher_id' => $ta->teacher_id,
                'academic_year_id' => $ta->academic_year_id,
                'semester_id' => $ta->semester_id,
                'assessment_weight_configuration_id' => $weightConfig?->id,
                'weight_snapshot' => $weights,
                'status' => SubjectGradebook::STATUS_DRAFT,
            ]);

            // Compile items for all active students in the classroom
            $students = $ta->classroom->activeStudents;
            foreach ($students as $enrollment) {
                $student = $enrollment->student;
                if (! $student) {
                    continue;
                }

                $sources = $this->calculationService->aggregateStudentSourceScores(
                    $student->id,
                    $ta->classroom_id,
                    $ta->subject_id,
                    $ta->academic_year_id,
                    $ta->semester_id
                );

                $finalScore = $this->calculationService->calculateFinalScore(
                    $sources['assignment_avg'],
                    $sources['quiz_avg'],
                    $sources['midterm_score'],
                    $sources['final_score_raw'],
                    $weights
                );

                $predicate = $this->calculationService->determinePredicate($finalScore, $weights['grade_scale'], $weights['passing_grade']);
                $isPassed = $this->calculationService->isPassed($finalScore, $weights['passing_grade']);
                $description = $this->calculationService->generateAchievementDescription(
                    $ta->subject?->name ?? 'Mata Pelajaran',
                    $predicate,
                    $finalScore,
                    $weights['passing_grade']
                );

                SubjectGradebookItem::create([
                    'subject_gradebook_id' => $gradebook->id,
                    'student_id' => $student->id,
                    'assignment_avg' => $sources['assignment_avg'],
                    'quiz_avg' => $sources['quiz_avg'],
                    'midterm_score' => $sources['midterm_score'],
                    'final_score_raw' => $sources['final_score_raw'],
                    'final_score' => $finalScore,
                    'predicate' => $predicate,
                    'is_passed' => $isPassed,
                    'is_complete' => $sources['is_complete'],
                    'achievement_description' => $description,
                ]);
            }
        });

        return redirect()->route('reports.gradebook.show', $gradebook)
            ->with('success', 'Buku nilai semester berhasil dibuat dan dihubungkan ke sumber tugas & CBT.');
    }

    /**
     * Display a specific subject gradebook with student score details.
     */
    public function show(SubjectGradebook $gradebook): View
    {
        $this->authorizeGradebookAccess($gradebook);

        $gradebook->load([
            'classroom',
            'subject',
            'teacher.staffProfile',
            'academicYear',
            'semester',
            'assessmentWeightConfiguration',
            'verifiedByUser',
            'items' => fn ($q) => $q->with(['student.studentProfile'])->orderBy('id'),
        ]);

        return view('reports.gradebook.show', compact('gradebook'));
    }

    /**
     * Show form to edit manual components & descriptions.
     */
    public function edit(SubjectGradebook $gradebook): View
    {
        $this->authorizeGradebookAccess($gradebook, true);

        $gradebook->load([
            'classroom',
            'subject',
            'teacher',
            'academicYear',
            'semester',
            'items.student.studentProfile',
        ]);

        return view('reports.gradebook.edit', compact('gradebook'));
    }

    /**
     * Update scores & descriptions in the gradebook.
     */
    public function update(UpdateGradebookRequest $request, SubjectGradebook $gradebook): RedirectResponse
    {
        $this->authorizeGradebookAccess($gradebook, true);

        $validated = $request->validated();
        $weights = $gradebook->weight_snapshot ?? [
            'assignment_weight' => 30.0,
            'quiz_weight' => 20.0,
            'midterm_weight' => 25.0,
            'final_weight' => 25.0,
            'passing_grade' => 75.0,
            'grade_scale' => AssessmentWeightConfiguration::defaultGradeScale(),
        ];

        DB::transaction(function () use ($validated, $gradebook, $weights): void {
            if (isset($validated['submission_notes'])) {
                $gradebook->update(['submission_notes' => $validated['submission_notes']]);
            }

            foreach ($validated['items'] as $itemData) {
                $item = SubjectGradebookItem::where('subject_gradebook_id', $gradebook->id)
                    ->where('id', $itemData['id'])
                    ->first();

                if (! $item) {
                    continue;
                }

                $finalScore = $this->calculationService->calculateFinalScore(
                    (float) $itemData['assignment_avg'],
                    (float) $itemData['quiz_avg'],
                    (float) $itemData['midterm_score'],
                    (float) $itemData['final_score_raw'],
                    $weights
                );

                $predicate = $this->calculationService->determinePredicate(
                    $finalScore,
                    $weights['grade_scale'] ?? null,
                    $weights['passing_grade'] ?? 75.0
                );

                $isPassed = $this->calculationService->isPassed($finalScore, $weights['passing_grade'] ?? 75.0);

                $item->update([
                    'assignment_avg' => $itemData['assignment_avg'],
                    'quiz_avg' => $itemData['quiz_avg'],
                    'midterm_score' => $itemData['midterm_score'],
                    'final_score_raw' => $itemData['final_score_raw'],
                    'final_score' => $finalScore,
                    'predicate' => $predicate,
                    'is_passed' => $isPassed,
                    'achievement_description' => $itemData['achievement_description'] ?? $item->achievement_description,
                    'improvement_description' => $itemData['improvement_description'] ?? $item->improvement_description,
                ]);
            }
        });

        return redirect()->route('reports.gradebook.show', $gradebook)
            ->with('success', 'Nilai dan capaian pembelajaran berhasil diperbarui dan dihitung ulang.');
    }

    /**
     * Submit gradebook for verification.
     */
    public function submit(Request $request, SubjectGradebook $gradebook): RedirectResponse
    {
        $this->authorizeGradebookAccess($gradebook, true);

        $gradebook->update([
            'status' => SubjectGradebook::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'submission_notes' => $request->input('submission_notes', $gradebook->submission_notes),
        ]);

        return redirect()->route('reports.gradebook.show', $gradebook)
            ->with('success', 'Rekap nilai berhasil dikirimkan ke pihak kurikulum/sekolah untuk verifikasi.');
    }

    /**
     * Verify / approve gradebook (admin only).
     */
    public function verify(Request $request, SubjectGradebook $gradebook): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin() && ! $user->isSchoolAdmin()) {
            abort(403, 'Akses verifikasi hanya dapat dilakukan oleh Administrator Sekolah.');
        }

        $this->authorizeGradebookAccess($gradebook);

        $gradebook->update([
            'status' => SubjectGradebook::STATUS_VERIFIED,
            'verified_at' => now(),
            'verified_by' => $user->id,
            'verification_notes' => $request->input('verification_notes', 'Disetujui dan diverifikasi.'),
        ]);

        return redirect()->route('reports.gradebook.show', $gradebook)
            ->with('success', 'Rekap nilai mata pelajaran berhasil diverifikasi dan disetujui.');
    }

    /**
     * Request revision on gradebook (admin only).
     */
    public function requestRevision(Request $request, SubjectGradebook $gradebook): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin() && ! $user->isSchoolAdmin()) {
            abort(403, 'Akses revisi hanya dapat dilakukan oleh Administrator Sekolah.');
        }

        $this->authorizeGradebookAccess($gradebook);

        $request->validate([
            'verification_notes' => ['required', 'string', 'max:1000'],
        ]);

        $gradebook->update([
            'status' => SubjectGradebook::STATUS_REVISION_REQUESTED,
            'verification_notes' => $request->input('verification_notes'),
        ]);

        return redirect()->route('reports.gradebook.show', $gradebook)
            ->with('warning', 'Rekap nilai dikembalikan ke guru pengampu untuk perbaikan/revisi.');
    }

    /**
     * Re-sync source assignment and CBT scores while in draft mode.
     */
    public function sync(SubjectGradebook $gradebook): RedirectResponse
    {
        $this->authorizeGradebookAccess($gradebook, true);

        $weights = $gradebook->weight_snapshot ?? [
            'assignment_weight' => 30.0,
            'quiz_weight' => 20.0,
            'midterm_weight' => 25.0,
            'final_weight' => 25.0,
            'passing_grade' => 75.0,
            'grade_scale' => AssessmentWeightConfiguration::defaultGradeScale(),
        ];

        DB::transaction(function () use ($gradebook, $weights): void {
            foreach ($gradebook->items as $item) {
                $sources = $this->calculationService->aggregateStudentSourceScores(
                    $item->student_id,
                    $gradebook->classroom_id,
                    $gradebook->subject_id,
                    $gradebook->academic_year_id,
                    $gradebook->semester_id
                );

                $finalScore = $this->calculationService->calculateFinalScore(
                    $sources['assignment_avg'],
                    $sources['quiz_avg'],
                    $sources['midterm_score'],
                    $sources['final_score_raw'],
                    $weights
                );

                $predicate = $this->calculationService->determinePredicate(
                    $finalScore,
                    $weights['grade_scale'],
                    $weights['passing_grade']
                );
                $isPassed = $this->calculationService->isPassed($finalScore, $weights['passing_grade']);

                $item->update([
                    'assignment_avg' => $sources['assignment_avg'],
                    'quiz_avg' => $sources['quiz_avg'],
                    'midterm_score' => $sources['midterm_score'],
                    'final_score_raw' => $sources['final_score_raw'],
                    'final_score' => $finalScore,
                    'predicate' => $predicate,
                    'is_passed' => $isPassed,
                    'is_complete' => $sources['is_complete'],
                ]);
            }
        });

        return redirect()->route('reports.gradebook.show', $gradebook)
            ->with('success', 'Nilai sumber dari tugas dan ujian CBT berhasil disinkronkan ulang.');
    }
}
