<?php

namespace App\Http\Controllers;

use App\Http\Requests\Exam\StoreExamRequest;
use App\Http\Requests\Exam\UpdateExamRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionItem;
use App\Models\School;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExamController extends Controller
{
    /**
     * Resolve the active school ID for current context.
     */
    protected function currentSchoolId(): ?int
    {
        return auth()->user()?->school_id ?? School::current()?->id;
    }

    /**
     * Ensure the exam belongs to the current school tenant.
     */
    protected function authorizeTenant(Exam $exam): void
    {
        $schoolId = $this->currentSchoolId();
        if ($schoolId && (int) $exam->school_id !== (int) $schoolId) {
            abort(403, 'Akses ditolak: Ujian ini tidak terdaftar pada sekolah Anda.');
        }
    }

    /**
     * Display a listing of exams.
     */
    public function index(Request $request): View
    {
        $schoolId = $this->currentSchoolId();

        $subjectId = $request->input('subject_id');
        $status = $request->input('status');
        $search = $request->input('search');

        $query = Exam::query()
            ->with(['subject', 'teacher', 'classrooms'])
            ->withCount(['questionItems', 'attempts'])
            ->when($schoolId, fn ($q) => $q->forSchool($schoolId))
            ->when($subjectId, fn ($q) => $q->where('subject_id', (int) $subjectId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest('id');

        $exams = $query->paginate(15)->withQueryString();

        $subjects = Subject::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => Exam::when($schoolId, fn ($q) => $q->forSchool($schoolId))->count(),
            'published' => Exam::when($schoolId, fn ($q) => $q->forSchool($schoolId))->where('status', Exam::STATUS_PUBLISHED)->count(),
            'scheduled' => Exam::when($schoolId, fn ($q) => $q->forSchool($schoolId))->where('status', Exam::STATUS_SCHEDULED)->count(),
            'draft' => Exam::when($schoolId, fn ($q) => $q->forSchool($schoolId))->where('status', Exam::STATUS_DRAFT)->count(),
            'closed' => Exam::when($schoolId, fn ($q) => $q->forSchool($schoolId))->where('status', Exam::STATUS_CLOSED)->count(),
        ];

        $statuses = [
            Exam::STATUS_DRAFT => 'Draft',
            Exam::STATUS_SCHEDULED => 'Terjadwal',
            Exam::STATUS_PUBLISHED => 'Dipublikasikan',
            Exam::STATUS_CLOSED => 'Ditutup',
            Exam::STATUS_ARCHIVED => 'Diarsipkan',
        ];

        return view('cbt.exams.index', compact(
            'exams',
            'subjects',
            'stats',
            'statuses',
            'subjectId',
            'status',
            'search'
        ));
    }

    /**
     * Show the form for creating a new exam.
     */
    public function create(): View
    {
        $schoolId = $this->currentSchoolId();

        $activeAcademicYear = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->first();

        $subjects = Subject::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $classrooms = Classroom::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $statuses = [
            Exam::STATUS_DRAFT => 'Draft',
            Exam::STATUS_PUBLISHED => 'Dipublikasikan',
        ];

        $explanationPolicies = [
            Exam::EXPLANATION_AFTER_CLOSED => 'Setelah Ujian Ditutup',
            Exam::EXPLANATION_AFTER_SUBMIT => 'Segera Setelah Kumpul',
            Exam::EXPLANATION_NEVER => 'Tidak Pernah Ditampilkan',
        ];

        return view('cbt.exams.create', compact('subjects', 'classrooms', 'activeAcademicYear', 'statuses', 'explanationPolicies'));
    }

    /**
     * Store a newly created exam in storage.
     */
    public function store(StoreExamRequest $request): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();
        $validated = $request->validated();

        $academicYearId = ! empty($validated['academic_year_id'])
            ? (int) $validated['academic_year_id']
            : (int) School::current()?->activeAcademicYear?->id;

        $exam = DB::transaction(function () use ($schoolId, $academicYearId, $validated, $request) {
            $exam = Exam::create([
                'school_id' => $schoolId,
                'teacher_id' => auth()->id(),
                'subject_id' => (int) $validated['subject_id'],
                'academic_year_id' => $academicYearId ?: null,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'instructions' => $validated['instructions'] ?? null,
                'duration_minutes' => (int) $validated['duration_minutes'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'max_attempts' => (int) $validated['max_attempts'],
                'passing_grade' => $validated['passing_grade'] !== null ? (float) $validated['passing_grade'] : null,
                'status' => Exam::STATUS_DRAFT,
                'shuffle_questions' => $request->boolean('shuffle_questions', false),
                'shuffle_options' => $request->boolean('shuffle_options', false),
                'show_result_immediately' => $request->boolean('show_result_immediately', true),
                'show_explanation_policy' => $validated['show_explanation_policy'],
                'total_score' => 0.00,
            ]);

            $exam->classrooms()->sync($validated['classroom_ids']);

            return $exam;
        });

        return redirect()->route('exams.questions', $exam)
            ->with('success', 'Paket ujian berhasil dibuat dalam status Draf. Silakan pilih butir soal dari Bank Soal.');
    }

    /**
     * Display the specified exam details (redirects to questions manager).
     */
    public function show(Exam $exam): RedirectResponse
    {
        $this->authorizeTenant($exam);

        return redirect()->route('exams.questions', $exam);
    }

    /**
     * Show the form for editing the specified exam.
     */
    public function edit(Exam $exam): View
    {
        $this->authorizeTenant($exam);
        $schoolId = $this->currentSchoolId();

        $subjects = Subject::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $classrooms = Classroom::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedClassroomIds = $exam->classrooms()->pluck('classrooms.id')->toArray();

        $statuses = [
            Exam::STATUS_DRAFT => 'Draft',
            Exam::STATUS_SCHEDULED => 'Terjadwal',
            Exam::STATUS_PUBLISHED => 'Dipublikasikan',
            Exam::STATUS_CLOSED => 'Ditutup',
        ];

        $explanationPolicies = [
            Exam::EXPLANATION_AFTER_CLOSED => 'Setelah Ujian Ditutup',
            Exam::EXPLANATION_AFTER_SUBMIT => 'Segera Setelah Kumpul',
            Exam::EXPLANATION_NEVER => 'Tidak Pernah Ditampilkan',
        ];

        return view('cbt.exams.edit', compact('exam', 'subjects', 'classrooms', 'selectedClassroomIds', 'statuses', 'explanationPolicies'));
    }

    /**
     * Update the specified exam in storage.
     */
    public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
    {
        $this->authorizeTenant($exam);
        $validated = $request->validated();

        DB::transaction(function () use ($exam, $validated, $request) {
            $exam->update([
                'subject_id' => (int) $validated['subject_id'],
                'academic_year_id' => ! empty($validated['academic_year_id']) ? (int) $validated['academic_year_id'] : $exam->academic_year_id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'instructions' => $validated['instructions'] ?? null,
                'duration_minutes' => (int) $validated['duration_minutes'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'max_attempts' => (int) $validated['max_attempts'],
                'passing_grade' => $validated['passing_grade'] !== null ? (float) $validated['passing_grade'] : null,
                'shuffle_questions' => $request->boolean('shuffle_questions', false),
                'shuffle_options' => $request->boolean('shuffle_options', false),
                'show_result_immediately' => $request->boolean('show_result_immediately', true),
                'show_explanation_policy' => $validated['show_explanation_policy'],
            ]);

            $exam->classrooms()->sync($validated['classroom_ids']);
        });

        return redirect()->route('exams.index')
            ->with('success', 'Pengaturan ujian berhasil diperbarui.');
    }

    /**
     * Remove the specified exam.
     */
    public function destroy(Exam $exam): RedirectResponse
    {
        $this->authorizeTenant($exam);

        if ($exam->attempts()->exists()) {
            $exam->update(['status' => Exam::STATUS_ARCHIVED]);
            $msg = 'Ujian diarsipkan karena sudah memiliki riwayat pengerjaan siswa.';
        } else {
            $exam->delete();
            $msg = 'Paket ujian berhasil dihapus.';
        }

        return redirect()->route('exams.index')->with('success', $msg);
    }

    /**
     * Manage questions attached to this exam.
     */
    public function questions(Exam $exam): View
    {
        $this->authorizeTenant($exam);
        $schoolId = $this->currentSchoolId();

        $exam->load(['questionItems.question', 'subject']);

        $currentQuestionIds = $exam->questionItems()->pluck('question_id')->toArray();
        $currentItems = $exam->questionItems()->with('question')->get();

        // Bank questions for this subject that can be added
        $availableQuestions = ExamQuestion::query()
            ->when($schoolId, fn ($q) => $q->forSchool($schoolId))
            ->where('subject_id', $exam->subject_id)
            ->active()
            ->whereNotIn('id', $currentQuestionIds)
            ->latest('id')
            ->get();

        $errors = $exam->validateForPublishing();
        $validationStatus = [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];

        return view('cbt.exams.questions', compact('exam', 'currentItems', 'availableQuestions', 'validationStatus'));
    }

    /**
     * Attach questions from bank to this exam.
     */
    public function addQuestion(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeTenant($exam);

        $questionId = $request->input('question_id') ?? $request->input('exam_question_id');

        $question = ExamQuestion::findOrFail($questionId);
        if ((int) $question->school_id !== (int) $exam->school_id) {
            abort(403, 'Soal bukan milik sekolah ini.');
        }

        $nextOrder = (int) $exam->questionItems()->max('order') + 1;
        $score = $request->filled('score') ? (float) $request->input('score') : (float) $question->default_score;

        ExamQuestionItem::firstOrCreate(
            ['exam_id' => $exam->id, 'question_id' => $question->id],
            ['order' => $nextOrder, 'score' => $score]
        );

        $exam->recalculateTotalScore();

        return back()->with('success', 'Soal berhasil ditambahkan ke dalam paket ujian.');
    }

    /**
     * Remove question from this exam.
     */
    public function removeQuestion(Exam $exam, ExamQuestionItem $item): RedirectResponse
    {
        $this->authorizeTenant($exam);

        if ((int) $item->exam_id !== (int) $exam->id) {
            abort(403);
        }

        $item->delete();
        $exam->recalculateTotalScore();

        return back()->with('success', 'Soal berhasil dikeluarkan dari paket ujian.');
    }

    /**
     * Publish the exam after strict prerequisite validation.
     */
    public function publish(Exam $exam): RedirectResponse
    {
        $this->authorizeTenant($exam);

        $errors = $exam->validateForPublishing();
        if (! empty($errors)) {
            return back()->withErrors($errors);
        }

        $exam->recalculateTotalScore();
        $exam->update(['status' => Exam::STATUS_PUBLISHED]);

        return back()->with('success', 'Ujian berhasil dipublikasikan! Peserta di kelas terdaftar dapat mengakses ujian sesuai jadwal.');
    }

    /**
     * Close the exam manually.
     */
    public function close(Exam $exam): RedirectResponse
    {
        $this->authorizeTenant($exam);

        $exam->update(['status' => Exam::STATUS_CLOSED]);

        return back()->with('success', 'Ujian telah resmi ditutup.');
    }
}
