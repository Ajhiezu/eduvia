<?php

namespace App\Http\Controllers;

use App\Http\Requests\Exam\StoreExamQuestionRequest;
use App\Http\Requests\Exam\UpdateExamQuestionRequest;
use App\Models\ExamQuestion;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamQuestionController extends Controller
{
    /**
     * Resolve the active school ID for current context.
     */
    protected function currentSchoolId(): ?int
    {
        return auth()->user()?->school_id ?? School::current()?->id;
    }

    /**
     * Ensure the question belongs to the current school tenant.
     */
    protected function authorizeTenant(ExamQuestion $question): void
    {
        $schoolId = $this->currentSchoolId();
        if ($schoolId && (int) $question->school_id !== (int) $schoolId) {
            abort(403, 'Akses ditolak: Soal ini tidak terdaftar pada sekolah Anda.');
        }
    }

    /**
     * Display a listing of questions in the question bank.
     */
    public function index(Request $request): View
    {
        $schoolId = $this->currentSchoolId();

        $subjectId = $request->input('subject_id');
        $type = $request->input('type') ?? $request->input('question_type');
        $status = $request->input('status');
        $search = $request->input('search');

        $query = ExamQuestion::query()
            ->with(['subject', 'gradeLevel', 'creator'])
            ->withCount('exams')
            ->when($schoolId, fn ($q) => $q->forSchool($schoolId))
            ->when($subjectId, fn ($q) => $q->forSubject((int) $subjectId))
            ->when($type, fn ($q) => $q->forType($type))
            ->when($status !== null && $status !== '', fn ($q) => $q->where('is_active', (bool) $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('content', 'like', "%{$search}%")
                        ->orWhere('explanation', 'like', "%{$search}%");
                });
            })
            ->latest('id');

        $questions = $query->paginate(15)->withQueryString();

        $subjects = Subject::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $types = ExamQuestion::TYPES;

        $stats = [
            'total' => ExamQuestion::when($schoolId, fn ($q) => $q->forSchool($schoolId))->count(),
            'multiple_choice_single' => ExamQuestion::when($schoolId, fn ($q) => $q->forSchool($schoolId))->where('question_type', ExamQuestion::TYPE_MC_SINGLE)->count(),
            'multiple_choice_multi' => ExamQuestion::when($schoolId, fn ($q) => $q->forSchool($schoolId))->where('question_type', ExamQuestion::TYPE_MC_MULTI)->count(),
            'true_false' => ExamQuestion::when($schoolId, fn ($q) => $q->forSchool($schoolId))->where('question_type', ExamQuestion::TYPE_TRUE_FALSE)->count(),
            'short_answer' => ExamQuestion::when($schoolId, fn ($q) => $q->forSchool($schoolId))->where('question_type', ExamQuestion::TYPE_SHORT_ANSWER)->count(),
            'essay' => ExamQuestion::when($schoolId, fn ($q) => $q->forSchool($schoolId))->where('question_type', ExamQuestion::TYPE_ESSAY)->count(),
        ];

        return view('cbt.questions.index', compact(
            'questions',
            'subjects',
            'types',
            'stats',
            'subjectId',
            'type',
            'status',
            'search'
        ));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create(Request $request): View
    {
        $schoolId = $this->currentSchoolId();

        $subjects = Subject::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $gradeLevels = GradeLevel::active()->orderBy('order')->get();
        $types = ExamQuestion::TYPES;
        $selectedSubjectId = $request->input('subject_id');

        return view('cbt.questions.create', compact('subjects', 'gradeLevels', 'types', 'selectedSubjectId'));
    }

    /**
     * Store a newly created question in the bank.
     */
    public function store(StoreExamQuestionRequest $request): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();
        $validated = $request->validated();

        $type = $validated['question_type'];
        $options = $validated['options'] ?? null;
        $correct = $validated['correct_answer'] ?? null;

        // Clean options for multiple choice & true/false
        if (in_array($type, [ExamQuestion::TYPE_MC_SINGLE, ExamQuestion::TYPE_MC_MULTI], true) && is_array($options)) {
            $cleaned = [];
            foreach ($options as $opt) {
                if (! empty($opt['text']) || ! empty($opt['key'])) {
                    $cleaned[] = [
                        'key' => trim((string) ($opt['key'] ?? '')),
                        'text' => trim((string) ($opt['text'] ?? '')),
                    ];
                }
            }
            $options = $cleaned;
        } elseif ($type === ExamQuestion::TYPE_TRUE_FALSE) {
            $options = [
                ['key' => 'true', 'text' => 'Benar'],
                ['key' => 'false', 'text' => 'Salah'],
            ];
        } else {
            $options = null;
        }

        // Normalize correct answer format
        if ($type === ExamQuestion::TYPE_MC_MULTI && is_array($correct)) {
            $correct = array_values(array_map('strval', $correct));
        } elseif ($type === ExamQuestion::TYPE_SHORT_ANSWER && is_string($correct)) {
            // Split by comma or semicolon for multiple accepted alternative answers
            $keywords = preg_split('/[,;]+/', $correct);
            $correct = array_values(array_filter(array_map('trim', $keywords)));
        } elseif ($correct !== null) {
            $correct = [(string) $correct];
        }

        ExamQuestion::create([
            'school_id' => $schoolId,
            'created_by' => auth()->id(),
            'subject_id' => (int) $validated['subject_id'],
            'grade_level_id' => ! empty($validated['grade_level_id']) ? (int) $validated['grade_level_id'] : null,
            'question_type' => $type,
            'content' => $validated['content'],
            'options' => $options,
            'correct_answer' => $correct,
            'default_score' => (float) $validated['default_score'],
            'explanation' => $validated['explanation'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('exam-questions.index', ['subject_id' => $validated['subject_id']])
            ->with('success', 'Butir soal baru berhasil disimpan ke dalam Bank Soal.');
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(ExamQuestion $question): View
    {
        $this->authorizeTenant($question);
        $schoolId = $this->currentSchoolId();

        $subjects = Subject::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $gradeLevels = GradeLevel::active()->orderBy('order')->get();
        $types = ExamQuestion::TYPES;

        return view('cbt.questions.edit', compact('question', 'subjects', 'gradeLevels', 'types'));
    }

    /**
     * Update the specified question in storage.
     */
    public function update(UpdateExamQuestionRequest $request, ExamQuestion $question): RedirectResponse
    {
        $this->authorizeTenant($question);
        $validated = $request->validated();

        $type = $validated['question_type'];
        $options = $validated['options'] ?? null;
        $correct = $validated['correct_answer'] ?? null;

        if (in_array($type, [ExamQuestion::TYPE_MC_SINGLE, ExamQuestion::TYPE_MC_MULTI], true) && is_array($options)) {
            $cleaned = [];
            foreach ($options as $opt) {
                if (! empty($opt['text']) || ! empty($opt['key'])) {
                    $cleaned[] = [
                        'key' => trim((string) ($opt['key'] ?? '')),
                        'text' => trim((string) ($opt['text'] ?? '')),
                    ];
                }
            }
            $options = $cleaned;
        } elseif ($type === ExamQuestion::TYPE_TRUE_FALSE) {
            $options = [
                ['key' => 'true', 'text' => 'Benar'],
                ['key' => 'false', 'text' => 'Salah'],
            ];
        } else {
            $options = null;
        }

        if ($type === ExamQuestion::TYPE_MC_MULTI && is_array($correct)) {
            $correct = array_values(array_map('strval', $correct));
        } elseif ($type === ExamQuestion::TYPE_SHORT_ANSWER && is_string($correct)) {
            $keywords = preg_split('/[,;]+/', $correct);
            $correct = array_values(array_filter(array_map('trim', $keywords)));
        } elseif ($correct !== null) {
            $correct = [(string) $correct];
        }

        $question->update([
            'subject_id' => (int) $validated['subject_id'],
            'grade_level_id' => ! empty($validated['grade_level_id']) ? (int) $validated['grade_level_id'] : null,
            'question_type' => $type,
            'content' => $validated['content'],
            'options' => $options,
            'correct_answer' => $correct,
            'default_score' => (float) $validated['default_score'],
            'explanation' => $validated['explanation'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('exam-questions.index', ['subject_id' => $validated['subject_id']])
            ->with('success', 'Butir soal berhasil diperbarui.');
    }

    /**
     * Remove or archive the specified question.
     */
    public function destroy(ExamQuestion $question): RedirectResponse
    {
        $this->authorizeTenant($question);

        // If question is used in any exam, soft-archive to prevent breaking historical records
        if ($question->examItems()->exists()) {
            $question->update(['is_active' => false]);
            $msg = 'Soal dinonaktifkan (diarsipkan) karena telah digunakan pada paket ujian.';
        } else {
            $question->delete();
            $msg = 'Soal berhasil dihapus dari Bank Soal.';
        }

        return back()->with('success', $msg);
    }

    /**
     * Duplicate a question for fast variant creation.
     */
    public function duplicate(ExamQuestion $question): RedirectResponse
    {
        $this->authorizeTenant($question);

        $replica = $question->replicate();
        $replica->content = '[Salinan] '.$question->content;
        $replica->created_by = auth()->id();
        $replica->save();

        return redirect()->route('exam-questions.edit', $replica)
            ->with('success', 'Soal berhasil diduplikasi. Silakan sesuaikan isi pertanyaan dan jawaban.');
    }
}
