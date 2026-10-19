<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use BelongsToSchool, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'code',
        'name',
        'group',
        'curriculum',
        'educational_level',
        'credit_hours',
        'academic_program',
        'order',
        'is_active',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'order' => 'integer',
        'credit_hours' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * School relationship.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Teaching assignments relationship.
     */
    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    /**
     * Scope for active subjects.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered subjects.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('name');
    }

    /**
     * Scope for curriculum.
     */
    public function scopeCurriculum(Builder $query, ?string $curriculum): Builder
    {
        return $curriculum ? $query->where('curriculum', $curriculum) : $query;
    }

    /**
     * Scope for educational level.
     */
    public function scopeEducationalLevel(Builder $query, ?string $level): Builder
    {
        return $level ? $query->where('educational_level', $level) : $query;
    }
}
