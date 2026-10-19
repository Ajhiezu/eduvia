<?php

namespace Database\Seeders;

use App\Enums\StaffPosition;
use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\GradeLevel;
use App\Models\LessonSchedule;
use App\Models\ParentStudentRelation;
use App\Models\School;
use App\Models\Semester;
use App\Models\StaffProfile;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AllLevelsDummyDataSeeder extends Seeder
{
    protected string $defaultPassword;

    protected School $school;

    protected AcademicYear $academicYear;

    protected Semester $semesterGanjil;

    protected Semester $semesterGenap;

    /**
     * Run the database seeds for all educational levels (SD, SMP, SMA).
     */
    public function run(): void
    {
        $this->command->info('=== MEMULAI GENERASI DATA DUMMY SEMUA JENJANG (SD, SMP, SMA) ===');

        // Pre-hash password once for ultra-fast seeding
        $this->defaultPassword = Hash::make('password123');

        // 1. Dapatkan atau buat tenant sekolah utama
        $this->school = School::firstOrCreate(
            ['slug' => 'nusantara-utama'],
            [
                'name' => 'Yayasan Perguruan Nusantara Utama',
                'short_name' => 'Nusantara Utama',
                'npsn' => '20104567',
                'educational_levels' => ['sd', 'smp', 'sma'],
                'is_active' => true,
            ]
        );

        // 2. Tahun Ajaran & Semester
        $this->academicYear = AcademicYear::firstOrCreate(
            ['school_id' => $this->school->id, 'name' => '2026/2027'],
            [
                'start_date' => '2026-07-15',
                'end_date' => '2027-06-20',
                'is_active' => true,
                'description' => 'Tahun Ajaran 2026/2027',
            ]
        );
        $this->academicYear->activate();

        $this->semesterGanjil = Semester::firstOrCreate(
            ['academic_year_id' => $this->academicYear->id, 'semester_type' => 'ganjil'],
            [
                'name' => 'Semester Ganjil',
                'start_date' => '2026-07-15',
                'end_date' => '2026-12-20',
                'is_active' => true,
            ]
        );
        $this->semesterGanjil->activate();

        $this->semesterGenap = Semester::firstOrCreate(
            ['academic_year_id' => $this->academicYear->id, 'semester_type' => 'genap'],
            [
                'name' => 'Semester Genap',
                'start_date' => '2027-01-05',
                'end_date' => '2027-06-20',
                'is_active' => false,
            ]
        );

        // 3. Kepala Sekolah (SD, SMP, SMA)
        $this->seedPrincipals();

        // 4. Guru & Mapel SD
        $this->seedSD();

        // 5. Guru & Mapel SMP
        $this->seedSMP();

        // 6. Guru & Mapel SMA
        $this->seedSMA();

        $this->command->info('=== SELURUH DATA DUMMY SD, SMP, SMA SELESAI DIGENERASI SECARA LENGKAP! ===');
    }

    /**
     * Seed Kepala Sekolah untuk SD, SMP, dan SMA.
     */
    protected function seedPrincipals(): void
    {
        $this->command->info('-> Mengenerasikan Kepala Sekolah SD, SMP, dan SMA...');

        $principals = [
            [
                'name' => 'Dra. Hj. Siti Aminah, M.Pd.',
                'email' => 'kepsek.sd@eduvia.test',
                'nip' => '197005121995032001',
                'nuptk' => '5432198765432101',
                'gender' => 'female',
                'unit' => 'SD Nusantara Utama',
                'edu' => 'S2 Manajemen Pendidikan Dasar',
            ],
            [
                'name' => 'Drs. H. Bambang Irawan, M.Si.',
                'email' => 'kepsek.smp@eduvia.test',
                'nip' => '196808201993031002',
                'nuptk' => '6543219876543202',
                'gender' => 'male',
                'unit' => 'SMP Nusantara Utama',
                'edu' => 'S2 Administrasi Pendidikan',
            ],
            [
                'name' => 'Dr. Hendro Prasetyo, M.Pd.',
                'email' => 'kepsek.sma@eduvia.test',
                'nip' => '196511101990031003',
                'nuptk' => '7654321987654303',
                'gender' => 'male',
                'unit' => 'SMA Nusantara Utama',
                'edu' => 'S3 Kurikulum dan Teknologi Pembelajaran',
            ],
        ];

        foreach ($principals as $p) {
            $user = User::firstOrCreate(
                ['email' => $p['email']],
                [
                    'school_id' => $this->school->id,
                    'name' => $p['name'],
                    'role' => UserRole::SCHOOL_ADMIN->value,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'password' => $this->defaultPassword,
                ]
            );

            StaffProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'school_id' => $this->school->id,
                    'nip' => $p['nip'],
                    'nuptk' => $p['nuptk'],
                    'gender' => $p['gender'],
                    'position' => StaffPosition::PRINCIPAL->value,
                    'work_unit' => $p['unit'],
                    'employment_status' => 'pns',
                    'join_date' => '2015-07-01',
                    'last_education' => $p['edu'],
                    'status' => 'active',
                    'activated_at' => now(),
                ]
            );
        }
    }

    /**
     * Seed Jenjang SD (Kelas 1 - 6, 36 siswa per kelas = 216 siswa).
     */
    protected function seedSD(): void
    {
        $this->command->info('-> Mengenerasikan Data Jenjang SD (Kelas 1 - 6, 36 siswa/kelas)...');

        // 1. Mapel SD
        $sdSubjectsData = [
            ['code' => 'PAI-SD', 'name' => 'Pendidikan Agama Islam & Budi Pekerti', 'group' => 'Wajib'],
            ['code' => 'PPKN-SD', 'name' => 'Pendidikan Pancasila', 'group' => 'Wajib'],
            ['code' => 'BIND-SD', 'name' => 'Bahasa Indonesia', 'group' => 'Wajib'],
            ['code' => 'MAT-SD', 'name' => 'Matematika', 'group' => 'Wajib'],
            ['code' => 'IPAS-SD', 'name' => 'Ilmu Pengetahuan Alam & Sosial (IPAS)', 'group' => 'Wajib'],
            ['code' => 'PJOK-SD', 'name' => 'Pendidikan Jasmani, Olahraga & Kesehatan', 'group' => 'Wajib'],
            ['code' => 'SENI-SD', 'name' => 'Seni Budaya & Prakarya', 'group' => 'Wajib'],
            ['code' => 'BING-SD', 'name' => 'Bahasa Inggris Dasar', 'group' => 'Muatan Lokal'],
        ];

        $sdSubjects = [];
        foreach ($sdSubjectsData as $idx => $s) {
            $sdSubjects[$s['code']] = Subject::firstOrCreate(
                ['school_id' => $this->school->id, 'code' => $s['code']],
                [
                    'name' => $s['name'],
                    'group' => $s['group'],
                    'educational_level' => 'sd',
                    'order' => $idx + 1,
                    'is_active' => true,
                ]
            );
        }

        // 2. Guru SD (6 Guru Kelas + Guru Khusus Agama, PJOK, B.Inggris, Seni)
        $sdTeachersData = [
            ['name' => 'Ratna Dewi, S.Pd.SD', 'email' => 'guru.sd.k1@eduvia.test', 'pos' => StaffPosition::HOMEROOM_TEACHER, 'role' => 'Wali Kelas 1'],
            ['name' => 'Ahmad Subandi, S.Pd.', 'email' => 'guru.sd.k2@eduvia.test', 'pos' => StaffPosition::HOMEROOM_TEACHER, 'role' => 'Wali Kelas 2'],
            ['name' => 'Nurul Hidayah, S.Pd.', 'email' => 'guru.sd.k3@eduvia.test', 'pos' => StaffPosition::HOMEROOM_TEACHER, 'role' => 'Wali Kelas 3'],
            ['name' => 'Budi Utomo, S.Pd.SD', 'email' => 'guru.sd.k4@eduvia.test', 'pos' => StaffPosition::HOMEROOM_TEACHER, 'role' => 'Wali Kelas 4'],
            ['name' => 'Siti Khotimah, M.Pd.', 'email' => 'guru.sd.k5@eduvia.test', 'pos' => StaffPosition::HOMEROOM_TEACHER, 'role' => 'Wali Kelas 5'],
            ['name' => 'Eko Prasetyo, S.Pd.', 'email' => 'guru.sd.k6@eduvia.test', 'pos' => StaffPosition::HOMEROOM_TEACHER, 'role' => 'Wali Kelas 6'],
            ['name' => 'Ust. Muhammad Zaid, S.Pd.I.', 'email' => 'guru.sd.pai@eduvia.test', 'pos' => StaffPosition::SUBJECT_TEACHER, 'role' => 'Guru PAI SD'],
            ['name' => 'Yuda Pratama, S.Pd.Jas', 'email' => 'guru.sd.pjok@eduvia.test', 'pos' => StaffPosition::SUBJECT_TEACHER, 'role' => 'Guru PJOK SD'],
            ['name' => 'Jessica Marlina, S.Pd.', 'email' => 'guru.sd.bing@eduvia.test', 'pos' => StaffPosition::SUBJECT_TEACHER, 'role' => 'Guru Bahasa Inggris SD'],
            ['name' => 'Rina Astuti, S.Sn.', 'email' => 'guru.sd.seni@eduvia.test', 'pos' => StaffPosition::SUBJECT_TEACHER, 'role' => 'Guru Seni SD'],
        ];

        $sdTeachers = [];
        foreach ($sdTeachersData as $idx => $t) {
            $user = User::firstOrCreate(
                ['email' => $t['email']],
                [
                    'school_id' => $this->school->id,
                    'name' => $t['name'],
                    'role' => UserRole::TEACHER->value,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'password' => $this->defaultPassword,
                ]
            );

            StaffProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'school_id' => $this->school->id,
                    'nip' => '198'.str_pad((string) (10 + $idx), 2, '0', STR_PAD_LEFT).'011200901'.($idx % 2 === 0 ? '1' : '2').'00'.($idx + 1),
                    'nuptk' => '12345678901234'.str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT),
                    'gender' => $idx % 2 === 0 ? 'female' : 'male',
                    'position' => $t['pos']->value,
                    'work_unit' => 'Unit SD Nusantara',
                    'employment_status' => 'gty',
                    'join_date' => '2019-07-15',
                    'last_education' => 'S1 Kependidikan',
                    'status' => 'active',
                    'activated_at' => now(),
                ]
            );

            $sdTeachers[$t['email']] = $user;
        }

        // 3. Buat Kelas 1 s/d 6
        for ($level = 1; $level <= 6; $level++) {
            $grade = GradeLevel::where('educational_level', 'sd')->where('level_number', $level)->first();
            $homeroomTeacher = $sdTeachers["guru.sd.k{$level}@eduvia.test"];

            $classroom = Classroom::firstOrCreate(
                [
                    'school_id' => $this->school->id,
                    'academic_year_id' => $this->academicYear->id,
                    'name' => "Kelas {$level}",
                ],
                [
                    'grade_level_id' => $grade->id,
                    'homeroom_teacher_id' => $homeroomTeacher->id,
                    'code' => "SD-CLS-{$level}",
                    'capacity' => 36,
                    'is_active' => true,
                    'notes' => "Rombongan Belajar Kelas {$level} SD",
                ]
            );

            // Siswa (36 siswa per kelas) & Orang Tua
            $this->seedStudentsAndParentsForClassroom($classroom, 36, "sd-k{$level}", 1000 + ($level * 100), 2018 - $level);

            // Penugasan Mengajar & Jadwal Mingguan SD
            $this->seedSchedulesForSD($classroom, $homeroomTeacher, $sdTeachers, $sdSubjects);
        }
    }

    /**
     * Seed Jenjang SMP (Kelas 7, 8, 9 masing-masing 9 kelas = 27 kelas, 32 siswa/kelas = 864 siswa).
     */
    protected function seedSMP(): void
    {
        $this->command->info('-> Mengenerasikan Data Jenjang SMP (27 Kelas: 7A-7I, 8A-8I, 9A-9I, 32 siswa/kelas)...');

        // 1. Mapel SMP
        $smpSubjectsData = [
            ['code' => 'PAI-SMP', 'name' => 'Pendidikan Agama Islam & Budi Pekerti', 'group' => 'Wajib'],
            ['code' => 'PPKN-SMP', 'name' => 'Pendidikan Pancasila & Kewarganegaraan', 'group' => 'Wajib'],
            ['code' => 'BIND-SMP', 'name' => 'Bahasa Indonesia', 'group' => 'Wajib'],
            ['code' => 'MAT-SMP', 'name' => 'Matematika', 'group' => 'Wajib'],
            ['code' => 'IPA-SMP', 'name' => 'Ilmu Pengetahuan Alam', 'group' => 'Wajib'],
            ['code' => 'IPS-SMP', 'name' => 'Ilmu Pengetahuan Sosial', 'group' => 'Wajib'],
            ['code' => 'BING-SMP', 'name' => 'Bahasa Inggris', 'group' => 'Wajib'],
            ['code' => 'PJOK-SMP', 'name' => 'Pendidikan Jasmani, Olahraga & Kesehatan', 'group' => 'Wajib'],
            ['code' => 'INF-SMP', 'name' => 'Informatika', 'group' => 'Wajib'],
            ['code' => 'SENI-SMP', 'name' => 'Seni Budaya', 'group' => 'Wajib'],
            ['code' => 'PRA-SMP', 'name' => 'Prakarya & Kewirausahaan', 'group' => 'Pilihan'],
            ['code' => 'BK-SMP', 'name' => 'Bimbingan dan Konseling', 'group' => 'Bimbingan'],
        ];

        $smpSubjects = [];
        foreach ($smpSubjectsData as $idx => $s) {
            $smpSubjects[$s['code']] = Subject::firstOrCreate(
                ['school_id' => $this->school->id, 'code' => $s['code']],
                [
                    'name' => $s['name'],
                    'group' => $s['group'],
                    'educational_level' => 'smp',
                    'order' => $idx + 1,
                    'is_active' => true,
                ]
            );
        }

        // 2. Guru SMP (30 Guru Berdedikasi Khusus SMP)
        $teacherNames = [
            'Drs. H. Mulyadi, M.Pd.', 'Hj. Endah Sulistyo, S.Pd.', 'Bambang Sudarsono, S.Pd.',
            'Dra. Nur Indahwati', 'Irfan Hakim, M.Pd.', 'Rini Kusuma, S.Pd.',
            'Agus Santoso, S.Si.', 'Dewi Lestari, S.Pd.', 'Farhan Pratama, M.Kom.',
            'Tri Wahyuni, S.Pd.', 'Hadi Purnomo, S.Pd.Jas', 'Anita Rahmawati, S.Pd.',
            'Kurnia Sandi, S.Pd.', 'Lina Marlina, S.Si.', 'Wahyu Hidayat, S.Kom.',
            'Sri Rejeki, S.Pd.', 'Arief Wicaksono, M.Pd.', 'Yuliana Sari, S.Pd.',
            'Rudi Hartono, S.Pd.', 'Fajar Nugraha, S.Pd.', 'Wulan Dari, S.Pd.',
            'Hendrik Tanuwijaya, S.Pd.', 'Maya Angelia, S.Pd.', 'Zulkifli Lubis, S.Ag.',
            'Dian Permatasari, M.Pd.', 'Galih Prakoso, S.Pd.', 'Niken Anggraini, S.Pd.',
            'Hasan Basri, S.Pd.I.', 'Fitria Ningsih, S.Pd.', 'Doni Setiawan, S.Pd.Jas',
        ];

        $smpTeachers = [];
        foreach ($teacherNames as $idx => $name) {
            $tNum = $idx + 1;
            $email = "guru.smp.{$tNum}@eduvia.test";

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'school_id' => $this->school->id,
                    'name' => $name,
                    'role' => UserRole::TEACHER->value,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'password' => $this->defaultPassword,
                ]
            );

            StaffProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'school_id' => $this->school->id,
                    'nip' => '197'.str_pad((string) (50 + $idx), 2, '0', STR_PAD_LEFT).'0510200501'.($idx % 2 === 0 ? '1' : '2').str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT),
                    'nuptk' => '23456789012345'.str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT),
                    'gender' => $idx % 2 === 0 ? 'male' : 'female',
                    'position' => StaffPosition::SUBJECT_TEACHER->value,
                    'work_unit' => 'Unit SMP Nusantara',
                    'employment_status' => 'gty',
                    'join_date' => '2017-07-15',
                    'last_education' => 'S1 / S2 Kependidikan',
                    'status' => 'active',
                    'activated_at' => now(),
                ]
            );

            $smpTeachers[] = $user;
        }

        // 3. Buat Kelas 7A-7I, 8A-8I, 9A-9I (Total 27 Kelas)
        $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
        $classIndex = 0;

        foreach ([7, 8, 9] as $gradeLevelNum) {
            $grade = GradeLevel::where('educational_level', 'smp')->where('level_number', $gradeLevelNum)->first();

            foreach ($letters as $letter) {
                $className = "{$gradeLevelNum}{$letter}";
                $homeroomTeacher = $smpTeachers[$classIndex % count($smpTeachers)];

                $classroom = Classroom::firstOrCreate(
                    [
                        'school_id' => $this->school->id,
                        'academic_year_id' => $this->academicYear->id,
                        'name' => $className,
                    ],
                    [
                        'grade_level_id' => $grade->id,
                        'homeroom_teacher_id' => $homeroomTeacher->id,
                        'code' => "SMP-{$className}",
                        'capacity' => 32,
                        'is_active' => true,
                        'notes' => "Rombongan Belajar Kelas {$className} SMP",
                    ]
                );

                // Siswa (32 siswa per kelas) & Orang Tua
                $prefix = strtolower("smp-{$className}");
                $this->seedStudentsAndParentsForClassroom($classroom, 32, $prefix, 20000 + ($classIndex * 100), 2013 - ($gradeLevelNum - 7));

                // Penugasan Mengajar & Jadwal Pelajaran Mingguan SMP
                $this->seedSchedulesForSMP($classroom, $smpTeachers, $smpSubjects, $classIndex);

                $classIndex++;
            }
        }
    }

    /**
     * Seed Jenjang SMA (Kelas 10, 11, 12, masing-masing 10 kelas = 30 kelas, 32 siswa/kelas = 960 siswa).
     */
    protected function seedSMA(): void
    {
        $this->command->info('-> Mengenerasikan Data Jenjang SMA (30 Kelas: X, XI, XII MIPA 1-5 & IPS 1-5, 32 siswa/kelas)...');

        // 1. Mapel SMA
        $smaSubjectsData = [
            // Wajib Umum
            ['code' => 'PAI-SMA', 'name' => 'Pendidikan Agama Islam & Budi Pekerti', 'group' => 'Wajib Umum', 'prog' => null],
            ['code' => 'PPKN-SMA', 'name' => 'Pendidikan Pancasila & Kewarganegaraan', 'group' => 'Wajib Umum', 'prog' => null],
            ['code' => 'BIND-SMA', 'name' => 'Bahasa Indonesia', 'group' => 'Wajib Umum', 'prog' => null],
            ['code' => 'MAT-SMA', 'name' => 'Matematika Umum', 'group' => 'Wajib Umum', 'prog' => null],
            ['code' => 'BING-SMA', 'name' => 'Bahasa Inggris', 'group' => 'Wajib Umum', 'prog' => null],
            ['code' => 'SEJ-SMA', 'name' => 'Sejarah Indonesia', 'group' => 'Wajib Umum', 'prog' => null],
            ['code' => 'PJOK-SMA', 'name' => 'PJOK', 'group' => 'Wajib Umum', 'prog' => null],
            ['code' => 'SENI-SMA', 'name' => 'Seni Budaya', 'group' => 'Wajib Umum', 'prog' => null],
            // Peminatan MIPA
            ['code' => 'FIS-SMA', 'name' => 'Fisika', 'group' => 'Peminatan MIPA', 'prog' => 'mipa'],
            ['code' => 'KIM-SMA', 'name' => 'Kimia', 'group' => 'Peminatan MIPA', 'prog' => 'mipa'],
            ['code' => 'BIO-SMA', 'name' => 'Biologi', 'group' => 'Peminatan MIPA', 'prog' => 'mipa'],
            ['code' => 'MAT-TL-SMA', 'name' => 'Matematika Tingkat Lanjut', 'group' => 'Peminatan MIPA', 'prog' => 'mipa'],
            // Peminatan IPS
            ['code' => 'EKO-SMA', 'name' => 'Ekonomi', 'group' => 'Peminatan IPS', 'prog' => 'ips'],
            ['code' => 'GEO-SMA', 'name' => 'Geografi', 'group' => 'Peminatan IPS', 'prog' => 'ips'],
            ['code' => 'SOS-SMA', 'name' => 'Sosiologi', 'group' => 'Peminatan IPS', 'prog' => 'ips'],
            ['code' => 'SEJ-TL-SMA', 'name' => 'Sejarah Tingkat Lanjut', 'group' => 'Peminatan IPS', 'prog' => 'ips'],
            // Pilihan
            ['code' => 'INF-SMA', 'name' => 'Informatika Lanjutan', 'group' => 'Pilihan', 'prog' => null],
            ['code' => 'BK-SMA', 'name' => 'Bimbingan Konseling', 'group' => 'Bimbingan', 'prog' => null],
        ];

        $smaSubjects = [];
        foreach ($smaSubjectsData as $idx => $s) {
            $smaSubjects[$s['code']] = Subject::firstOrCreate(
                ['school_id' => $this->school->id, 'code' => $s['code']],
                [
                    'name' => $s['name'],
                    'group' => $s['group'],
                    'educational_level' => 'sma',
                    'academic_program' => $s['prog'],
                    'order' => $idx + 1,
                    'is_active' => true,
                ]
            );
        }

        // 2. Guru SMA (35 Guru Spesifik SMA)
        $smaTeacherNames = [
            'Dr. H. Sukardi, M.Pd.', 'Dra. Hj. Sri Rahayu, M.Si.', 'Ir. Agus Hariyanto, M.T.',
            'Drs. Eko Purwanto, M.M.', 'Laksmi Wardani, M.Pd.', 'Bambang Susanto, S.Pd.',
            'Ratna Juwita, S.Si.', 'Hendy Wijaya, M.Sc.', 'Nursyamsi, S.Pd.',
            'Faisal Rahman, M.Kom.', 'Indra Gunawan, S.Pd.Jas', 'Widya Astuti, S.Pd.',
            'Arif Budiman, S.Pd.', 'Novita Sari, M.Pd.', 'Teguh Wibowo, S.Si.',
            'Putri Permata, S.Pd.', 'Rian Pratama, S.Pd.', 'Siti Marlina, M.Si.',
            'Kuncoro Aji, S.Pd.', 'Yohanes Surya, M.Sc.', 'Dewi Handayani, M.Pd.',
            'Hasan Basri, S.Pd.', 'Dina Mariana, S.Pd.', 'Rahmat Hidayat, M.Kom.',
            'Eka Wulandari, S.Pd.', 'Iwan Setiawan, S.Pd.Jas', 'Maya Safitri, M.Si.',
            'Budi Setiadi, S.Pd.', 'Wahyu Pratama, S.Si.', 'Citra Lestari, S.Pd.',
            'Yoga Aditia, S.Pd.', 'Nurul Aini, S.Pd.', 'Ahmad Fauzan, S.Pd.I.',
            'Desi Ratnasari, M.Pd.', 'Guruh Soekarno, S.Sn.',
        ];

        $smaTeachers = [];
        foreach ($smaTeacherNames as $idx => $name) {
            $tNum = $idx + 1;
            $email = "guru.sma.{$tNum}@eduvia.test";

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'school_id' => $this->school->id,
                    'name' => $name,
                    'role' => UserRole::TEACHER->value,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'password' => $this->defaultPassword,
                ]
            );

            StaffProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'school_id' => $this->school->id,
                    'nip' => '196'.str_pad((string) (70 + $idx), 2, '0', STR_PAD_LEFT).'0815199803'.($idx % 2 === 0 ? '1' : '2').str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT),
                    'nuptk' => '34567890123456'.str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT),
                    'gender' => $idx % 2 === 0 ? 'male' : 'female',
                    'position' => StaffPosition::SUBJECT_TEACHER->value,
                    'work_unit' => 'Unit SMA Nusantara',
                    'employment_status' => 'gty',
                    'join_date' => '2016-07-15',
                    'last_education' => 'S1 / S2 / S3 Kependidikan',
                    'status' => 'active',
                    'activated_at' => now(),
                ]
            );

            $smaTeachers[] = $user;
        }

        // 3. Buat Kelas SMA: Tingkat 10, 11, 12 masing-masing 10 rombel (5 MIPA & 5 IPS) -> Total 30 Kelas
        $levels = [
            10 => ['prefix' => 'X', 'level_num' => 10, 'birth_year' => 2010],
            11 => ['prefix' => 'XI', 'level_num' => 11, 'birth_year' => 2009],
            12 => ['prefix' => 'XII', 'level_num' => 12, 'birth_year' => 2008],
        ];

        $classIndex = 0;
        foreach ($levels as $lvlNum => $meta) {
            $grade = GradeLevel::where('educational_level', 'sma')->where('level_number', $lvlNum)->first();

            // 5 Kelas MIPA (1 s/d 5)
            for ($i = 1; $i <= 5; $i++) {
                $className = "{$meta['prefix']} MIPA {$i}";
                $homeroomTeacher = $smaTeachers[$classIndex % count($smaTeachers)];

                $classroom = Classroom::firstOrCreate(
                    [
                        'school_id' => $this->school->id,
                        'academic_year_id' => $this->academicYear->id,
                        'name' => $className,
                    ],
                    [
                        'grade_level_id' => $grade->id,
                        'homeroom_teacher_id' => $homeroomTeacher->id,
                        'code' => "SMA-{$lvlNum}MIPA{$i}",
                        'capacity' => 32,
                        'academic_program' => 'mipa',
                        'is_active' => true,
                        'notes' => "Rombongan Belajar {$className} SMA",
                    ]
                );

                $prefix = strtolower("sma-{$lvlNum}mipa{$i}");
                $this->seedStudentsAndParentsForClassroom($classroom, 32, $prefix, 30000 + ($classIndex * 100), $meta['birth_year']);
                $this->seedSchedulesForSMA($classroom, $smaTeachers, $smaSubjects, 'mipa', $classIndex);

                $classIndex++;
            }

            // 5 Kelas IPS (1 s/d 5)
            for ($i = 1; $i <= 5; $i++) {
                $className = "{$meta['prefix']} IPS {$i}";
                $homeroomTeacher = $smaTeachers[$classIndex % count($smaTeachers)];

                $classroom = Classroom::firstOrCreate(
                    [
                        'school_id' => $this->school->id,
                        'academic_year_id' => $this->academicYear->id,
                        'name' => $className,
                    ],
                    [
                        'grade_level_id' => $grade->id,
                        'homeroom_teacher_id' => $homeroomTeacher->id,
                        'code' => "SMA-{$lvlNum}IPS{$i}",
                        'capacity' => 32,
                        'academic_program' => 'ips',
                        'is_active' => true,
                        'notes' => "Rombongan Belajar {$className} SMA",
                    ]
                );

                $prefix = strtolower("sma-{$lvlNum}ips{$i}");
                $this->seedStudentsAndParentsForClassroom($classroom, 32, $prefix, 30000 + ($classIndex * 100), $meta['birth_year']);
                $this->seedSchedulesForSMA($classroom, $smaTeachers, $smaSubjects, 'ips', $classIndex);

                $classIndex++;
            }
        }
    }

    /**
     * Helper efisien untuk membuat siswa, profil siswa, akun orang tua, dan relasi orang tua-anak secara batch.
     */
    protected function seedStudentsAndParentsForClassroom(
        Classroom $classroom,
        int $studentCount,
        string $prefix,
        int $startNis,
        int $birthYear
    ): void {
        $firstNamesL = ['Ahmad', 'Dimas', 'Fajar', 'Guntur', 'Hendra', 'Ilham', 'Joko', 'Kurnia', 'Lukman', 'Rizky', 'Bagus', 'Candra', 'Dwi', 'Gilang', 'Indra', 'Kevin'];
        $firstNamesP = ['Ayu', 'Citra', 'Dewi', 'Fitri', 'Gita', 'Indah', 'Lestari', 'Maya', 'Nanda', 'Putri', 'Ratna', 'Sari', 'Tri', 'Wulandari', 'Zahra', 'Anisa'];
        $lastNames = ['Pratama', 'Saputra', 'Setiawan', 'Kusuma', 'Santoso', 'Wijaya', 'Permana', 'Hidayat', 'Nugraha', 'Wibowo', 'Firmansyah', 'Purnama'];
        $parentTitles = ['Bpk. ', 'Ibu '];

        for ($i = 1; $i <= $studentCount; $i++) {
            $isFemale = ($i % 2 === 0);
            $fn = $isFemale ? $firstNamesP[($i - 1) % count($firstNamesP)] : $firstNamesL[($i - 1) % count($firstNamesL)];
            $ln = $lastNames[($i - 1) % count($lastNames)];
            $studentName = "{$fn} {$ln}";

            $studentEmail = "{$prefix}.siswa".str_pad((string) $i, 2, '0', STR_PAD_LEFT).'@eduvia.test';
            $parentEmail = "{$prefix}.ortu".str_pad((string) $i, 2, '0', STR_PAD_LEFT).'@eduvia.test';

            $nis = (string) ($startNis + $i);
            $nisn = '00'.substr((string) $birthYear, -2).str_pad((string) ($startNis + $i), 6, '0', STR_PAD_LEFT);

            // 1. Akun Siswa
            $student = User::firstOrCreate(
                ['email' => $studentEmail],
                [
                    'school_id' => $this->school->id,
                    'name' => $studentName,
                    'role' => UserRole::STUDENT->value,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'password' => $this->defaultPassword,
                ]
            );

            // 2. Akun Orang Tua
            $parentGender = ($i % 3 === 0) ? 'female' : 'male';
            $parentName = ($parentGender === 'male' ? 'Bpk. ' : 'Ibu ').$lastNames[($i + 2) % count($lastNames)].' '.$ln;
            $parentRelation = ($parentGender === 'male') ? 'father' : 'mother';

            $parent = User::firstOrCreate(
                ['email' => $parentEmail],
                [
                    'school_id' => $this->school->id,
                    'name' => $parentName,
                    'role' => UserRole::PARENT->value,
                    'phone' => '0812'.str_pad((string) ($startNis + $i), 8, '0', STR_PAD_LEFT),
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'password' => $this->defaultPassword,
                ]
            );

            // 3. Relasi Orang Tua - Siswa
            ParentStudentRelation::firstOrCreate(
                [
                    'school_id' => $this->school->id,
                    'parent_id' => $parent->id,
                    'student_id' => $student->id,
                ],
                [
                    'relationship_type' => $parentRelation,
                    'status' => ParentStudentRelation::STATUS_ACTIVE,
                    'is_primary_contact' => true,
                    'verified_at' => now(),
                ]
            );

            // 4. Profil Siswa
            StudentProfile::firstOrCreate(
                ['user_id' => $student->id],
                [
                    'school_id' => $this->school->id,
                    'nis' => $nis,
                    'nisn' => $nisn,
                    'gender' => $isFemale ? 'female' : 'male',
                    'birth_place' => 'Jakarta',
                    'birth_date' => "{$birthYear}-0".(($i % 9) + 1).'-'.str_pad((string) (($i % 25) + 1), 2, '0', STR_PAD_LEFT),
                    'religion' => 'Islam',
                    'address' => 'Jl. Pendidikan Nusantara No. '.($i + 1).', Jakarta',
                    'parent_name' => $parentName,
                    'parent_phone' => $parent->phone,
                    'parent_relationship' => $parentRelation === 'father' ? 'Ayah' : 'Ibu',
                    'student_status' => 'active',
                    'enrollment_date' => '2026-07-15',
                ]
            );

            // 5. Penempatan Siswa di Kelas
            ClassroomStudent::firstOrCreate(
                [
                    'classroom_id' => $classroom->id,
                    'student_id' => $student->id,
                    'academic_year_id' => $this->academicYear->id,
                ],
                [
                    'enrollment_status' => 'active',
                    'joined_at' => '2026-07-15',
                ]
            );
        }
    }

    /**
     * Jadwal Mingguan SD (Senin s/d Jumat).
     */
    protected function seedSchedulesForSD(Classroom $classroom, User $homeroomTeacher, array $teachers, array $subjects): void
    {
        $subjectPlan = [
            1 => [ // Senin
                ['sub' => 'PAI-SD', 'teacher' => $teachers['guru.sd.pai@eduvia.test']->id, 'start' => '07:15', 'end' => '08:35'],
                ['sub' => 'BIND-SD', 'teacher' => $homeroomTeacher->id, 'start' => '08:50', 'end' => '10:10'],
                ['sub' => 'PPKN-SD', 'teacher' => $homeroomTeacher->id, 'start' => '10:30', 'end' => '11:50'],
            ],
            2 => [ // Selasa
                ['sub' => 'MAT-SD', 'teacher' => $homeroomTeacher->id, 'start' => '07:15', 'end' => '08:35'],
                ['sub' => 'IPAS-SD', 'teacher' => $homeroomTeacher->id, 'start' => '08:50', 'end' => '10:10'],
                ['sub' => 'BING-SD', 'teacher' => $teachers['guru.sd.bing@eduvia.test']->id, 'start' => '10:30', 'end' => '11:50'],
            ],
            3 => [ // Rabu
                ['sub' => 'PJOK-SD', 'teacher' => $teachers['guru.sd.pjok@eduvia.test']->id, 'start' => '07:15', 'end' => '08:35'],
                ['sub' => 'BIND-SD', 'teacher' => $homeroomTeacher->id, 'start' => '08:50', 'end' => '10:10'],
                ['sub' => 'SENI-SD', 'teacher' => $teachers['guru.sd.seni@eduvia.test']->id, 'start' => '10:30', 'end' => '11:50'],
            ],
            4 => [ // Kamis
                ['sub' => 'MAT-SD', 'teacher' => $homeroomTeacher->id, 'start' => '07:15', 'end' => '08:35'],
                ['sub' => 'IPAS-SD', 'teacher' => $homeroomTeacher->id, 'start' => '08:50', 'end' => '10:10'],
                ['sub' => 'PPKN-SD', 'teacher' => $homeroomTeacher->id, 'start' => '10:30', 'end' => '11:50'],
            ],
            5 => [ // Jumat
                ['sub' => 'PAI-SD', 'teacher' => $teachers['guru.sd.pai@eduvia.test']->id, 'start' => '07:15', 'end' => '08:35'],
                ['sub' => 'BIND-SD', 'teacher' => $homeroomTeacher->id, 'start' => '08:50', 'end' => '10:10'],
            ],
        ];

        $this->createAssignmentsAndSchedules($classroom, $subjectPlan, $subjects);
    }

    /**
     * Jadwal Mingguan SMP (Senin s/d Jumat).
     */
    protected function seedSchedulesForSMP(Classroom $classroom, array $teachers, array $subjects, int $offset): void
    {
        $tCount = count($teachers);

        $subjectPlan = [
            1 => [ // Senin
                ['sub' => 'PAI-SMP', 'teacher' => $teachers[$offset % $tCount]->id, 'start' => '07:15', 'end' => '08:45'],
                ['sub' => 'PPKN-SMP', 'teacher' => $teachers[($offset + 1) % $tCount]->id, 'start' => '09:00', 'end' => '10:30'],
                ['sub' => 'BIND-SMP', 'teacher' => $teachers[($offset + 2) % $tCount]->id, 'start' => '10:45', 'end' => '12:15'],
                ['sub' => 'INF-SMP', 'teacher' => $teachers[($offset + 3) % $tCount]->id, 'start' => '13:00', 'end' => '14:30'],
            ],
            2 => [ // Selasa
                ['sub' => 'MAT-SMP', 'teacher' => $teachers[($offset + 4) % $tCount]->id, 'start' => '07:15', 'end' => '08:45'],
                ['sub' => 'IPA-SMP', 'teacher' => $teachers[($offset + 5) % $tCount]->id, 'start' => '09:00', 'end' => '10:30'],
                ['sub' => 'BING-SMP', 'teacher' => $teachers[($offset + 6) % $tCount]->id, 'start' => '10:45', 'end' => '12:15'],
                ['sub' => 'SENI-SMP', 'teacher' => $teachers[($offset + 7) % $tCount]->id, 'start' => '13:00', 'end' => '14:30'],
            ],
            3 => [ // Rabu
                ['sub' => 'PJOK-SMP', 'teacher' => $teachers[($offset + 8) % $tCount]->id, 'start' => '07:15', 'end' => '08:45'],
                ['sub' => 'IPS-SMP', 'teacher' => $teachers[($offset + 9) % $tCount]->id, 'start' => '09:00', 'end' => '10:30'],
                ['sub' => 'BIND-SMP', 'teacher' => $teachers[($offset + 2) % $tCount]->id, 'start' => '10:45', 'end' => '12:15'],
                ['sub' => 'PRA-SMP', 'teacher' => $teachers[($offset + 10) % $tCount]->id, 'start' => '13:00', 'end' => '14:30'],
            ],
            4 => [ // Kamis
                ['sub' => 'MAT-SMP', 'teacher' => $teachers[($offset + 4) % $tCount]->id, 'start' => '07:15', 'end' => '08:45'],
                ['sub' => 'IPA-SMP', 'teacher' => $teachers[($offset + 5) % $tCount]->id, 'start' => '09:00', 'end' => '10:30'],
                ['sub' => 'BING-SMP', 'teacher' => $teachers[($offset + 6) % $tCount]->id, 'start' => '10:45', 'end' => '12:15'],
                ['sub' => 'BK-SMP', 'teacher' => $teachers[($offset + 11) % $tCount]->id, 'start' => '13:00', 'end' => '14:30'],
            ],
            5 => [ // Jumat
                ['sub' => 'IPS-SMP', 'teacher' => $teachers[($offset + 9) % $tCount]->id, 'start' => '07:15', 'end' => '08:45'],
                ['sub' => 'INF-SMP', 'teacher' => $teachers[($offset + 3) % $tCount]->id, 'start' => '09:00', 'end' => '10:30'],
            ],
        ];

        $this->createAssignmentsAndSchedules($classroom, $subjectPlan, $subjects);
    }

    /**
     * Jadwal Mingguan SMA (Senin s/d Jumat disesuaikan MIPA dan IPS).
     */
    protected function seedSchedulesForSMA(Classroom $classroom, array $teachers, array $subjects, string $program, int $offset): void
    {
        $tCount = count($teachers);

        if ($program === 'mipa') {
            $peminatan1 = 'FIS-SMA';
            $peminatan2 = 'KIM-SMA';
            $peminatan3 = 'BIO-SMA';
            $peminatan4 = 'MAT-TL-SMA';
        } else {
            $peminatan1 = 'EKO-SMA';
            $peminatan2 = 'GEO-SMA';
            $peminatan3 = 'SOS-SMA';
            $peminatan4 = 'SEJ-TL-SMA';
        }

        $subjectPlan = [
            1 => [ // Senin
                ['sub' => 'PAI-SMA', 'teacher' => $teachers[$offset % $tCount]->id, 'start' => '07:15', 'end' => '08:45'],
                ['sub' => 'BIND-SMA', 'teacher' => $teachers[($offset + 1) % $tCount]->id, 'start' => '09:00', 'end' => '10:30'],
                ['sub' => $peminatan1, 'teacher' => $teachers[($offset + 2) % $tCount]->id, 'start' => '10:45', 'end' => '12:15'],
                ['sub' => 'PPKN-SMA', 'teacher' => $teachers[($offset + 3) % $tCount]->id, 'start' => '13:00', 'end' => '14:30'],
            ],
            2 => [ // Selasa
                ['sub' => 'MAT-SMA', 'teacher' => $teachers[($offset + 4) % $tCount]->id, 'start' => '07:15', 'end' => '08:45'],
                ['sub' => $peminatan2, 'teacher' => $teachers[($offset + 5) % $tCount]->id, 'start' => '09:00', 'end' => '10:30'],
                ['sub' => 'BING-SMA', 'teacher' => $teachers[($offset + 6) % $tCount]->id, 'start' => '10:45', 'end' => '12:15'],
                ['sub' => 'SENI-SMA', 'teacher' => $teachers[($offset + 7) % $tCount]->id, 'start' => '13:00', 'end' => '14:30'],
            ],
            3 => [ // Rabu
                ['sub' => 'PJOK-SMA', 'teacher' => $teachers[($offset + 8) % $tCount]->id, 'start' => '07:15', 'end' => '08:45'],
                ['sub' => $peminatan3, 'teacher' => $teachers[($offset + 9) % $tCount]->id, 'start' => '09:00', 'end' => '10:30'],
                ['sub' => 'SEJ-SMA', 'teacher' => $teachers[($offset + 10) % $tCount]->id, 'start' => '10:45', 'end' => '12:15'],
                ['sub' => 'INF-SMA', 'teacher' => $teachers[($offset + 11) % $tCount]->id, 'start' => '13:00', 'end' => '14:30'],
            ],
            4 => [ // Kamis
                ['sub' => $peminatan4, 'teacher' => $teachers[($offset + 12) % $tCount]->id, 'start' => '07:15', 'end' => '08:45'],
                ['sub' => 'MAT-SMA', 'teacher' => $teachers[($offset + 4) % $tCount]->id, 'start' => '09:00', 'end' => '10:30'],
                ['sub' => $peminatan1, 'teacher' => $teachers[($offset + 2) % $tCount]->id, 'start' => '10:45', 'end' => '12:15'],
                ['sub' => 'BK-SMA', 'teacher' => $teachers[($offset + 13) % $tCount]->id, 'start' => '13:00', 'end' => '14:30'],
            ],
            5 => [ // Jumat
                ['sub' => 'BING-SMA', 'teacher' => $teachers[($offset + 6) % $tCount]->id, 'start' => '07:15', 'end' => '08:45'],
                ['sub' => $peminatan2, 'teacher' => $teachers[($offset + 5) % $tCount]->id, 'start' => '09:00', 'end' => '10:30'],
            ],
        ];

        $this->createAssignmentsAndSchedules($classroom, $subjectPlan, $subjects);
    }

    /**
     * Simpan Teaching Assignment dan Lesson Schedules.
     */
    protected function createAssignmentsAndSchedules(Classroom $classroom, array $weeklyPlan, array $subjects): void
    {
        foreach ($weeklyPlan as $dayOfWeek => $slots) {
            foreach ($slots as $slot) {
                if (! isset($subjects[$slot['sub']])) {
                    continue;
                }

                $subject = $subjects[$slot['sub']];

                // Penugasan Mengajar
                $assignment = TeachingAssignment::firstOrCreate(
                    [
                        'school_id' => $this->school->id,
                        'academic_year_id' => $this->academicYear->id,
                        'classroom_id' => $classroom->id,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'semester_id' => $this->semesterGanjil->id,
                        'teacher_id' => $slot['teacher'],
                        'hours_per_week' => 2,
                        'is_active' => true,
                    ]
                );

                // Jadwal Mingguan
                LessonSchedule::firstOrCreate(
                    [
                        'school_id' => $this->school->id,
                        'teaching_assignment_id' => $assignment->id,
                        'day_of_week' => $dayOfWeek,
                        'start_time' => $slot['start'],
                    ],
                    [
                        'end_time' => $slot['end'],
                        'room' => "Ruang {$classroom->name}",
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
