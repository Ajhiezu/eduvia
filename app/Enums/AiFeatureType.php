<?php

namespace App\Enums;

enum AiFeatureType: string
{
    case LESSON_PLAN = 'lesson_plan';
    case QUESTION_SET = 'question_set';
    case GRADING_RUBRIC = 'grading_rubric';
    case ESSAY_FEEDBACK = 'essay_feedback';

    /**
     * Get user-friendly label.
     */
    public function label(): string
    {
        return match ($this) {
            self::LESSON_PLAN => 'Modul Ajar / RPP',
            self::QUESTION_SET => 'Generator Soal Ujian',
            self::GRADING_RUBRIC => 'Rubrik Penilaian',
            self::ESSAY_FEEDBACK => 'Umpan Balik Tugas Esai',
        };
    }

    /**
     * Approximate token cost estimate in IDR (per 1k tokens).
     */
    public function estimatedCostPer1kTokensIdr(): float
    {
        // Based on Gemini 1.5 Flash input/output blend ~Rp 1.50 per 1k tokens
        return 1.50;
    }
}
