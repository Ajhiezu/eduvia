<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case SCHOOL_ADMIN = 'school_admin';
    case TEACHER = 'teacher';
    case STAFF = 'staff';
    case STUDENT = 'student';
    case PARENT = 'parent';

    /**
     * Get human-readable Indonesian label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Administrator Utama (Super Admin)',
            self::SCHOOL_ADMIN => 'Admin Sekolah',
            self::TEACHER => 'Guru / Tenaga Pendidik',
            self::STAFF => 'Staf / Tenaga Kependidikan',
            self::STUDENT => 'Siswa / Peserta Didik',
            self::PARENT => 'Orang Tua / Wali Murid',
        };
    }

    /**
     * Get badge visual color variant for UI design system.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'danger',
            self::SCHOOL_ADMIN => 'primary',
            self::TEACHER => 'success',
            self::STAFF => 'info',
            self::STUDENT => 'warning',
            self::PARENT => 'neutral',
        };
    }

    /**
     * Check if the role is a school staff member.
     */
    public function isStaff(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::SCHOOL_ADMIN, self::TEACHER, self::STAFF], true);
    }
}
