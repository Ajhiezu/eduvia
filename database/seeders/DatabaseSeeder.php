<?php

namespace Database\Seeders;

use App\Enums\StaffPosition;
use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\AdmissionApplicant;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionTrack;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Semester;
use App\Models\StaffProfile;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\SystemSetting;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with safe development roles.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('Password123!');

        // 1. Super Admin Platform
        User::firstOrCreate(
            ['email' => 'superadmin@eduvia.test'],
            [
                'name' => 'Super Administrator Eduvia',
                'role' => UserRole::SUPER_ADMIN->value,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => $defaultPassword,
            ]
        );

        // 2. School Admin (Admin Sekolah)
        User::firstOrCreate(
            ['email' => 'admin.sekolah@eduvia.test'],
            [
                'name' => 'Admin Sekolah Nusantara',
                'role' => UserRole::SCHOOL_ADMIN->value,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => $defaultPassword,
            ]
        );

        // 3. Teacher (Guru)
        User::firstOrCreate(
            ['email' => 'guru@eduvia.test'],
            [
                'name' => 'Budi Santoso, S.Pd.',
                'role' => UserRole::TEACHER->value,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => $defaultPassword,
            ]
        );

        // 4. Student (Siswa)
        User::firstOrCreate(
            ['email' => 'siswa@eduvia.test'],
            [
                'name' => 'Ahmad Fauzi',
                'role' => UserRole::STUDENT->value,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => $defaultPassword,
            ]
        );

        // 5. Parent (Orang Tua / Wali)
        User::firstOrCreate(
            ['email' => 'orangtua@eduvia.test'],
            [
                'name' => 'Hendra Setiawan (Wali)',
                'role' => UserRole::PARENT->value,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => $defaultPassword,
            ]
        );

        // 6. School Profile Foundation (Modul 1)
        $school = School::firstOrCreate(
            ['name' => 'SMP/SMA Nusantara Utama'],
            [
                'short_name' => 'Nusantara Utama',
                'npsn' => '20104567',
                'educational_levels' => ['smp', 'sma'],
                'address' => 'Jl. Pendidikan Karakter No. 45, Kebayoran Baru',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12150',
                'phone' => '(021) 7890-1234',
                'email' => 'info@nusantarautama.sch.id',
                'website' => 'https://nusantarautama.sch.id',
                'is_active' => true,
            ]
        );

        // Seed Staff Profiles for School Admin, Teacher, and Staff TU
        $schoolAdminUser = User::where('email', 'admin.sekolah@eduvia.test')->first();
        if ($schoolAdminUser) {
            $schoolAdminUser->update(['school_id' => $school->id]);
            StaffProfile::firstOrCreate(
                ['user_id' => $schoolAdminUser->id],
                [
                    'school_id' => $school->id,
                    'nip' => '197503122000031001',
                    'nuptk' => '7654321098765432',
                    'gender' => 'male',
                    'birth_place' => 'Bandung',
                    'birth_date' => '1975-03-12',
                    'position' => StaffPosition::PRINCIPAL->value,
                    'work_unit' => 'Pimpinan Sekolah',
                    'employment_status' => 'pns',
                    'join_date' => '2015-01-02',
                    'last_education' => 'S2 Manajemen Pendidikan',
                    'status' => 'active',
                    'activated_at' => now(),
                ]
            );
        }

        $teacherUser = User::where('email', 'guru@eduvia.test')->first();
        if ($teacherUser) {
            $teacherUser->update(['school_id' => $school->id]);
            StaffProfile::firstOrCreate(
                ['user_id' => $teacherUser->id],
                [
                    'school_id' => $school->id,
                    'nip' => '198507202010011002',
                    'nuptk' => '8976543210123456',
                    'gender' => 'male',
                    'birth_place' => 'Jakarta',
                    'birth_date' => '1985-07-20',
                    'position' => StaffPosition::SUBJECT_TEACHER->value,
                    'work_unit' => 'Bidang Kurikulum',
                    'employment_status' => 'gty',
                    'join_date' => '2018-07-15',
                    'last_education' => 'S1 Pendidikan Matematika',
                    'status' => 'active',
                    'activated_at' => now(),
                ]
            );
        }

        $staffTuUser = User::firstOrCreate(
            ['email' => 'staf.tu@eduvia.test'],
            [
                'name' => 'Siti Nurhaliza, A.Md.',
                'school_id' => $school->id,
                'role' => UserRole::STAFF->value,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => $defaultPassword,
            ]
        );

        StaffProfile::firstOrCreate(
            ['user_id' => $staffTuUser->id],
            [
                'school_id' => $school->id,
                'nip' => '199204182016022003',
                'gender' => 'female',
                'birth_place' => 'Semarang',
                'birth_date' => '1992-04-18',
                'position' => StaffPosition::ADMINISTRATION->value,
                'work_unit' => 'Tata Usaha & Administrasi',
                'employment_status' => 'pty',
                'join_date' => '2020-02-01',
                'last_education' => 'D3 Administrasi Perkantoran',
                'status' => 'active',
                'activated_at' => now(),
            ]
        );

        // 7. Academic Years & Semesters (Modul 1)
        $academicYear = AcademicYear::firstOrCreate(
            [
                'school_id' => $school->id,
                'name' => '2025/2026',
            ],
            [
                'start_date' => '2025-07-14',
                'end_date' => '2026-06-19',
                'is_active' => true,
                'description' => 'Tahun Ajaran Aktif 2025/2026',
            ]
        );

        $semesterGanjil = Semester::firstOrCreate(
            [
                'academic_year_id' => $academicYear->id,
                'semester_type' => 'ganjil',
            ],
            [
                'name' => 'Semester Ganjil',
                'start_date' => '2025-07-14',
                'end_date' => '2025-12-19',
                'is_active' => true,
            ]
        );

        Semester::firstOrCreate(
            [
                'academic_year_id' => $academicYear->id,
                'semester_type' => 'genap',
            ],
            [
                'name' => 'Semester Genap',
                'start_date' => '2026-01-05',
                'end_date' => '2026-06-19',
                'is_active' => false,
            ]
        );

        // 8. Basic System Settings (Modul 1)
        SystemSetting::set('timezone', 'Asia/Jakarta', 'string', 'general', $school->id);
        SystemSetting::set('locale', 'id', 'string', 'general', $school->id);
        SystemSetting::set('date_format', 'd/m/Y', 'string', 'general', $school->id);

        // 9. Master Referensi Tingkat Kelas (Modul 6)
        $gradeLevelsData = [
            // SD: 1 - 6
            ['educational_level' => 'sd', 'level_number' => 1, 'name' => 'Kelas 1', 'order' => 1],
            ['educational_level' => 'sd', 'level_number' => 2, 'name' => 'Kelas 2', 'order' => 2],
            ['educational_level' => 'sd', 'level_number' => 3, 'name' => 'Kelas 3', 'order' => 3],
            ['educational_level' => 'sd', 'level_number' => 4, 'name' => 'Kelas 4', 'order' => 4],
            ['educational_level' => 'sd', 'level_number' => 5, 'name' => 'Kelas 5', 'order' => 5],
            ['educational_level' => 'sd', 'level_number' => 6, 'name' => 'Kelas 6', 'order' => 6],
            // SMP: 7 - 9
            ['educational_level' => 'smp', 'level_number' => 7, 'name' => 'Kelas 7', 'order' => 7],
            ['educational_level' => 'smp', 'level_number' => 8, 'name' => 'Kelas 8', 'order' => 8],
            ['educational_level' => 'smp', 'level_number' => 9, 'name' => 'Kelas 9', 'order' => 9],
            // SMA: 10 - 12
            ['educational_level' => 'sma', 'level_number' => 10, 'name' => 'Kelas 10', 'order' => 10],
            ['educational_level' => 'sma', 'level_number' => 11, 'name' => 'Kelas 11', 'order' => 11],
            ['educational_level' => 'sma', 'level_number' => 12, 'name' => 'Kelas 12', 'order' => 12],
            // SMK: 10 - 12
            ['educational_level' => 'smk', 'level_number' => 10, 'name' => 'Kelas 10 SMK', 'order' => 13],
            ['educational_level' => 'smk', 'level_number' => 11, 'name' => 'Kelas 11 SMK', 'order' => 14],
            ['educational_level' => 'smk', 'level_number' => 12, 'name' => 'Kelas 12 SMK', 'order' => 15],
        ];

        foreach ($gradeLevelsData as $gl) {
            GradeLevel::firstOrCreate(
                ['educational_level' => $gl['educational_level'], 'level_number' => $gl['level_number']],
                ['name' => $gl['name'], 'order' => $gl['order'], 'is_active' => true]
            );
        }

        // 10. Initial Classrooms & Enrollment (Modul 6)
        $grade7 = GradeLevel::where('educational_level', 'smp')->where('level_number', 7)->first();
        $grade10 = GradeLevel::where('educational_level', 'sma')->where('level_number', 10)->first();
        $teacherUser = User::where('email', 'guru@eduvia.test')->first();
        $studentUser = User::where('email', 'siswa@eduvia.test')->first();

        if ($grade7) {
            $class7A = Classroom::firstOrCreate(
                [
                    'academic_year_id' => $academicYear->id,
                    'name' => 'VII-A',
                ],
                [
                    'school_id' => $school->id,
                    'grade_level_id' => $grade7->id,
                    'homeroom_teacher_id' => $teacherUser?->id,
                    'code' => 'CLS-7A',
                    'capacity' => 36,
                    'is_active' => true,
                    'notes' => 'Rombongan Belajar Kelas VII-A Unggulan',
                ]
            );

            Classroom::firstOrCreate(
                [
                    'academic_year_id' => $academicYear->id,
                    'name' => 'VII-B',
                ],
                [
                    'school_id' => $school->id,
                    'grade_level_id' => $grade7->id,
                    'homeroom_teacher_id' => null,
                    'code' => 'CLS-7B',
                    'capacity' => 36,
                    'is_active' => true,
                ]
            );

            // Enroll student
            if ($studentUser && $class7A) {
                ClassroomStudent::firstOrCreate(
                    [
                        'classroom_id' => $class7A->id,
                        'student_id' => $studentUser->id,
                        'academic_year_id' => $academicYear->id,
                    ],
                    [
                        'enrollment_status' => 'active',
                        'joined_at' => '2025-07-14',
                    ]
                );

                StudentProfile::firstOrCreate(
                    ['user_id' => $studentUser->id],
                    [
                        'school_id' => $school->id,
                        'nis' => '202507001',
                        'nisn' => '0081234567',
                        'gender' => 'male',
                        'birth_place' => 'Jakarta',
                        'birth_date' => '2012-05-14',
                        'religion' => 'Islam',
                        'address' => 'Jl. Melati No. 12, Kebayoran Baru, Jakarta Selatan',
                        'parent_name' => 'Hendra Setiawan',
                        'parent_phone' => '081234567890',
                        'parent_relationship' => 'Ayah',
                        'student_status' => 'active',
                        'enrollment_date' => '2025-07-14',
                        'notes' => 'Peserta didik jalur zonasi prestasi.',
                    ]
                );
            }
        }

        if ($grade10) {
            Classroom::firstOrCreate(
                [
                    'academic_year_id' => $academicYear->id,
                    'name' => 'X-MIPA-1',
                ],
                [
                    'school_id' => $school->id,
                    'grade_level_id' => $grade10->id,
                    'homeroom_teacher_id' => null,
                    'code' => 'CLS-10MIPA1',
                    'capacity' => 36,
                    'is_active' => true,
                ]
            );
        }

        // 11. Master Mata Pelajaran (Fase 2 / Fase 3)
        $subjectsData = [
            ['code' => 'MAT', 'name' => 'Matematika', 'group' => 'Umum', 'order' => 1],
            ['code' => 'BIND', 'name' => 'Bahasa Indonesia', 'group' => 'Umum', 'order' => 2],
            ['code' => 'IPA', 'name' => 'Ilmu Pengetahuan Alam', 'group' => 'Umum', 'order' => 3],
            ['code' => 'BING', 'name' => 'Bahasa Inggris', 'group' => 'Umum', 'order' => 4],
            ['code' => 'PAI', 'name' => 'Pendidikan Agama Islam', 'group' => 'Umum', 'order' => 5],
        ];

        $createdSubjects = [];
        foreach ($subjectsData as $s) {
            $createdSubjects[$s['code']] = Subject::firstOrCreate(
                ['code' => $s['code']],
                [
                    'school_id' => $school->id,
                    'name' => $s['name'],
                    'group' => $s['group'],
                    'order' => $s['order'],
                    'is_active' => true,
                ]
            );
        }

        // 12. Teaching Assignment & Attendance (Modul 11)
        if (isset($class7A) && $teacherUser && isset($createdSubjects['MAT'])) {
            $assignment = TeachingAssignment::firstOrCreate(
                [
                    'academic_year_id' => $academicYear->id,
                    'classroom_id' => $class7A->id,
                    'subject_id' => $createdSubjects['MAT']->id,
                    'teacher_id' => $teacherUser->id,
                ],
                [
                    'school_id' => $school->id,
                    'semester_id' => $semesterGanjil->id,
                    'hours_per_week' => 4,
                    'is_active' => true,
                    'notes' => 'Pengampu Matematika Kelas VII-A Tahun 2025/2026',
                ]
            );

            // Initial attendance session 1 (Finalized)
            $session1 = AttendanceSession::firstOrCreate(
                [
                    'teaching_assignment_id' => $assignment->id,
                    'meeting_number' => 1,
                    'date' => '2025-07-21',
                ],
                [
                    'school_id' => $school->id,
                    'classroom_id' => $class7A->id,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semesterGanjil->id,
                    'teacher_id' => $teacherUser->id,
                    'session_type' => 'subject_session',
                    'start_time' => '07:30',
                    'end_time' => '09:00',
                    'topic' => 'Pengenalan dan Operasi Bilangan Bulat',
                    'status' => 'finalized',
                    'finalized_at' => now(),
                    'finalized_by' => $teacherUser->id,
                    'notes' => 'Pertemuan pertama semester ganjil.',
                ]
            );

            if ($studentUser) {
                AttendanceRecord::firstOrCreate(
                    [
                        'attendance_session_id' => $session1->id,
                        'student_id' => $studentUser->id,
                    ],
                    [
                        'status' => 'present',
                        'notes' => 'Tepat waktu',
                    ]
                );
            }

            // Attendance session 2 (Draft)
            $session2 = AttendanceSession::firstOrCreate(
                [
                    'teaching_assignment_id' => $assignment->id,
                    'meeting_number' => 2,
                    'date' => '2025-07-28',
                ],
                [
                    'school_id' => $school->id,
                    'classroom_id' => $class7A->id,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semesterGanjil->id,
                    'teacher_id' => $teacherUser->id,
                    'session_type' => 'subject_session',
                    'start_time' => '07:30',
                    'end_time' => '09:00',
                    'topic' => 'Operasi Pecahan dan Desimal',
                    'status' => 'draft',
                    'notes' => 'Sesi berjalan, belum difinalisasi.',
                ]
            );

            if ($studentUser) {
                AttendanceRecord::firstOrCreate(
                    [
                        'attendance_session_id' => $session2->id,
                        'student_id' => $studentUser->id,
                    ],
                    [
                        'status' => 'present',
                        'notes' => null,
                    ]
                );
            }

            // 11. Modul 25: PPDB / SPMB Seeding
            $admissionPeriod = AdmissionPeriod::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'code' => 'PPDB-2026',
                ],
                [
                    'academic_year_id' => $academicYear->id,
                    'name' => 'PPDB Terpadu Tahun Ajaran 2026/2027',
                    'start_date' => '2026-05-01',
                    'end_date' => '2026-07-20',
                    'announcement_date' => '2026-07-25',
                    're_registration_start_date' => '2026-07-26',
                    're_registration_end_date' => '2026-07-31',
                    'quota' => 120,
                    'status' => 'open',
                    'description' => 'Penerimaan Peserta Didik Baru SMP Eduvia Nusantara Tahun Ajaran 2026/2027.',
                    'requirements' => "1. Lulus SD/MI atau sederajat\n2. Memiliki Akta Kelahiran dan Kartu Keluarga\n3. Berusia maksimal 15 tahun pada 1 Juli 2026\n4. Surat Keterangan Lulus / Ijazah",
                    'is_active' => true,
                ]
            );

            $trackZonasi = AdmissionTrack::firstOrCreate(
                ['admission_period_id' => $admissionPeriod->id, 'code' => 'ZONASI'],
                ['name' => 'Jalur Zonasi Domisili', 'quota' => 60, 'description' => 'Berdasarkan radius jarak tempat tinggal ke sekolah.']
            );

            $trackPrestasi = AdmissionTrack::firstOrCreate(
                ['admission_period_id' => $admissionPeriod->id, 'code' => 'PRESTASI'],
                ['name' => 'Jalur Prestasi Akademik & Non-Akademik', 'quota' => 30, 'description' => 'Berdasarkan nilai rapor dan sertifikat kejuaraan.']
            );

            $trackAfirmasi = AdmissionTrack::firstOrCreate(
                ['admission_period_id' => $admissionPeriod->id, 'code' => 'AFIRMASI'],
                ['name' => 'Jalur Afirmasi Ekonomi', 'quota' => 20, 'description' => 'Bagi pemegang KIP/PKH atau keluarga prasejahtera.']
            );

            $trackMutasi = AdmissionTrack::firstOrCreate(
                ['admission_period_id' => $admissionPeriod->id, 'code' => 'MUTASI'],
                ['name' => 'Jalur Perpindahan Tugas Orang Tua', 'quota' => 10, 'description' => 'Bagi calon siswa yang orang tuanya pindah dinas/tugas.']
            );

            // Sample Applicants
            AdmissionApplicant::firstOrCreate(
                ['registration_number' => 'PPDB-2026-0001'],
                [
                    'admission_period_id' => $admissionPeriod->id,
                    'admission_track_id' => $trackZonasi->id,
                    'target_grade_level_id' => $grade7->id,
                    'tracking_code' => 'TRACK001',
                    'full_name' => 'Bima Arya Pratama',
                    'nisn' => '0098765401',
                    'nik' => '3271012005120001',
                    'gender' => 'male',
                    'birth_place' => 'Jakarta',
                    'birth_date' => '2013-05-12',
                    'religion' => 'Islam',
                    'address' => 'Jl. Tebet Barat No. 14, Jakarta Selatan',
                    'phone' => '081233445501',
                    'email' => 'bima.arya@example.test',
                    'previous_school' => 'SD Negeri Tebet 01 Pagi',
                    'parent_name' => 'Arya Gunawan',
                    'parent_phone' => '081233445502',
                    'parent_relationship' => 'Ayah',
                    'status' => 'verified',
                    'selection_score' => 88.50,
                ]
            );

            AdmissionApplicant::firstOrCreate(
                ['registration_number' => 'PPDB-2026-0002'],
                [
                    'admission_period_id' => $admissionPeriod->id,
                    'admission_track_id' => $trackPrestasi->id,
                    'target_grade_level_id' => $grade7->id,
                    'tracking_code' => 'TRACK002',
                    'full_name' => 'Farah Salsabila Putri',
                    'nisn' => '0098765402',
                    'nik' => '3271016508130002',
                    'gender' => 'female',
                    'birth_place' => 'Bandung',
                    'birth_date' => '2013-08-25',
                    'religion' => 'Islam',
                    'address' => 'Jl. Bukit Duri Raya No. 45, Jakarta Selatan',
                    'phone' => '081233445503',
                    'email' => 'farah.putri@example.test',
                    'previous_school' => 'SD Kartika Nusantara',
                    'parent_name' => 'Dedi Salsabila',
                    'parent_phone' => '081233445504',
                    'parent_relationship' => 'Ayah',
                    'status' => 'accepted',
                    'selection_score' => 95.00,
                ]
            );

            AdmissionApplicant::firstOrCreate(
                ['registration_number' => 'PPDB-2026-0003'],
                [
                    'admission_period_id' => $admissionPeriod->id,
                    'admission_track_id' => $trackZonasi->id,
                    'target_grade_level_id' => $grade7->id,
                    'tracking_code' => 'TRACK003',
                    'full_name' => 'Dimas Anggara Wicaksono',
                    'nisn' => '0098765403',
                    'nik' => '3271011102130003',
                    'gender' => 'male',
                    'birth_place' => 'Jakarta',
                    'birth_date' => '2013-02-11',
                    'religion' => 'Islam',
                    'address' => 'Jl. Saharjo No. 88, Jakarta Selatan',
                    'phone' => '081233445505',
                    'email' => 'dimas.wicaksono@example.test',
                    'previous_school' => 'SD Negeri Menteng 02',
                    'parent_name' => 'Wicaksono Hartono',
                    'parent_phone' => '081233445506',
                    'parent_relationship' => 'Ayah',
                    'status' => 're_registered',
                    're_registered_at' => now(),
                    'selection_score' => 91.20,
                ]
            );

            AdmissionApplicant::firstOrCreate(
                ['registration_number' => 'PPDB-2026-0004'],
                [
                    'admission_period_id' => $admissionPeriod->id,
                    'admission_track_id' => $trackAfirmasi->id,
                    'target_grade_level_id' => $grade7->id,
                    'tracking_code' => 'TRACK004',
                    'full_name' => 'Tiara Andini Lestari',
                    'nisn' => '0098765404',
                    'nik' => '3271014407130004',
                    'gender' => 'female',
                    'birth_place' => 'Bogor',
                    'birth_date' => '2013-07-04',
                    'religion' => 'Islam',
                    'address' => 'Jl. Pancoran Barat No. 23, Jakarta Selatan',
                    'phone' => '081233445507',
                    'email' => 'tiara.andini@example.test',
                    'previous_school' => 'SD Negeri Pancoran 01',
                    'parent_name' => 'Rina Lestari',
                    'parent_phone' => '081233445508',
                    'parent_relationship' => 'Ibu',
                    'status' => 'pending',
                ]
            );
        }

        // 7. Seed School Public Website Content
        $this->call(SchoolWebsiteSeeder::class);
    }
}
