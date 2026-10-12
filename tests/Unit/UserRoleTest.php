<?php

namespace Tests\Unit;

use App\Enums\Permission;
use App\Enums\SchoolLevel;
use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_user_role_labels_and_badge_variants(): void
    {
        $this->assertEquals('Administrator Utama (Super Admin)', UserRole::SUPER_ADMIN->label());
        $this->assertEquals('Admin Sekolah', UserRole::SCHOOL_ADMIN->label());
        $this->assertEquals('Guru / Tenaga Pendidik', UserRole::TEACHER->label());
        $this->assertEquals('Siswa / Peserta Didik', UserRole::STUDENT->label());
        $this->assertEquals('Orang Tua / Wali Murid', UserRole::PARENT->label());

        $this->assertEquals('danger', UserRole::SUPER_ADMIN->badgeVariant());
        $this->assertEquals('primary', UserRole::SCHOOL_ADMIN->badgeVariant());
        $this->assertEquals('success', UserRole::TEACHER->badgeVariant());
        $this->assertEquals('warning', UserRole::STUDENT->badgeVariant());
    }

    public function test_user_role_staff_check(): void
    {
        $this->assertTrue(UserRole::SUPER_ADMIN->isStaff());
        $this->assertTrue(UserRole::SCHOOL_ADMIN->isStaff());
        $this->assertTrue(UserRole::TEACHER->isStaff());
        $this->assertFalse(UserRole::STUDENT->isStaff());
        $this->assertFalse(UserRole::PARENT->isStaff());
    }

    public function test_school_level_grades_and_labels(): void
    {
        $this->assertEquals([1, 2, 3, 4, 5, 6], SchoolLevel::SD->grades());
        $this->assertEquals([7, 8, 9], SchoolLevel::SMP->grades());
        $this->assertEquals([10, 11, 12], SchoolLevel::SMA->grades());
        $this->assertEquals([10, 11, 12], SchoolLevel::SMK->grades());

        $this->assertEquals('SD', SchoolLevel::SD->code());
        $this->assertEquals('SMK', SchoolLevel::SMK->code());
    }

    public function test_permissions_mapping_for_roles(): void
    {
        $superAdminPerms = Permission::forRole(UserRole::SUPER_ADMIN);
        $this->assertCount(count(Permission::cases()), $superAdminPerms);

        $teacherPerms = Permission::forRole(UserRole::TEACHER);
        $this->assertContains(Permission::CLASSES_VIEW, $teacherPerms);
        $this->assertContains(Permission::LEARNING_MANAGE, $teacherPerms);
        $this->assertNotContains(Permission::SCHOOL_MANAGE, $teacherPerms);
    }
}
