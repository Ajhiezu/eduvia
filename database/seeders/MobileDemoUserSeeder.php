<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\ParentStudentRelation;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MobileDemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('password');
        $school = School::first();
        $academicYear = AcademicYear::first();
        $classroom = Classroom::first();

        // 1. Student Demo User (student@eduvia.test)
        $student = User::firstOrCreate(
            ['email' => 'student@eduvia.test'],
            [
                'name' => 'Ahmad Fauzi (Student Demo)',
                'role' => UserRole::STUDENT->value,
                'school_id' => $school?->id ?? 1,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => $defaultPassword,
            ]
        );

        if ($student && $classroom && $academicYear) {
            ClassroomStudent::firstOrCreate(
                [
                    'classroom_id' => $classroom->id,
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'enrollment_status' => 'active',
                    'joined_at' => '2025-07-14',
                ]
            );

            StudentProfile::firstOrCreate(
                ['user_id' => $student->id],
                [
                    'school_id' => $school?->id ?? 1,
                    'nis' => '202507099',
                    'nisn' => '0081234599',
                    'gender' => 'male',
                ]
            );
        }

        // 2. Parent Demo User (parent@eduvia.test)
        $parent = User::firstOrCreate(
            ['email' => 'parent@eduvia.test'],
            [
                'name' => 'Hendra Setiawan (Parent Demo)',
                'role' => UserRole::PARENT->value,
                'school_id' => $school?->id ?? 1,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => $defaultPassword,
            ]
        );

        // Link parent to both student@eduvia.test and siswa@eduvia.test
        $siswaUser = User::where('email', 'siswa@eduvia.test')->first();

        if ($parent && $student) {
            ParentStudentRelation::firstOrCreate(
                [
                    'school_id' => $school?->id ?? 1,
                    'parent_id' => $parent->id,
                    'student_id' => $student->id,
                ],
                [
                    'relationship_type' => 'father',
                    'status' => 'active',
                    'is_primary_contact' => true,
                    'verified_at' => now(),
                    'verified_by' => 1,
                ]
            );
        }

        if ($parent && $siswaUser) {
            ParentStudentRelation::firstOrCreate(
                [
                    'school_id' => $school?->id ?? 1,
                    'parent_id' => $parent->id,
                    'student_id' => $siswaUser->id,
                ],
                [
                    'relationship_type' => 'father',
                    'status' => 'active',
                    'is_primary_contact' => false,
                    'verified_at' => now(),
                    'verified_by' => 1,
                ]
            );
        }
    }
}
