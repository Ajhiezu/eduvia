<?php

namespace App\Enums;

enum StaffPosition: string
{
    case PRINCIPAL = 'kepala_sekolah';
    case VICE_PRINCIPAL = 'wakil_kepala_sekolah';
    case SUBJECT_TEACHER = 'guru_mapel';
    case HOMEROOM_TEACHER = 'guru_kelas';
    case COUNSELOR = 'guru_bk';
    case SCHOOL_OPERATOR = 'operator_sekolah';
    case ADMINISTRATION = 'tata_usaha';
    case LIBRARIAN = 'pustakawan';
    case LAB_ASSISTANT = 'laboran';
    case TREASURER = 'bendahara';
    case GENERAL_STAFF = 'staf_administrasi';

    /**
     * Get human-readable Indonesian label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PRINCIPAL => 'Kepala Sekolah',
            self::VICE_PRINCIPAL => 'Wakil Kepala Sekolah',
            self::SUBJECT_TEACHER => 'Guru Mata Pelajaran',
            self::HOMEROOM_TEACHER => 'Guru Kelas / Wali Kelas',
            self::COUNSELOR => 'Guru Bimbingan Konseling (BK)',
            self::SCHOOL_OPERATOR => 'Operator Sekolah',
            self::ADMINISTRATION => 'Tata Usaha (TU)',
            self::LIBRARIAN => 'Pustakawan',
            self::LAB_ASSISTANT => 'Laboran',
            self::TREASURER => 'Bendahara / Staf Keuangan',
            self::GENERAL_STAFF => 'Staf Administrasi Lainnya',
        };
    }

    /**
     * Check if this position is primarily an educator/teacher.
     */
    public function isTeaching(): bool
    {
        return in_array($this, [
            self::PRINCIPAL,
            self::VICE_PRINCIPAL,
            self::SUBJECT_TEACHER,
            self::HOMEROOM_TEACHER,
            self::COUNSELOR,
        ], true);
    }

    /**
     * Badge visual color variant for UI design system.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::PRINCIPAL, self::VICE_PRINCIPAL => 'primary',
            self::SUBJECT_TEACHER, self::HOMEROOM_TEACHER, self::COUNSELOR => 'success',
            self::SCHOOL_OPERATOR, self::ADMINISTRATION => 'info',
            self::TREASURER => 'warning',
            default => 'neutral',
        };
    }

    /**
     * Recommended default user application role.
     */
    public function defaultRole(): UserRole
    {
        return match ($this) {
            self::PRINCIPAL => UserRole::SCHOOL_ADMIN,
            self::VICE_PRINCIPAL => UserRole::SCHOOL_ADMIN,
            self::SUBJECT_TEACHER, self::HOMEROOM_TEACHER, self::COUNSELOR => UserRole::TEACHER,
            self::SCHOOL_OPERATOR => UserRole::SCHOOL_ADMIN,
            default => UserRole::STAFF,
        };
    }
}
