<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Database\Factories\ParentTeacherConversationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ParentTeacherConversation extends Model
{
    use BelongsToSchool;

    /** @use HasFactory<ParentTeacherConversationFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'student_id',
        'parent_id',
        'teacher_id',
        'subject',
        'status',
        'last_message_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /**
     * School relationship.
     *
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Student being discussed relationship.
     *
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Parent relationship.
     *
     * @return BelongsTo<User, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * Teacher relationship.
     *
     * @return BelongsTo<User, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Messages in this conversation.
     *
     * @return HasMany<ConversationMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class, 'conversation_id')->oldest('created_at');
    }

    /**
     * Latest message in this conversation.
     *
     * @return HasOne<ConversationMessage, $this>
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(ConversationMessage::class, 'conversation_id')->latestOfMany('created_at');
    }

    /**
     * Count unread messages for a given user.
     */
    public function unreadCountFor(User $user): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Check if a given user is an authorized participant.
     */
    public function hasParticipant(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isSchoolAdmin() && (int) $user->school_id === (int) $this->school_id) {
            return true;
        }

        return (int) $user->id === (int) $this->parent_id || (int) $user->id === (int) $this->teacher_id;
    }

    /**
     * Scope for a specific participant.
     */
    public function scopeForParticipant(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isSchoolAdmin()) {
            return $query->where('school_id', $user->school_id);
        }

        return $query->where(function (Builder $sub) use ($user) {
            $sub->where('parent_id', $user->id)
                ->orWhere('teacher_id', $user->id);
        });
    }
}
