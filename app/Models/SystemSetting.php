<?php

namespace App\Models;

use Database\Factories\SystemSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'school_id',
    'group',
    'key',
    'value',
    'type',
])]
class SystemSetting extends Model
{
    /** @use HasFactory<SystemSettingFactory> */
    use HasFactory;

    /**
     * School this setting optionally belongs to.
     *
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get typed value.
     */
    public function getTypedValueAttribute(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'json' => json_decode($this->value ?? '[]', true),
            default => $this->value,
        };
    }

    /**
     * Get setting value by key.
     */
    public static function get(string $key, mixed $default = null, ?int $schoolId = null): mixed
    {
        $query = static::where('key', $key);

        if ($schoolId !== null) {
            $query->where('school_id', $schoolId);
        }

        $setting = $query->first();

        return $setting ? $setting->typed_value : $default;
    }

    /**
     * Set setting value by key.
     */
    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general', ?int $schoolId = null): self
    {
        $serializedValue = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => is_string($value) ? $value : json_encode($value),
            default => (string) $value,
        };

        return static::updateOrCreate(
            ['key' => $key, 'school_id' => $schoolId],
            [
                'value' => $serializedValue,
                'type' => $type,
                'group' => $group,
            ]
        );
    }
}
