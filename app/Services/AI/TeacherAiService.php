<?php

namespace App\Services\AI;

use App\Enums\AiDraftStatus;
use App\Enums\AiFeatureType;
use App\Models\AiGenerationDraft;
use App\Models\ExamQuestion;
use App\Models\User;
use App\Services\AI\Contracts\LlmDriverInterface;
use App\Services\AI\Drivers\GeminiLlmDriver;
use App\Services\AI\Drivers\MockLlmDriver;
use Illuminate\Support\Facades\App;
use InvalidArgumentException;

class TeacherAiService
{
    protected LlmDriverInterface $driver;

    public function __construct(
        protected AiQuotaManager $quotaManager,
        protected PiiScrubber $piiScrubber,
        protected PromptInjectionShield $shield,
        ?LlmDriverInterface $driver = null
    ) {
        $this->driver = $driver ?: $this->resolveDefaultDriver();
    }

    /**
     * Resolve default LLM driver based on environment or configuration.
     */
    protected function resolveDefaultDriver(): LlmDriverInterface
    {
        $provider = config('services.ai.default_provider', env('AI_PROVIDER', 'gemini'));

        if ($provider === 'mock' || App::environment('testing')) {
            return new MockLlmDriver;
        }

        return new GeminiLlmDriver;
    }

    /**
     * Explicitly set driver (useful for tests).
     */
    public function setDriver(LlmDriverInterface $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * 1. Generate Lesson Plan / Modul Ajar (Kurikulum Merdeka / K13).
     *
     * @param  array{
     *     topic: string,
     *     learning_objectives: string,
     *     grade_level: string,
     *     duration_minutes?: int,
     *     subject_id?: int,
     *     grade_level_id?: int,
     *     model?: string
     * }  $params
     */
    public function generateLessonPlan(User $teacher, array $params, ?string $ipAddress = null, ?string $userAgent = null): AiGenerationDraft
    {
        $this->quotaManager->authorizeUsage($teacher, estimatedTokens: 2000);

        $cleanTopic = $this->piiScrubber->scrub($params['topic']);
        $cleanObjectives = $this->piiScrubber->scrub($params['learning_objectives']);
        $gradeLevel = $params['grade_level'] ?? 'Umum';
        $duration = $params['duration_minutes'] ?? 80;

        $systemPrompt = $this->shield->buildGuardedSystemPrompt(
            'Anda adalah Asisten Kurikulum Eduvia spesialis penyusunan Modul Ajar Kurikulum Merdeka Indonesia.',
            'Format output JSON wajib memiliki kunci: title (string), grade_level (string), duration (string), learning_objectives (array string), pancasila_profile (array string), learning_activities (object dengan keys pendahuluan, inti, penutup), assessment_method (string).'
        );

        $userCurriculum = <<<TXT
Topik / Materi: {$cleanTopic}
Tujuan / Capaian Pembelajaran: {$cleanObjectives}
Jenjang / Kelas: {$gradeLevel}
Alokasi Waktu: {$duration} menit
TXT;

        $userContent = "Buatkan Draf Modul Ajar terstruktur untuk:\n".$this->shield->wrapData($userCurriculum);

        $result = $this->driver->generateStructured($systemPrompt, $userContent, [
            'model' => $params['model'] ?? 'gemini-1.5-flash',
            'temperature' => 0.3,
        ]);

        $this->quotaManager->recordUsage(
            $teacher,
            AiFeatureType::LESSON_PLAN,
            $result['provider'],
            $result['model'],
            $result['prompt_tokens'],
            $result['completion_tokens'],
            $ipAddress,
            $userAgent
        );

        return AiGenerationDraft::create([
            'school_id' => $teacher->school_id,
            'teacher_id' => $teacher->id,
            'feature_type' => AiFeatureType::LESSON_PLAN,
            'subject_id' => $params['subject_id'] ?? null,
            'grade_level_id' => $params['grade_level_id'] ?? null,
            'title' => $result['content']['title'] ?? 'Draf Modul Ajar: '.$cleanTopic,
            'input_context' => [
                'topic' => $cleanTopic,
                'learning_objectives' => $cleanObjectives,
                'grade_level' => $gradeLevel,
                'duration_minutes' => $duration,
            ],
            'generated_content' => $result['content'],
            'status' => AiDraftStatus::DRAFT,
            'provider' => $result['provider'],
            'model_name' => $result['model'],
            'tokens_used' => $result['total_tokens'],
            'estimated_cost_idr' => ($result['total_tokens'] / 1000) * AiFeatureType::LESSON_PLAN->estimatedCostPer1kTokensIdr(),
        ]);
    }

    /**
     * 2. Generate Question Set for CBT Exam Bank.
     *
     * @param  array{
     *     topic: string,
     *     grade_level: string,
     *     count?: int,
     *     difficulty_distribution?: string, // e.g. "mudah: 2, sedang: 2, sukar: 1"
     *     question_types?: array<string>, // multiple_choice_single, true_false, essay
     *     subject_id?: int,
     *     grade_level_id?: int,
     *     model?: string
     * }  $params
     */
    public function generateQuestionSet(User $teacher, array $params, ?string $ipAddress = null, ?string $userAgent = null): AiGenerationDraft
    {
        $count = max(1, min(20, (int) ($params['count'] ?? 5)));
        $this->quotaManager->authorizeUsage($teacher, estimatedTokens: $count * 300);

        $cleanTopic = $this->piiScrubber->scrub($params['topic']);
        $gradeLevel = $params['grade_level'] ?? 'Umum';
        $difficulty = $params['difficulty_distribution'] ?? 'seimbang (mudah, sedang, sukar)';
        $types = ! empty($params['question_types']) ? implode(', ', $params['question_types']) : 'multiple_choice_single, true_false, essay';

        $systemPrompt = $this->shield->buildGuardedSystemPrompt(
            'Anda adalah Asisten Bank Soal Eduvia untuk guru Indonesia.',
            'Format output JSON wajib memiliki kunci: questions (array of objects). Setiap objek memiliki kunci: number (int), question_type (string: multiple_choice_single|true_false|essay), difficulty (string: mudah|sedang|sukar), content (string teks soal), options (array of {key, text} atau null untuk esai), correct_answer (string key jawaban atau pembahasan esai), default_score (float), explanation (string pembahasan fakta).'
        );

        $userCurriculum = <<<TXT
Materi Ujian: {$cleanTopic}
Tingkat Kelas: {$gradeLevel}
Jumlah Soal: {$count} butir
Distribusi Kesukaran: {$difficulty}
Tipe Soal yang Diminta: {$types}
TXT;

        $userContent = "Buatkan paket butir soal berjenjang lengkap dengan kunci dan pembahasan untuk:\n".$this->shield->wrapData($userCurriculum);

        $result = $this->driver->generateStructured($systemPrompt, $userContent, [
            'model' => $params['model'] ?? 'gemini-1.5-flash',
            'temperature' => 0.2,
        ]);

        $this->quotaManager->recordUsage(
            $teacher,
            AiFeatureType::QUESTION_SET,
            $result['provider'],
            $result['model'],
            $result['prompt_tokens'],
            $result['completion_tokens'],
            $ipAddress,
            $userAgent
        );

        return AiGenerationDraft::create([
            'school_id' => $teacher->school_id,
            'teacher_id' => $teacher->id,
            'feature_type' => AiFeatureType::QUESTION_SET,
            'subject_id' => $params['subject_id'] ?? null,
            'grade_level_id' => $params['grade_level_id'] ?? null,
            'title' => "Draf Paket Soal ({$count} butir): {$cleanTopic}",
            'input_context' => [
                'topic' => $cleanTopic,
                'grade_level' => $gradeLevel,
                'count' => $count,
                'difficulty' => $difficulty,
                'types' => $types,
            ],
            'generated_content' => $result['content'],
            'status' => AiDraftStatus::DRAFT,
            'provider' => $result['provider'],
            'model_name' => $result['model'],
            'tokens_used' => $result['total_tokens'],
            'estimated_cost_idr' => ($result['total_tokens'] / 1000) * AiFeatureType::QUESTION_SET->estimatedCostPer1kTokensIdr(),
        ]);
    }

    /**
     * 3. Generate Grading Rubric.
     *
     * @param  array{
     *     task_title: string,
     *     learning_goals: string,
     *     scale?: string, // 1-4 or 1-5
     *     subject_id?: int,
     *     grade_level_id?: int,
     *     model?: string
     * }  $params
     */
    public function generateGradingRubric(User $teacher, array $params, ?string $ipAddress = null, ?string $userAgent = null): AiGenerationDraft
    {
        $this->quotaManager->authorizeUsage($teacher, estimatedTokens: 1500);

        $cleanTask = $this->piiScrubber->scrub($params['task_title']);
        $cleanGoals = $this->piiScrubber->scrub($params['learning_goals']);
        $scale = $params['scale'] ?? '1-4 (Sangat Baik, Baik, Cukup, Perlu Bimbingan)';

        $systemPrompt = $this->shield->buildGuardedSystemPrompt(
            'Anda adalah Asisten Evaluasi Pembelajaran Eduvia untuk guru Indonesia.',
            'Format output JSON wajib memiliki kunci: title (string), criteria (array of objects). Setiap kriteria memiliki kunci: name (string), weight_percent (int), levels (array of {score: int, label: string, description: string}). Total weight_percent seluruh kriteria wajib berjumlah 100.'
        );

        $userCurriculum = <<<TXT
Judul Tugas / Proyek: {$cleanTask}
Tujuan Pembelajaran: {$cleanGoals}
Skala Skor: {$scale}
TXT;

        $userContent = "Buatkan rubrik penilaian autentik terukur untuk:\n".$this->shield->wrapData($userCurriculum);

        $result = $this->driver->generateStructured($systemPrompt, $userContent, [
            'model' => $params['model'] ?? 'gemini-1.5-flash',
            'temperature' => 0.2,
        ]);

        $this->quotaManager->recordUsage(
            $teacher,
            AiFeatureType::GRADING_RUBRIC,
            $result['provider'],
            $result['model'],
            $result['prompt_tokens'],
            $result['completion_tokens'],
            $ipAddress,
            $userAgent
        );

        return AiGenerationDraft::create([
            'school_id' => $teacher->school_id,
            'teacher_id' => $teacher->id,
            'feature_type' => AiFeatureType::GRADING_RUBRIC,
            'subject_id' => $params['subject_id'] ?? null,
            'grade_level_id' => $params['grade_level_id'] ?? null,
            'title' => 'Rubrik Penilaian: '.$cleanTask,
            'input_context' => [
                'task_title' => $cleanTask,
                'learning_goals' => $cleanGoals,
                'scale' => $scale,
            ],
            'generated_content' => $result['content'],
            'status' => AiDraftStatus::DRAFT,
            'provider' => $result['provider'],
            'model_name' => $result['model'],
            'tokens_used' => $result['total_tokens'],
            'estimated_cost_idr' => ($result['total_tokens'] / 1000) * AiFeatureType::GRADING_RUBRIC->estimatedCostPer1kTokensIdr(),
        ]);
    }

    /**
     * 4. Generate Feedback Suggestions for Essay / Assignment.
     *
     * @param  array{
     *     assignment_title: string,
     *     essay_text: string,
     *     prompt_instructions?: string,
     *     student_pseudo_id?: string, // NEVER send real name/NISN
     *     model?: string
     * }  $params
     */
    public function generateEssayFeedback(User $teacher, array $params, ?string $ipAddress = null, ?string $userAgent = null): AiGenerationDraft
    {
        $this->quotaManager->authorizeUsage($teacher, estimatedTokens: 1800);

        $cleanTitle = $this->piiScrubber->scrub($params['assignment_title']);
        $cleanEssay = $this->piiScrubber->scrub($params['essay_text']);
        $pseudoId = $params['student_pseudo_id'] ?? 'Peserta Didik #'.rand(100, 999);

        $systemPrompt = $this->shield->buildGuardedSystemPrompt(
            'Anda adalah Asisten Pemeriksa Tugas Esai Eduvia untuk memberikan saran umpan balik konstruktif bagi guru.',
            'Format output JSON wajib memiliki kunci: observations (array string), strengths (array string), areas_for_improvement (array string), suggested_feedback_text (string umpan balik hangat & menyemangati untuk siswa), suggested_score_range (string rentang perkiraan skor e.g. "80 - 85").'
        );

        $userCurriculum = <<<TXT
Judul Tugas: {$cleanTitle}
Identitas Penulis: {$pseudoId} (Anonim)
Isi Esai Siswa:
{$cleanEssay}
TXT;

        $userContent = "Berikan analisis dan draf saran umpan balik konstruktif untuk tugas siswa berikut:\n".$this->shield->wrapData($userCurriculum);

        $result = $this->driver->generateStructured($systemPrompt, $userContent, [
            'model' => $params['model'] ?? 'gemini-1.5-flash',
            'temperature' => 0.3,
        ]);

        $this->quotaManager->recordUsage(
            $teacher,
            AiFeatureType::ESSAY_FEEDBACK,
            $result['provider'],
            $result['model'],
            $result['prompt_tokens'],
            $result['completion_tokens'],
            $ipAddress,
            $userAgent
        );

        return AiGenerationDraft::create([
            'school_id' => $teacher->school_id,
            'teacher_id' => $teacher->id,
            'feature_type' => AiFeatureType::ESSAY_FEEDBACK,
            'title' => 'Saran Umpan Balik: '.$cleanTitle,
            'input_context' => [
                'assignment_title' => $cleanTitle,
                'student_pseudonym' => $pseudoId,
                'essay_text_scrubbed' => $cleanEssay,
            ],
            'generated_content' => $result['content'],
            'status' => AiDraftStatus::DRAFT,
            'provider' => $result['provider'],
            'model_name' => $result['model'],
            'tokens_used' => $result['total_tokens'],
            'estimated_cost_idr' => ($result['total_tokens'] / 1000) * AiFeatureType::ESSAY_FEEDBACK->estimatedCostPer1kTokensIdr(),
        ]);
    }

    /**
     * Import an approved question set draft into the official CBT ExamQuestion bank.
     *
     * @return array<ExamQuestion>
     */
    public function importQuestionsToCbt(AiGenerationDraft $draft, ?int $subjectId = null, ?int $gradeLevelId = null): array
    {
        if ($draft->feature_type !== AiFeatureType::QUESTION_SET) {
            throw new InvalidArgumentException('Draf ini bukan merupakan paket soal ujian.');
        }

        $activeContent = $draft->active_content;
        $questions = $activeContent['questions'] ?? [];
        if (empty($questions)) {
            throw new InvalidArgumentException('Tidak ada butir soal yang ditemukan di dalam draf.');
        }

        $targetSubjectId = $subjectId ?? $draft->subject_id;
        if (! $targetSubjectId) {
            throw new InvalidArgumentException('Mata pelajaran wajib dipilih sebelum mengimpor soal ke bank soal CBT.');
        }

        $createdQuestions = [];
        foreach ($questions as $q) {
            $questionType = $q['question_type'] ?? 'multiple_choice_single';
            $options = $q['options'] ?? null;
            $correct = $q['correct_answer'] ?? null;

            $examQuestion = ExamQuestion::create([
                'school_id' => $draft->school_id,
                'created_by' => $draft->teacher_id,
                'subject_id' => $targetSubjectId,
                'grade_level_id' => $gradeLevelId ?? $draft->grade_level_id,
                'question_type' => $questionType,
                'content' => $q['content'] ?? $q['question_text'] ?? '',
                'options' => $options,
                'correct_answer' => $correct,
                'default_score' => (float) ($q['default_score'] ?? $q['score'] ?? 10.0),
                'explanation' => $q['explanation'] ?? null,
                'is_active' => true,
            ]);

            $createdQuestions[] = $examQuestion;
        }

        // Mark draft as accepted
        $draft->accept(destinationType: 'exam_questions', destinationId: count($createdQuestions));

        return $createdQuestions;
    }
}
