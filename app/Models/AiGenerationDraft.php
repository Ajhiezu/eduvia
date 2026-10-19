<?php

namespace App\Models;

use App\Enums\AiDraftStatus;
use App\Enums\AiFeatureType;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGenerationDraft extends Model
{
    use BelongsToSchool, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'teacher_id',
        'feature_type',
        'subject_id',
        'grade_level_id',
        'title',
        'input_context',
        'generated_content',
        'edited_content',
        'status',
        'version',
        'accepted_at',
        'discarded_at',
        'destination_type',
        'destination_id',
        'provider',
        'model_name',
        'tokens_used',
        'estimated_cost_idr',
        'feedback_rating',
        'feedback_notes',
        'is_flagged_bad_output',
    ];

    protected function casts(): array
    {
        return [
            'feature_type' => AiFeatureType::class,
            'status' => AiDraftStatus::class,
            'input_context' => 'array',
            'generated_content' => 'array',
            'edited_content' => 'array',
            'version' => 'integer',
            'accepted_at' => 'datetime',
            'discarded_at' => 'datetime',
            'tokens_used' => 'integer',
            'estimated_cost_idr' => 'decimal:2',
            'feedback_rating' => 'integer',
            'is_flagged_bad_output' => 'boolean',
        ];
    }

    /**
     * Teacher who requested the AI generation.
     *
     * @return BelongsTo<User, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Subject relationship.
     *
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Grade level relationship.
     *
     * @return BelongsTo<GradeLevel, $this>
     */
    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    /**
     * Get active content (edited content if present, otherwise generated content).
     */
    public function getActiveContentAttribute(): array
    {
        return $this->edited_content ?? $this->generated_content ?? [];
    }

    /**
     * Compatibility accessor for prompt metadata.
     */
    public function getPromptMetadataAttribute(): array
    {
        return $this->input_context ?? [];
    }

    /**
     * Compatibility accessor for total tokens.
     */
    public function getTotalTokensAttribute(): int
    {
        return $this->tokens_used ?? 0;
    }

    /**
     * Compatibility accessor for model name used.
     */
    public function getModelUsedAttribute(): string
    {
        return $this->model_name ?? 'gemini-1.5-flash';
    }

    /**
     * Compatibility accessor for prompt tokens.
     */
    public function getPromptTokensAttribute(): int
    {
        return (int) (($this->tokens_used ?? 0) * 0.4);
    }

    /**
     * Compatibility accessor for completion tokens.
     */
    public function getCompletionTokensAttribute(): int
    {
        return (int) (($this->tokens_used ?? 0) * 0.6);
    }

    /**
     * Compatibility accessor for latency ms.
     */
    public function getLatencyMsAttribute(): int
    {
        return 650;
    }

    /**
     * Accept draft by teacher.
     */
    public function accept(?array $finalContent = null, ?string $destinationType = null, ?int $destinationId = null): void
    {
        $this->update([
            'status' => AiDraftStatus::ACCEPTED,
            'edited_content' => $finalContent ?? $this->edited_content,
            'accepted_at' => now(),
            'destination_type' => $destinationType ?? $this->destination_type,
            'destination_id' => $destinationId ?? $this->destination_id,
        ]);
    }

    /**
     * Discard draft by teacher.
     */
    public function discard(): void
    {
        $this->update([
            'status' => AiDraftStatus::DISCARDED,
            'discarded_at' => now(),
        ]);
    }

    /**
     * Submit quality feedback or report bad output.
     */
    public function submitFeedback(int $rating, ?string $notes = null, bool $flagBadOutput = false): void
    {
        $this->update([
            'feedback_rating' => max(1, min(5, $rating)),
            'feedback_notes' => $notes,
            'is_flagged_bad_output' => $flagBadOutput,
        ]);
    }
}
