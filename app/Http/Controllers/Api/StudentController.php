<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\LearningMaterial;
use App\Models\LessonSchedule;
use App\Models\SchoolAnnouncement;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    use ApiResponse;

    /**
     * Get student dashboard overview with real DB data.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $classroom = $user->classroom;
        $profile = $user->studentProfile;
        $now = Carbon::now();
        $todayIso = $now->dayOfWeekIso; // 1 (Mon) - 7 (Sun)

        $classroomId = $classroom?->id;

        // 1. Schedules today
        $todaySchedules = $classroomId ? LessonSchedule::where('is_active', true)
            ->where('day_of_week', $todayIso)
            ->whereHas('teachingAssignment', fn ($q) => $q->where('classroom_id', $classroomId))
            ->with(['teachingAssignment.subject', 'teachingAssignment.teacher'])
            ->orderBy('start_time')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'subject' => $s->teachingAssignment?->subject?->name ?? 'Mata Pelajaran',
                'teacher' => $s->teachingAssignment?->teacher?->name ?? 'Guru Pengampu',
                'time' => $s->timeRange(),
                'room' => $s->room ?? 'Kelas',
                'is_ongoing' => $s->isOngoing($now),
            ]) : collect();

        // 2. Active assignments count
        $activeAssignmentsCount = $classroomId ? Assignment::where('classroom_id', $classroomId)
            ->where('status', 'published')
            ->where('due_date', '>=', $now)
            ->count() : 0;

        // 3. Available CBT exams count
        $schoolId = $user->school_id ?? 1;
        $activeExamsCount = Exam::where('status', 'published')
            ->where(function ($q) use ($schoolId) {
                if ($schoolId) {
                    $q->where('school_id', $schoolId);
                }
            })
            ->count();

        // 4. Recent announcements
        $announcements = SchoolAnnouncement::where('status', 'published')
            ->whereIn('target_audience', ['all', 'students'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->take(3)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'category' => $a->category,
                'content' => $a->content,
                'date' => $a->published_at ? $a->published_at->translatedFormat('d M Y') : $a->created_at->translatedFormat('d M Y'),
                'is_pinned' => (bool) $a->is_pinned,
            ]);

        return $this->apiSuccess(
            data: [
                'student' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'nis' => $profile?->nis ?? '-',
                    'nisn' => $profile?->nisn ?? '0081234567',
                    'classroom' => $classroom?->name ?? 'VII-A',
                    'classroom_code' => $classroom?->code ?? 'CLS-7A',
                ],
                'metrics' => [
                    'today_schedules_count' => $todaySchedules->count(),
                    'active_assignments_count' => $activeAssignmentsCount,
                    'active_exams_count' => $activeExamsCount,
                ],
                'today_schedules' => $todaySchedules,
                'announcements' => $announcements,
            ],
            message: 'Dashboard siswa berhasil dimuat.'
        );
    }

    /**
     * Get weekly lesson schedules.
     */
    public function schedules(Request $request): JsonResponse
    {
        $user = $request->user();
        $classroomId = $user->classroom?->id;

        $schedules = $classroomId ? LessonSchedule::where('is_active', true)
            ->whereHas('teachingAssignment', fn ($q) => $q->where('classroom_id', $classroomId))
            ->with(['teachingAssignment.subject', 'teachingAssignment.teacher'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week')
            ->map(function ($items, $day) {
                return [
                    'day_number' => (int) $day,
                    'day_name' => LessonSchedule::DAYS[(int) $day] ?? '-',
                    'lessons' => $items->map(fn ($s) => [
                        'id' => $s->id,
                        'subject' => $s->teachingAssignment?->subject?->name ?? 'Mata Pelajaran',
                        'teacher' => $s->teachingAssignment?->teacher?->name ?? 'Guru',
                        'time' => $s->timeRange(),
                        'room' => $s->room ?? 'Kelas',
                    ]),
                ];
            })
            ->values() : collect();

        return $this->apiSuccess(
            data: $schedules,
            message: 'Jadwal pelajaran berhasil dimuat.'
        );
    }

    /**
     * Get learning materials for student.
     */
    public function materials(Request $request): JsonResponse
    {
        $user = $request->user();
        $classroomId = $user->classroom?->id;

        $materials = $classroomId ? LearningMaterial::where('classroom_id', $classroomId)
            ->where('status', 'published')
            ->with(['subject', 'teacher'])
            ->orderByDesc('published_at')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'title' => $m->title,
                'topic' => $m->topic ?? 'Umum',
                'subject' => $m->subject?->name ?? 'Mata Pelajaran',
                'teacher' => $m->teacher?->name ?? 'Guru',
                'description' => $m->description ?? '',
                'date' => $m->published_at ? $m->published_at->translatedFormat('d M Y') : $m->created_at->translatedFormat('d M Y'),
            ]) : collect();

        return $this->apiSuccess(
            data: $materials,
            message: 'Materi belajar berhasil dimuat.'
        );
    }

    /**
     * Get assignments list.
     */
    public function assignments(Request $request): JsonResponse
    {
        $user = $request->user();
        $classroomId = $user->classroom?->id;

        $assignments = $classroomId ? Assignment::where('classroom_id', $classroomId)
            ->where('status', 'published')
            ->with(['subject', 'teacher', 'submissions' => fn ($q) => $q->where('student_id', $user->id)])
            ->orderBy('due_date')
            ->get()
            ->map(function ($a) {
                $submission = $a->submissions->first();

                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'topic' => $a->topic ?? 'Tugas',
                    'subject' => $a->subject?->name ?? 'Mata Pelajaran',
                    'teacher' => $a->teacher?->name ?? 'Guru',
                    'due_date' => Carbon::parse($a->due_date)->translatedFormat('d M Y, H:i').' WIB',
                    'is_overdue' => Carbon::parse($a->due_date)->isPast() && ! $submission,
                    'max_score' => $a->max_score,
                    'instructions' => $a->instructions,
                    'status' => $submission ? ($submission->score !== null ? 'Dinilai' : 'Terkirim') : 'Belum Selesai',
                    'score' => $submission?->score,
                ];
            }) : collect();

        return $this->apiSuccess(
            data: $assignments,
            message: 'Daftar tugas berhasil dimuat.'
        );
    }

    /**
     * Get CBT exams available.
     */
    public function cbt(Request $request): JsonResponse
    {
        $schoolId = $request->user()->school_id ?? 1;

        $exams = Exam::where('status', 'published')
            ->where(function ($q) use ($schoolId) {
                if ($schoolId) {
                    $q->where('school_id', $schoolId);
                }
            })
            ->with(['subject', 'questions'])
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'subject' => $e->subject?->name ?? 'Mata Pelajaran',
                'duration_minutes' => $e->duration_minutes ?? 60,
                'total_questions' => $e->questions->count(),
                'status' => 'Siap Dikerjakan',
            ]);

        return $this->apiSuccess(
            data: $exams,
            message: 'Daftar ujian CBT berhasil dimuat.'
        );
    }

    /**
     * Start CBT exam session or resume existing attempt.
     */
    public function startCbt(Request $request, Exam $exam): JsonResponse
    {
        $user = $request->user();

        // Check if there is an in-progress attempt
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $user->id)
            ->where('status', ExamAttempt::STATUS_IN_PROGRESS)
            ->first();

        if (! $attempt) {
            $durationMinutes = $exam->duration_minutes ?: 60;
            $attemptNumber = (int) (ExamAttempt::where('exam_id', $exam->id)->where('student_id', $user->id)->max('attempt_number') ?? 0) + 1;
            $snapshot = $exam->buildFrozenSnapshot();

            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $user->id,
                'attempt_number' => $attemptNumber,
                'started_at' => now(),
                'expires_at' => now()->addMinutes($durationMinutes),
                'questions_snapshot' => $snapshot,
                'status' => ExamAttempt::STATUS_IN_PROGRESS,
            ]);
        }

        // Check if already expired
        if ($attempt->isExpired()) {
            $attempt->update(['status' => ExamAttempt::STATUS_EXPIRED]);

            return $this->apiError('Waktu ujian telah habis.', 400);
        }

        // Load questions without revealing correct answer!
        $questions = $exam->questionItems()->with('question')->get()->map(function ($item, $idx) use ($attempt) {
            $q = $item->question;
            $existingAnswer = ExamAnswer::where('exam_attempt_id', $attempt->id)
                ->where('question_id', $q->id)
                ->first();

            return [
                'id' => $q->id,
                'number' => $idx + 1,
                'type' => $q->question_type,
                'content' => $q->content,
                'options' => $q->options,
                'user_answer' => $existingAnswer?->answer,
                'is_flagged' => (bool) $existingAnswer?->is_marked_for_review,
            ];
        });

        return $this->apiSuccess(
            data: [
                'attempt_id' => $attempt->id,
                'exam_id' => $exam->id,
                'title' => $exam->title,
                'duration_minutes' => $exam->duration_minutes,
                'remaining_seconds' => $attempt->remainingSeconds(),
                'expires_at' => $attempt->expires_at->toIso8601String(),
                'questions' => $questions,
            ],
            message: 'Sesi ujian berhasil dimulai.'
        );
    }

    /**
     * Save single answer during CBT (autosave).
     */
    public function saveCbtAnswer(Request $request, Exam $exam): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'attempt_id' => 'required|integer',
            'question_id' => 'required|integer',
            'answer' => 'nullable',
            'is_flagged' => 'nullable|boolean',
        ]);

        $attempt = ExamAttempt::where('id', $validated['attempt_id'])
            ->where('exam_id', $exam->id)
            ->where('student_id', $user->id)
            ->firstOrFail();

        if (! $attempt->isInProgress() || $attempt->isExpired()) {
            return $this->apiError('Sesi ujian tidak aktif atau telah berakhir.', 400);
        }

        ExamAnswer::updateOrCreate(
            [
                'exam_attempt_id' => $attempt->id,
                'question_id' => $validated['question_id'],
            ],
            [
                'answer' => $validated['answer'],
                'is_marked_for_review' => $validated['is_flagged'] ?? false,
                'saved_at' => now(),
            ]
        );

        return $this->apiSuccess(
            data: [
                'remaining_seconds' => $attempt->remainingSeconds(),
            ],
            message: 'Jawaban berhasil disimpan.'
        );
    }

    /**
     * Finalize / Submit CBT Exam.
     */
    public function finishCbt(Request $request, Exam $exam): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'attempt_id' => 'required|integer',
        ]);

        $attempt = ExamAttempt::where('id', $validated['attempt_id'])
            ->where('exam_id', $exam->id)
            ->where('student_id', $user->id)
            ->firstOrFail();

        if ($attempt->isSubmitted()) {
            return $this->apiSuccess(
                data: [
                    'status' => $attempt->status,
                    'total_score' => $attempt->total_score,
                    'final_percentage' => $attempt->final_percentage,
                ],
                message: 'Ujian telah dikumpulkan sebelumnya.'
            );
        }

        // Auto-grade objective questions
        $answers = $attempt->answers()->with('question')->get();
        foreach ($answers as $ans) {
            $questionItem = $exam->questionItems()->where('question_id', $ans->question_id)->first();
            $maxScore = (float) ($questionItem?->score ?? 10.0);
            $ans->autoGrade($maxScore);
        }

        $attempt->submitted_at = now();
        $attempt->save();
        $attempt->calculateFinalGrade();

        return $this->apiSuccess(
            data: [
                'status' => $attempt->status,
                'total_score' => $attempt->total_score,
                'final_percentage' => $attempt->final_percentage,
                'is_passed' => $attempt->is_passed,
            ],
            message: 'Ujian berhasil dikumpulkan.'
        );
    }
}
