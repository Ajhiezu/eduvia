<?php

namespace App\Models;

use App\Enums\Permission;
use App\Enums\StaffPosition;
use App\Enums\UserRole;
use App\Traits\Auditable;
use App\Traits\BelongsToSchool;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'email',
    'phone',
    'role',
    'is_active',
    'avatar',
    'password',
    'school_id',
    'email_verified_at',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'two_factor_confirmed_at',
])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Auditable, BelongsToSchool, HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if user has any of the specified roles.
     */
    public function hasRole(UserRole|string ...$roles): bool
    {
        $currentRoleValue = $this->role instanceof UserRole ? $this->role->value : (string) $this->role;

        foreach ($roles as $role) {
            $expectedRoleValue = $role instanceof UserRole ? $role->value : (string) $role;
            if ($currentRoleValue === $expectedRoleValue) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(Permission $permission): bool
    {
        if ($this->hasRole(UserRole::SUPER_ADMIN)) {
            return true;
        }

        if (! $this->role instanceof UserRole) {
            return false;
        }

        return in_array($permission, Permission::forRole($this->role), true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(UserRole::SUPER_ADMIN);
    }

    public function isSchoolAdmin(): bool
    {
        return $this->hasRole(UserRole::SCHOOL_ADMIN);
    }

    public function isTeacher(): bool
    {
        return $this->hasRole(UserRole::TEACHER);
    }

    public function isStudent(): bool
    {
        return $this->hasRole(UserRole::STUDENT);
    }

    public function isParent(): bool
    {
        return $this->hasRole(UserRole::PARENT);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Classroom enrollments for student.
     */
    public function classroomEnrollments(): HasMany
    {
        return $this->hasMany(ClassroomStudent::class, 'student_id');
    }

    /**
     * Current enrolled classroom for student.
     *
     * @return HasOneThrough<Classroom, ClassroomStudent, $this>
     */
    public function classroom(): HasOneThrough
    {
        return $this->hasOneThrough(
            Classroom::class,
            ClassroomStudent::class,
            'student_id',
            'id',
            'id',
            'classroom_id'
        )->latest('classroom_students.id');
    }

    /**
     * All classrooms enrolled by this student.
     *
     * @return BelongsToMany<Classroom, $this>
     */
    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_students', 'student_id', 'classroom_id')
            ->withPivot(['id', 'academic_year_id', 'enrollment_status', 'joined_at', 'left_at', 'notes'])
            ->withTimestamps();
    }

    /**
     * Classrooms where this user is the homeroom teacher.
     */
    public function homeroomClassrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'homeroom_teacher_id');
    }

    /**
     * Determine if this user is a homeroom teacher (optionally for a specific classroom).
     */
    public function isHomeroomTeacher(?Classroom $classroom = null): bool
    {
        if (! $this->isTeacher()) {
            return false;
        }

        if ($classroom) {
            return (int) $classroom->homeroom_teacher_id === (int) $this->id;
        }

        return Classroom::where('homeroom_teacher_id', $this->id)->exists();
    }

    /**
     * Determine if this teacher is the homeroom teacher of a specific student.
     */
    public function isHomeroomTeacherOf(User $student): bool
    {
        if (! $this->isTeacher()) {
            return false;
        }

        return ClassroomStudent::where('student_id', $student->id)
            ->where('enrollment_status', 'active')
            ->whereHas('classroom', fn ($q) => $q->where('homeroom_teacher_id', $this->id))
            ->exists();
    }

    /**
     * Determine if this teacher teaches a specific student.
     */
    public function teachesStudent(User $student): bool
    {
        if (! $this->isTeacher()) {
            return false;
        }

        if ($this->isHomeroomTeacherOf($student)) {
            return true;
        }

        $studentClassroomIds = ClassroomStudent::where('student_id', $student->id)
            ->where('enrollment_status', 'active')
            ->pluck('classroom_id');

        if ($studentClassroomIds->isEmpty()) {
            return false;
        }

        return TeachingAssignment::where('teacher_id', $this->id)
            ->where('is_active', true)
            ->whereIn('classroom_id', $studentClassroomIds)
            ->exists();
    }

    /**
     * Teaching assignments where this user is the teacher.
     */
    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class, 'teacher_id');
    }

    /**
     * Attendance records for this student.
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'student_id');
    }

    /**
     * Extended profile for student.
     */
    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class, 'user_id');
    }

    /**
     * Active classroom enrollment for student.
     */
    public function classroomEnrollment(): HasOne
    {
        return $this->hasOne(ClassroomStudent::class, 'student_id')->where('enrollment_status', 'active')->latest('id');
    }

    /**
     * Current active classroom for student.
     */
    public function getClassroomAttribute(): ?Classroom
    {
        return $this->classroomEnrollment?->classroom;
    }

    /**
     * Current active classroom ID for student.
     */
    public function getClassroomIdAttribute(): ?int
    {
        return $this->classroomEnrollment?->classroom_id;
    }

    /**
     * Status change histories for student.
     */
    public function studentStatusHistories(): HasMany
    {
        return $this->hasMany(StudentStatusHistory::class, 'student_id')->latest('effective_date')->latest('id');
    }

    /**
     * Learning materials created by this user (teacher).
     */
    public function learningMaterials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class, 'teacher_id');
    }

    /**
     * Assignments created by this user (teacher).
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'teacher_id');
    }

    /**
     * Assignment submissions by this user (student).
     */
    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class, 'student_id');
    }

    /**
     * Learning material progress records for this user (student).
     */
    public function materialProgress(): HasMany
    {
        return $this->hasMany(LearningMaterialProgress::class, 'student_id');
    }

    /**
     * Questions created by this user in the question bank.
     */
    public function createdExamQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class, 'created_by');
    }

    /**
     * Exams created by this user (teacher).
     */
    public function createdExams(): HasMany
    {
        return $this->hasMany(Exam::class, 'teacher_id');
    }

    /**
     * Exam attempts made by this user (student).
     */
    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class, 'student_id');
    }

    /**
     * Extended profile for staff / teacher.
     */
    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class, 'user_id');
    }

    /**
     * School relationship.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Audit histories for this staff account.
     */
    public function staffStatusHistories(): HasMany
    {
        return $this->hasMany(StaffStatusHistory::class, 'user_id')->latest('created_at');
    }

    public function isStaffRole(): bool
    {
        return $this->hasRole(UserRole::STAFF);
    }

    /**
     * Get NIP attribute.
     */
    public function getNipAttribute(): ?string
    {
        return $this->staffProfile?->nip;
    }

    /**
     * Get NUPTK attribute.
     */
    public function getNuptkAttribute(): ?string
    {
        return $this->staffProfile?->nuptk;
    }

    /**
     * Get employment position attribute.
     */
    public function getStaffPositionAttribute(): ?StaffPosition
    {
        return $this->staffProfile?->position;
    }

    /**
     * Get NISN attribute.
     */
    public function getNisnAttribute(): ?string
    {
        return $this->studentProfile?->nisn;
    }

    /**
     * Get NIS attribute.
     */
    public function getNisAttribute(): ?string
    {
        return $this->studentProfile?->nis;
    }

    /**
     * Subject gradebooks managed by this teacher.
     *
     * @return HasMany<SubjectGradebook, $this>
     */
    public function subjectGradebooks(): HasMany
    {
        return $this->hasMany(SubjectGradebook::class, 'teacher_id');
    }

    /**
     * Report cards for this student.
     *
     * @return HasMany<StudentReportCard, $this>
     */
    public function studentReportCards(): HasMany
    {
        return $this->hasMany(StudentReportCard::class, 'student_id');
    }

    /**
     * Report cards managed by this homeroom teacher.
     *
     * @return HasMany<StudentReportCard, $this>
     */
    public function homeroomReportCards(): HasMany
    {
        return $this->hasMany(StudentReportCard::class, 'homeroom_teacher_id');
    }

    /**
     * Parent-student relations where this user is the parent.
     *
     * @return HasMany<ParentStudentRelation, $this>
     */
    public function parentRelations(): HasMany
    {
        return $this->hasMany(ParentStudentRelation::class, 'parent_id');
    }

    /**
     * Parent-student relations where this user is the student.
     *
     * @return HasMany<ParentStudentRelation, $this>
     */
    public function studentRelations(): HasMany
    {
        return $this->hasMany(ParentStudentRelation::class, 'student_id');
    }

    /**
     * Linked children for this parent account.
     *
     * @return BelongsToMany<User, $this>
     */
    public function linkedChildren(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student_relations', 'parent_id', 'student_id')
            ->withPivot(['id', 'school_id', 'relationship_type', 'status', 'is_primary_contact', 'verified_at', 'notes'])
            ->withTimestamps();
    }

    /**
     * Active, verified children for this parent.
     *
     * @return BelongsToMany<User, $this>
     */
    public function activeChildren(): BelongsToMany
    {
        return $this->linkedChildren()->wherePivot('status', ParentStudentRelation::STATUS_ACTIVE);
    }

    /**
     * Check if this parent user is actively linked to a specific student.
     */
    public function isParentOf(User $student): bool
    {
        if (! $this->isParent()) {
            return false;
        }

        return ParentStudentRelation::where('parent_id', $this->id)
            ->where('student_id', $student->id)
            ->where('status', ParentStudentRelation::STATUS_ACTIVE)
            ->exists();
    }

    /**
     * Linked parents for this student account.
     *
     * @return BelongsToMany<User, $this>
     */
    public function linkedParents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student_relations', 'student_id', 'parent_id')
            ->withPivot(['id', 'school_id', 'relationship_type', 'status', 'is_primary_contact', 'verified_at', 'notes'])
            ->withTimestamps();
    }

    /**
     * Active, verified parents for this student.
     *
     * @return BelongsToMany<User, $this>
     */
    public function activeParents(): BelongsToMany
    {
        return $this->linkedParents()->wherePivot('status', ParentStudentRelation::STATUS_ACTIVE);
    }

    /**
     * Conversations where this user is the parent.
     *
     * @return HasMany<ParentTeacherConversation, $this>
     */
    public function parentConversations(): HasMany
    {
        return $this->hasMany(ParentTeacherConversation::class, 'parent_id');
    }

    /**
     * Conversations where this user is the teacher.
     *
     * @return HasMany<ParentTeacherConversation, $this>
     */
    public function teacherConversations(): HasMany
    {
        return $this->hasMany(ParentTeacherConversation::class, 'teacher_id');
    }

    /**
     * Invoices billed to this student.
     *
     * @return HasMany<Invoice, $this>
     */
    public function studentInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'student_id');
    }

    /**
     * Payments associated with this student.
     *
     * @return HasMany<Payment, $this>
     */
    public function studentPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'student_id');
    }

    /**
     * Discounts or scholarships assigned to this student.
     *
     * @return HasMany<StudentDiscount, $this>
     */
    public function studentDiscounts(): HasMany
    {
        return $this->hasMany(StudentDiscount::class, 'student_id');
    }

    public function libraryLoans(): HasMany
    {
        return $this->hasMany(LibraryLoan::class);
    }

    public function facilityReservations(): HasMany
    {
        return $this->hasMany(FacilityReservation::class);
    }

    public function lettersCreated(): HasMany
    {
        return $this->hasMany(SchoolLetter::class, 'created_by');
    }
}
