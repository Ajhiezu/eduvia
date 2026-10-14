<?php

namespace App\Models;

use Database\Factories\GradeLevelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['educational_level', 'level_number', 'name', 'order', 'is_active'])]
class GradeLevel extends Model
{
    /** @use HasFactory<GradeLevelFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level_number' => 'integer',
            'order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope query to active grade levels.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query by educational level (sd, smp, sma, smk).
     */
    public function scopeForLevel(Builder $query, string $level): Builder
    {
        return $query->where('educational_level', strtolower($level));
    }

    /**
     * Classrooms belonging to this grade level.
     */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    /**
     * Format display label.
     */
    public function displayLabel(): string
    {
        return strtoupper($this->educational_level).' — '.$this->name;
    }
}
