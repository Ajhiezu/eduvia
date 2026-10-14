<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\AdmissionApplicantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

#[Fillable([
    'admission_period_id',
    'admission_track_id',
    'target_grade_level_id',
    'registration_number',
    'tracking_code',
    'full_name',
    'nisn',
    'nik',
    'gender',
    'birth_place',
    'birth_date',
    'religion',
    'address',
    'phone',
    'email',
    'previous_school',
    'parent_name',
    'parent_phone',
    'parent_relationship',
    'status',
    'rejection_reason',
    'selection_score',
    'selection_notes',
    're_registered_at',
    'converted_at',
    'converted_user_id',
])]
class AdmissionApplicant extends Model
{
    /** @use HasFactory<AdmissionApplicantFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'selection_score' => 'decimal:2',
            're_registered_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    /**
     * Admission period reference.
     */
    public function admissionPeriod(): BelongsTo
    {
        return $this->belongsTo(AdmissionPeriod::class);
    }

    /**
     * Admission track reference.
     */
    public function admissionTrack(): BelongsTo
    {
        return $this->belongsTo(AdmissionTrack::class);
    }

    /**
     * Target grade level.
     */
    public function targetGradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class, 'target_grade_level_id');
    }

    /**
     * Converted active student user.
     */
    public function convertedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_user_id');
    }

    /**
     * Uploaded requirement documents.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ApplicantDocument::class);
    }

    /**
     * Scope applicants by status.
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Indonesian gender label.
     */
    public function genderLabel(): string
    {
        return match ($this->gender) {
            'male', 'L' => 'Laki-laki',
            'female', 'P' => 'Perempuan',
            default => ucfirst($this->gender),
        };
    }

    /**
     * Indonesian status label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Verifikasi',
            'verified' => 'Berkas Terverifikasi',
            'revision_needed' => 'Perlu Perbaikan Berkas',
            'accepted' => 'Diterima (Lulus Seleksi)',
            'rejected' => 'Tidak Diterima',
            're_registered' => 'Sudah Daftar Ulang',
            'converted' => 'Siswa Aktif',
            default => ucfirst($this->status),
        };
    }

    /**
     * Badge visual color.
     */
    public function statusBadgeVariant(): string
    {
        return match ($this->status) {
            'pending' => 'neutral',
            'verified' => 'primary',
            'revision_needed' => 'warning',
            'accepted' => 'success',
            'rejected' => 'danger',
            're_registered' => 'primary',
            'converted' => 'success',
            default => 'neutral',
        };
    }

    /**
     * Generate unique registration number and tracking code.
     */
    public static function generateCredentials(AdmissionPeriod $period): array
    {
        $year = now()->format('Y');
        $count = self::where('admission_period_id', $period->id)->count() + 1;
        $registrationNumber = sprintf('PPDB-%s-%04d', $year, $count);

        // Ensure registration number uniqueness
        while (self::where('registration_number', $registrationNumber)->exists()) {
            $count++;
            $registrationNumber = sprintf('PPDB-%s-%04d', $year, $count);
        }

        // Generate 8-character uppercase tracking code
        $trackingCode = strtoupper(Str::random(8));

        return [
            'registration_number' => $registrationNumber,
            'tracking_code' => $trackingCode,
        ];
    }

    /**
     * Convert this applicant into an active student in users & student_profiles.
     * Idempotent: safe against double submission.
     *
     * @throws ValidationException
     */
    public function convertToActiveStudent(?int $classroomId = null, ?User $actor = null): User
    {
        if ($this->status === 'converted' && $this->converted_user_id) {
            // Already converted, return existing converted student
            return User::findOrFail($this->converted_user_id);
        }

        if (! in_array($this->status, ['accepted', 're_registered'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Hanya calon siswa yang dinyatakan diterima atau telah daftar ulang yang dapat dikonversi menjadi siswa aktif.',
            ]);
        }

        return DB::transaction(function () use ($classroomId, $actor) {
            // Determine school
            $school = $this->admissionPeriod?->school ?? School::current();
            $schoolId = $school?->id;

            // Generate temporary unique email if none
            $email = $this->email;
            if (! $email || User::where('email', $email)->exists()) {
                $sanitizedName = Str::slug($this->full_name);
                $randomSuffix = rand(100, 999);
                $email = "{$sanitizedName}.{$randomSuffix}@siswa.eduvia.test";
            }

            // Create User account for student
            $studentUser = User::create([
                'name' => $this->full_name,
                'email' => $email,
                'phone' => $this->phone,
                'role' => UserRole::STUDENT->value,
                'is_active' => true,
                'password' => Hash::make('Password123!'),
            ]);

            // Create Student Profile
            $profile = StudentProfile::create([
                'user_id' => $studentUser->id,
                'school_id' => $schoolId,
                'nis' => 'PPDB'.substr($this->registration_number, -4),
                'nisn' => $this->nisn,
                'gender' => in_array($this->gender, ['female', 'P'], true) ? 'female' : 'male',
                'birth_place' => $this->birth_place,
                'birth_date' => $this->birth_date,
                'religion' => $this->religion,
                'address' => $this->address,
                'parent_name' => $this->parent_name,
                'parent_phone' => $this->parent_phone,
                'parent_relationship' => $this->parent_relationship,
                'student_status' => 'active',
                'enrollment_date' => now()->toDateString(),
                'notes' => "Dikonversi dari pendaftaran {$this->registration_number} (Jalur {$this->admissionTrack?->name})",
            ]);

            // Audit record for initial status
            StudentStatusHistory::create([
                'student_id' => $studentUser->id,
                'previous_status' => 'unregistered',
                'new_status' => 'active',
                'effective_date' => now()->toDateString(),
                'reason' => "Penerimaan siswa baru ({$this->registration_number})",
                'changed_by' => $actor?->id,
            ]);

            // Assign initial classroom if provided
            if ($classroomId) {
                $classroom = Classroom::findOrFail($classroomId);
                $classroom->addStudent(
                    $studentUser->id,
                    now()->toDateString(),
                    "Penempatan awal PPDB ({$this->registration_number})"
                );
            }

            // Update applicant state
            $this->update([
                'status' => 'converted',
                'converted_at' => now(),
                'converted_user_id' => $studentUser->id,
            ]);

            return $studentUser;
        });
    }
}
