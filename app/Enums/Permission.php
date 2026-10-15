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

    // School Finance Management (Phase 9)
    case FINANCE_MANAGE = 'finance:manage';
    case FINANCE_VIEW = 'finance:view';
    case FINANCE_PAY = 'finance:pay';

    // School Operations Management (Phase 10)
    case OPERATIONS_MANAGE = 'operations:manage';
    case OPERATIONS_VIEW = 'operations:view';
    case ADMISSIONS_MANAGE = 'admissions:manage';
    case ADMISSIONS_VIEW = 'admissions:view';
    case LIBRARY_MANAGE = 'library:manage';
    case LIBRARY_VIEW = 'library:view';
    case ASSETS_MANAGE = 'assets:manage';
    case ASSETS_VIEW = 'assets:view';
    case FACILITIES_MANAGE = 'facilities:manage';
    case FACILITIES_VIEW = 'facilities:view';
    case FACILITIES_RESERVE = 'facilities:reserve';
    case CORRESPONDENCE_MANAGE = 'correspondence:manage';
    case CORRESPONDENCE_VIEW = 'correspondence:view';

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
                self::EXAM_MANAGE,
                self::REPORT_MANAGE,
                self::WEBSITE_MANAGE,
                self::WEBSITE_VIEW,
                self::FINANCE_MANAGE,
                self::FINANCE_VIEW,
                self::OPERATIONS_MANAGE,
                self::OPERATIONS_VIEW,
                self::ADMISSIONS_MANAGE,
                self::ADMISSIONS_VIEW,
                self::LIBRARY_MANAGE,
                self::LIBRARY_VIEW,
                self::ASSETS_MANAGE,
                self::ASSETS_VIEW,
                self::FACILITIES_MANAGE,
                self::FACILITIES_VIEW,
                self::FACILITIES_RESERVE,
                self::CORRESPONDENCE_MANAGE,
                self::CORRESPONDENCE_VIEW,
            ],
            UserRole::TEACHER => [
                self::CLASSES_VIEW,
                self::LEARNING_MANAGE,
                self::LEARNING_VIEW,
                self::ASSIGNMENT_MANAGE,
                self::ASSIGNMENT_GRADE,
                self::EXAM_MANAGE,
                self::REPORT_MANAGE,
                self::LIBRARY_VIEW,
                self::FACILITIES_VIEW,
                self::FACILITIES_RESERVE,
            ],
            UserRole::STAFF => [
                self::SCHOOL_VIEW,
                self::USERS_VIEW,
                self::CLASSES_VIEW,
                self::FINANCE_VIEW,
                self::OPERATIONS_VIEW,
                self::ADMISSIONS_VIEW,
                self::LIBRARY_VIEW,
                self::LIBRARY_MANAGE,
                self::ASSETS_VIEW,
                self::ASSETS_MANAGE,
                self::FACILITIES_VIEW,
                self::FACILITIES_MANAGE,
                self::FACILITIES_RESERVE,
                self::CORRESPONDENCE_VIEW,
                self::CORRESPONDENCE_MANAGE,
            ],
            UserRole::STUDENT => [
                self::CLASSES_VIEW,
                self::LEARNING_VIEW,
                self::ASSIGNMENT_SUBMIT,
                self::EXAM_TAKE,
                self::REPORT_VIEW_OWN,
                self::FINANCE_PAY,
                self::LIBRARY_VIEW,
            ],
            UserRole::PARENT => [
                self::CLASSES_VIEW,
                self::REPORT_VIEW_OWN,
                self::FINANCE_PAY,
                self::LIBRARY_VIEW,
            ],
        };
    }
}
