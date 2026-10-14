<?php

namespace App\Enums;

enum Permission: string
{
    // School & Tenant Management (Super Admin & School Admin)
    case SCHOOL_MANAGE = 'school:manage';
    case SCHOOL_VIEW = 'school:view';

    // User & Staff Management
    case USERS_MANAGE = 'users:manage';
    case USERS_VIEW = 'users:view';

    // Academic & Curriculum Structure (Roadmap)
    case ACADEMIC_MANAGE = 'academic:manage';
    case ACADEMIC_VIEW = 'academic:view';

    // Classes & Classrooms (Roadmap)
    case CLASSES_MANAGE = 'classes:manage';
    case CLASSES_VIEW = 'classes:view';

    // Learning Content & Materials (Roadmap)
    case LEARNING_MANAGE = 'learning:manage';
    case LEARNING_VIEW = 'learning:view';

    // Assignments & Submissions (Roadmap)
    case ASSIGNMENT_MANAGE = 'assignment:manage';
    case ASSIGNMENT_SUBMIT = 'assignment:submit';
    case ASSIGNMENT_GRADE = 'assignment:grade';

    // Exams & CBT (Roadmap)
    case EXAM_MANAGE = 'exam:manage';
    case EXAM_TAKE = 'exam:take';

    // Student Progress & Reports (Roadmap)
    case REPORT_VIEW_OWN = 'report:view-own';
    case REPORT_MANAGE = 'report:manage';

    // School Public Website & CMS
    case WEBSITE_MANAGE = 'website:manage';
    case WEBSITE_VIEW = 'website:view';

    /**
     * Map default permissions by role.
     *
     * @return array<self>
     */
    public static function forRole(UserRole $role): array
    {
        return match ($role) {
            UserRole::SUPER_ADMIN => self::cases(),
            UserRole::SCHOOL_ADMIN => [
                self::SCHOOL_VIEW,
                self::USERS_MANAGE,
                self::USERS_VIEW,
                self::ACADEMIC_MANAGE,
                self::ACADEMIC_VIEW,
                self::CLASSES_MANAGE,
                self::CLASSES_VIEW,
                self::LEARNING_VIEW,
                self::REPORT_MANAGE,
                self::WEBSITE_MANAGE,
                self::WEBSITE_VIEW,
            ],
            UserRole::TEACHER => [
                self::CLASSES_VIEW,
                self::LEARNING_MANAGE,
                self::LEARNING_VIEW,
                self::ASSIGNMENT_MANAGE,
                self::ASSIGNMENT_GRADE,
                self::EXAM_MANAGE,
                self::REPORT_MANAGE,
            ],
            UserRole::STAFF => [
                self::SCHOOL_VIEW,
                self::USERS_VIEW,
                self::CLASSES_VIEW,
            ],
            UserRole::STUDENT => [
                self::CLASSES_VIEW,
                self::LEARNING_VIEW,
                self::ASSIGNMENT_SUBMIT,
                self::EXAM_TAKE,
                self::REPORT_VIEW_OWN,
            ],
            UserRole::PARENT => [
                self::CLASSES_VIEW,
                self::REPORT_VIEW_OWN,
            ],
        };
    }
}
