<?php

namespace App\Models;

use App\Services\TenantManager;
use Database\Factories\SchoolFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name',
    'slug',
    'short_name',
    'npsn',
    'educational_levels',
    'address',
    'city',
    'province',
    'postal_code',
    'phone',
    'email',
    'website',
    'logo_path',
    'is_active',
    'public_website_enabled',
])]
class School extends Model
{
    /** @use HasFactory<SchoolFactory> */
    use HasFactory;

    /**
     * Get attributes cast types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'educational_levels' => 'array',
            'is_active' => 'boolean',
            'public_website_enabled' => 'boolean',
        ];
    }

    /**
     * Get all academic years for this school.
     *
     * @return HasMany<AcademicYear, $this>
     */
    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
    }

    /**
     * Get active academic year for this school.
     *
     * @return HasOne<AcademicYear, $this>
     */
    public function activeAcademicYear(): HasOne
    {
        return $this->hasOne(AcademicYear::class)->where('is_active', true);
    }

    /**
     * Get all custom system settings for this school.
     *
     * @return HasMany<SystemSetting, $this>
     */
    public function settings(): HasMany
    {
        return $this->hasMany(SystemSetting::class);
    }

    /**
     * Fee categories for this school.
     *
     * @return HasMany<FeeCategory, $this>
     */
    public function feeCategories(): HasMany
    {
        return $this->hasMany(FeeCategory::class);
    }

    /**
     * Invoices issued by this school.
     *
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Payments recorded for this school.
     *
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Receipts issued by this school.
     *
     * @return HasMany<Receipt, $this>
     */
    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    /**
     * Get classrooms belonging to this school.
     *
     * @return HasMany<Classroom, $this>
     */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    /**
     * Get all staff profiles in this school.
     *
     * @return HasMany<StaffProfile, $this>
     */
    public function staffProfiles(): HasMany
    {
        return $this->hasMany(StaffProfile::class);
    }

    /**
     * Get all users belonging to this school.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get current default/active school instance.
     */
    public static function current(): ?self
    {
        return app(TenantManager::class)->getSchool();
    }

    /**
     * Get website settings for this school.
     *
     * @return HasOne<SchoolWebsiteSetting, $this>
     */
    public function websiteSetting(): HasOne
    {
        return $this->hasOne(SchoolWebsiteSetting::class);
    }

    /**
     * Get all posts (news, events, achievements) for this school.
     *
     * @return HasMany<SchoolPost, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(SchoolPost::class);
    }

    /**
     * Get published posts.
     *
     * @return HasMany<SchoolPost, $this>
     */
    public function publishedPosts(): HasMany
    {
        return $this->hasMany(SchoolPost::class)->where('is_published', true);
    }

    /**
     * Get extracurriculars for this school.
     *
     * @return HasMany<SchoolExtracurricular, $this>
     */
    public function extracurriculars(): HasMany
    {
        return $this->hasMany(SchoolExtracurricular::class);
    }

    /**
     * Get facilities and featured programs for this school.
     *
     * @return HasMany<SchoolFacility, $this>
     */
    public function facilities(): HasMany
    {
        return $this->hasMany(SchoolFacility::class);
    }

    /**
     * Get media galleries for this school.
     *
     * @return HasMany<SchoolGallery, $this>
     */
    public function galleries(): HasMany
    {
        return $this->hasMany(SchoolGallery::class);
    }

    /**
     * Get guest contact messages for this school.
     *
     * @return HasMany<SchoolMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(SchoolMessage::class);
    }

    /**
     * Get unread guest messages for this school.
     *
     * @return HasMany<SchoolMessage, $this>
     */
    public function unreadMessages(): HasMany
    {
        return $this->hasMany(SchoolMessage::class)->where('is_read', false);
    }

    /**
     * Get subjects belonging to this school.
     *
     * @return HasMany<Subject, $this>
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Get teaching assignments in this school.
     *
     * @return HasMany<TeachingAssignment, $this>
     */
    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    /**
     * Get lesson schedules in this school.
     *
     * @return HasMany<LessonSchedule, $this>
     */
    public function lessonSchedules(): HasMany
    {
        return $this->hasMany(LessonSchedule::class);
    }

    /**
     * Get question bank items in this school.
     *
     * @return HasMany<ExamQuestion, $this>
     */
    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class);
    }

    /**
     * Get exams in this school.
     *
     * @return HasMany<Exam, $this>
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    /**
     * Get assessment weight configurations for this school.
     *
     * @return HasMany<AssessmentWeightConfiguration, $this>
     */
    public function assessmentWeightConfigurations(): HasMany
    {
        return $this->hasMany(AssessmentWeightConfiguration::class);
    }

    /**
     * Get subject gradebooks in this school.
     *
     * @return HasMany<SubjectGradebook, $this>
     */
    public function subjectGradebooks(): HasMany
    {
        return $this->hasMany(SubjectGradebook::class);
    }

    /**
     * Get student report cards in this school.
     *
     * @return HasMany<StudentReportCard, $this>
     */
    public function studentReportCards(): HasMany
    {
        return $this->hasMany(StudentReportCard::class);
    }

    /**
     * Get public URL for school logo.
     */
    public function logoUrl(): ?string
    {
        if (! $this->logo_path || ! Storage::disk('public')->exists($this->logo_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }

    public function libraryBooks(): HasMany
    {
        return $this->hasMany(LibraryBook::class);
    }

    public function libraryLoans(): HasMany
    {
        return $this->hasMany(LibraryLoan::class);
    }

    public function inventoryCategories(): HasMany
    {
        return $this->hasMany(InventoryCategory::class);
    }

    public function schoolAssets(): HasMany
    {
        return $this->hasMany(SchoolAsset::class);
    }

    public function consumableItems(): HasMany
    {
        return $this->hasMany(ConsumableItem::class);
    }

    public function schoolRooms(): HasMany
    {
        return $this->hasMany(SchoolRoom::class);
    }

    public function facilityReservations(): HasMany
    {
        return $this->hasMany(FacilityReservation::class);
    }

    public function facilityIncidents(): HasMany
    {
        return $this->hasMany(FacilityIncident::class);
    }

    public function schoolLetters(): HasMany
    {
        return $this->hasMany(SchoolLetter::class);
    }
}
