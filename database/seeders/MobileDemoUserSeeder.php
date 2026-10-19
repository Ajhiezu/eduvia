<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\FeeCategory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LearningMaterial;
use App\Models\LessonSchedule;
use App\Models\ParentStudentRelation;
use App\Models\School;
use App\Models\SchoolAnnouncement;
use App\Models\Semester;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MobileDemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('password');
        $school = School::first();
        $academicYear = AcademicYear::first();
        $semester = Semester::first();
        $classroom = Classroom::first();
        $teacher = User::where('role', UserRole::TEACHER->value)->first();

        // 1. Student Demo User (student@eduvia.test) & Real Student (siswa@eduvia.test)
        $studentDemo = User::firstOrCreate(
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

        $siswaUser = User::where('email', 'siswa@eduvia.test')->first();
        $students = array_filter([$studentDemo, $siswaUser]);

        foreach ($students as $s) {
            if ($classroom && $academicYear) {
                ClassroomStudent::firstOrCreate(
                    [
                        'classroom_id' => $classroom->id,
                        'student_id' => $s->id,
                        'academic_year_id' => $academicYear->id,
                    ],
                    [
                        'enrollment_status' => 'active',
                        'joined_at' => '2025-07-14',
                    ]
                );

                StudentProfile::firstOrCreate(
                    ['user_id' => $s->id],
                    [
                        'school_id' => $school?->id ?? 1,
                        'nis' => '202507001',
                        'nisn' => '0081234567',
                        'gender' => 'male',
                    ]
                );
            }
        }

        // 2. Parent Users (parent@eduvia.test & orangtua@eduvia.test)
        $parentDemo = User::firstOrCreate(
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

        $parents = array_filter([$parentDemo, User::where('email', 'orangtua@eduvia.test')->first()]);

        foreach ($parents as $p) {
            foreach ($students as $s) {
                ParentStudentRelation::firstOrCreate(
                    [
                        'school_id' => $school?->id ?? 1,
                        'parent_id' => $p->id,
                        'student_id' => $s->id,
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
        }

        // 3. School Announcements (Pengumuman Sekolah di DB)
        $announcements = [
            [
                'title' => 'Asesmen Sumatif Tengah Semester (ASTS) Ganjil 2026/2027',
                'category' => 'academic',
                'target_audience' => 'all',
                'content' => 'Pelaksanaan ASTS Ganjil akan dimulai pada hari Senin mendatang melalui platform CBT Eduvia. Seluruh peserta didik dimohon mempersiapkan akun dan perangkat masing-masing.',
                'is_pinned' => true,
            ],
            [
                'title' => 'Pemberitahuan Pelaksanaan Ekstrakurikuler Wajib Pramuka & Pilihan',
                'category' => 'general',
                'target_audience' => 'all',
                'content' => 'Kegiatan ekstrakurikuler tatap muka semester ganjil aktif mulai Sabtu pekan ini. Jadwal dan pembagian ruangan dapat dicek pada menu aktivitas.',
                'is_pinned' => false,
            ],
            [
                'title' => 'Pertemuan Wali Murid & Pembagian Laporan Progres Belajar',
                'category' => 'general',
                'target_audience' => 'parents',
                'content' => 'Undangan silaturahmi komite orang tua dan wali kelas VII-A yang akan diselenggarakan secara luring di Aula Utama Nusantara.',
                'is_pinned' => false,
            ],
        ];

        foreach ($announcements as $ann) {
            SchoolAnnouncement::firstOrCreate(
                ['school_id' => $school?->id ?? 1, 'title' => $ann['title']],
                [
                    'created_by' => 1,
                    'slug' => Str::slug($ann['title']),
                    'content' => $ann['content'],
                    'category' => $ann['category'],
                    'target_audience' => $ann['target_audience'],
                    'status' => 'published',
                    'is_pinned' => $ann['is_pinned'],
                    'published_at' => now()->subDays(2),
                ]
            );
        }

        // 4. Subjects & Teaching Assignments & Lesson Schedules
        $subjectsData = [
            ['code' => 'MAT-7', 'name' => 'Matematika'],
            ['code' => 'BIN-7', 'name' => 'Bahasa Indonesia'],
            ['code' => 'IPA-7', 'name' => 'Ilmu Pengetahuan Alam (IPA)'],
            ['code' => 'BIG-7', 'name' => 'Bahasa Inggris'],
            ['code' => 'INF-7', 'name' => 'Informatika'],
        ];

        $subjectModels = [];
        foreach ($subjectsData as $sData) {
            $subjectModels[$sData['code']] = Subject::firstOrCreate(
                ['school_id' => $school?->id ?? 1, 'code' => $sData['code']],
                ['name' => $sData['name'], 'is_active' => true]
            );
        }

        $teachingAssignments = [];
        if ($classroom && $academicYear && $teacher) {
            foreach ($subjectModels as $code => $subj) {
                $teachingAssignments[$code] = TeachingAssignment::firstOrCreate(
                    [
                        'school_id' => $school?->id ?? 1,
                        'classroom_id' => $classroom->id,
                        'subject_id' => $subj->id,
                        'academic_year_id' => $academicYear->id,
                    ],
                    [
                        'teacher_id' => $teacher->id,
                        'is_active' => true,
                    ]
                );
            }

            // Schedules (Senin s/d Jumat)
            $schedules = [
                ['ta' => 'MAT-7', 'day' => 1, 'start' => '07:30:00', 'end' => '09:00:00', 'room' => 'R-7A'],
                ['ta' => 'BIN-7', 'day' => 1, 'start' => '09:15:00', 'end' => '10:45:00', 'room' => 'R-7A'],
                ['ta' => 'IPA-7', 'day' => 2, 'start' => '07:30:00', 'end' => '09:00:00', 'room' => 'Lab IPA'],
                ['ta' => 'BIG-7', 'day' => 2, 'start' => '09:15:00', 'end' => '10:45:00', 'room' => 'R-7A'],
                ['ta' => 'INF-7', 'day' => 3, 'start' => '07:30:00', 'end' => '09:30:00', 'room' => 'Lab Komputer'],
                ['ta' => 'MAT-7', 'day' => 4, 'start' => '07:30:00', 'end' => '09:00:00', 'room' => 'R-7A'],
                ['ta' => 'BIN-7', 'day' => 5, 'start' => '07:30:00', 'end' => '09:00:00', 'room' => 'R-7A'],
            ];

            foreach ($schedules as $sched) {
                if (isset($teachingAssignments[$sched['ta']])) {
                    LessonSchedule::firstOrCreate(
                        [
                            'teaching_assignment_id' => $teachingAssignments[$sched['ta']]->id,
                            'day_of_week' => $sched['day'],
                            'start_time' => $sched['start'],
                        ],
                        [
                            'school_id' => $school?->id ?? 1,
                            'end_time' => $sched['end'],
                            'room' => $sched['room'],
                            'is_active' => true,
                        ]
                    );
                }
            }

            // 5. Learning Materials (Materi Belajar di DB)
            $materials = [
                [
                    'ta' => 'MAT-7',
                    'title' => 'Bab 1: Bilangan Bulat dan Operasi Pecahan',
                    'topic' => 'Aljabar & Bilangan',
                    'description' => 'Materi pendalaman operasi penjumlahan, pengurangan, perkalian pecahan serta aplikasinya.',
                ],
                [
                    'ta' => 'BIN-7',
                    'title' => 'Bab 2: Struktur Teks Deskripsi & Kaidah Kebahasaan',
                    'topic' => 'Teks Deskripsi',
                    'description' => 'Memahami ciri umum teks deskripsi, struktur identifikasi, dan variasi majas penegasan.',
                ],
                [
                    'ta' => 'IPA-7',
                    'title' => 'Bab 1: Hakikat Ilmu Sains dan Pengukuran Ilmiah',
                    'topic' => 'Pengukuran & Besaran',
                    'description' => 'Pengenalan alat ukur laboratorium, keselamatan kerja, dan besaran pokok serta turunan.',
                ],
            ];

            foreach ($materials as $mat) {
                if (isset($teachingAssignments[$mat['ta']])) {
                    $ta = $teachingAssignments[$mat['ta']];
                    LearningMaterial::firstOrCreate(
                        [
                            'teaching_assignment_id' => $ta->id,
                            'title' => $mat['title'],
                        ],
                        [
                            'school_id' => $school?->id ?? 1,
                            'classroom_id' => $classroom->id,
                            'subject_id' => $ta->subject_id,
                            'teacher_id' => $ta->teacher_id,
                            'topic' => $mat['topic'],
                            'description' => $mat['description'],
                            'status' => 'published',
                            'published_at' => now()->subDays(3),
                        ]
                    );
                }
            }

            // 6. Assignments (Tugas Belajar di DB)
            $assignments = [
                [
                    'ta' => 'MAT-7',
                    'title' => 'Latihan Soal Aljabar & Operasi Pecahan Mandiri',
                    'topic' => 'Tugas Mandiri 1',
                    'instructions' => 'Kerjakan soal pada halaman 24 nomor 1 sampai 10 di buku latihan dan kumpulkan dalam format PDF atau foto yang jelas.',
                    'due' => now()->addDays(2)->setTime(23, 59),
                    'max_score' => 100,
                ],
                [
                    'ta' => 'BIN-7',
                    'title' => 'Menulis Paragraf Deskripsi Tempat Bersejarah Sekitar',
                    'topic' => 'Tugas Proyek 1',
                    'instructions' => 'Pilih salah satu tempat wisata/budaya lokal, lalu susun teks deskripsi minimal 3 paragraf dengan memperhatikan EYD.',
                    'due' => now()->addDays(5)->setTime(23, 59),
                    'max_score' => 100,
                ],
            ];

            foreach ($assignments as $asgn) {
                if (isset($teachingAssignments[$asgn['ta']])) {
                    $ta = $teachingAssignments[$asgn['ta']];
                    Assignment::firstOrCreate(
                        [
                            'teaching_assignment_id' => $ta->id,
                            'title' => $asgn['title'],
                        ],
                        [
                            'school_id' => $school?->id ?? 1,
                            'classroom_id' => $classroom->id,
                            'subject_id' => $ta->subject_id,
                            'teacher_id' => $ta->teacher_id,
                            'topic' => $asgn['topic'],
                            'instructions' => $asgn['instructions'],
                            'due_date' => $asgn['due'],
                            'max_score' => $asgn['max_score'],
                            'status' => 'published',
                            'published_at' => now()->subDay(),
                        ]
                    );
                }
            }
        }

        // 7. Finance & Invoices (SPP & Tagihan Sekolah di DB)
        $feeCategorySpp = FeeCategory::firstOrCreate(
            ['school_id' => $school?->id ?? 1, 'code' => 'SPP-BULANAN'],
            [
                'name' => 'SPP Bulanan',
                'description' => 'Iuran Sumbangan Pembinaan Pendidikan bulanan siswa',
                'type' => 'recurring',
                'billing_period' => 'monthly',
                'amount' => 350000,
                'is_active' => true,
            ]
        );

        $feeCategoryEks = FeeCategory::firstOrCreate(
            ['school_id' => $school?->id ?? 1, 'code' => 'KEGIATAN-EKS'],
            [
                'name' => 'Iuran Ekstrakurikuler & OSIS',
                'description' => 'Iuran pengembangan minat bakat dan kegiatan kesiswaan',
                'type' => 'one_time',
                'billing_period' => 'semester',
                'amount' => 150000,
                'is_active' => true,
            ]
        );

        foreach ($students as $st) {
            // Tagihan 1: SPP Oktober 2026 (Belum Lunas / Issued)
            $inv1 = Invoice::firstOrCreate(
                [
                    'school_id' => $school?->id ?? 1,
                    'student_id' => $st->id,
                    'fee_category_id' => $feeCategorySpp->id,
                    'billing_period_key' => '2026-10',
                ],
                [
                    'invoice_number' => 'INV-202610-'.$st->id.'-'.Str::upper(Str::random(4)),
                    'academic_year_id' => $academicYear?->id ?? 1,
                    'title' => 'SPP Siswa Bulan Oktober 2026',
                    'issue_date' => '2026-10-01',
                    'due_date' => '2026-10-25',
                    'subtotal' => 350000,
                    'total_amount' => 350000,
                    'paid_amount' => 0,
                    'status' => 'issued',
                    'notes' => 'Harap diselesaikan sebelum tanggal jatuh tempo 25 Oktober 2026.',
                ]
            );

            InvoiceItem::firstOrCreate(
                ['invoice_id' => $inv1->id, 'item_name' => 'SPP Bulan Oktober 2026'],
                ['unit_price' => 350000, 'quantity' => 1, 'subtotal' => 350000]
            );

            // Tagihan 2: SPP September 2026 (Lunas / Paid)
            $inv2 = Invoice::firstOrCreate(
                [
                    'school_id' => $school?->id ?? 1,
                    'student_id' => $st->id,
                    'fee_category_id' => $feeCategorySpp->id,
                    'billing_period_key' => '2026-09',
                ],
                [
                    'invoice_number' => 'INV-202609-'.$st->id.'-'.Str::upper(Str::random(4)),
                    'academic_year_id' => $academicYear?->id ?? 1,
                    'title' => 'SPP Siswa Bulan September 2026',
                    'issue_date' => '2026-09-01',
                    'due_date' => '2026-09-25',
                    'subtotal' => 350000,
                    'total_amount' => 350000,
                    'paid_amount' => 350000,
                    'status' => 'paid',
                    'notes' => 'Lunas via Virtual Account Bank pada 10 September 2026.',
                ]
            );

            InvoiceItem::firstOrCreate(
                ['invoice_id' => $inv2->id, 'item_name' => 'SPP Bulan September 2026'],
                ['unit_price' => 350000, 'quantity' => 1, 'subtotal' => 350000]
            );
        }

        // 8. Attendance Sessions & Records (Presensi Siswa di DB)
        if ($classroom && $academicYear && $teacher) {
            $dates = [
                ['date' => Carbon::now()->subDays(4)->format('Y-m-d'), 'status' => 'present', 'topic' => 'Aljabar Lanjutan'],
                ['date' => Carbon::now()->subDays(3)->format('Y-m-d'), 'status' => 'present', 'topic' => 'Teks Prosedur'],
                ['date' => Carbon::now()->subDays(2)->format('Y-m-d'), 'status' => 'present', 'topic' => 'Besaran dan Pengukuran'],
                ['date' => Carbon::now()->subDays(1)->format('Y-m-d'), 'status' => 'sick', 'topic' => 'Struktur Kebahasaan'],
                ['date' => Carbon::now()->format('Y-m-d'), 'status' => 'present', 'topic' => 'Asesmen Harian'],
            ];

            foreach ($dates as $idx => $d) {
                $session = AttendanceSession::firstOrCreate(
                    [
                        'classroom_id' => $classroom->id,
                        'date' => $d['date'],
                        'session_type' => 'daily',
                    ],
                    [
                        'school_id' => $school?->id ?? 1,
                        'academic_year_id' => $academicYear->id,
                        'semester_id' => $semester?->id ?? 1,
                        'teacher_id' => $teacher->id,
                        'meeting_number' => $idx + 1,
                        'start_time' => '07:00:00',
                        'end_time' => '07:30:00',
                        'topic' => $d['topic'],
                        'status' => 'finalized',
                        'finalized_at' => now(),
                    ]
                );

                foreach ($students as $st) {
                    AttendanceRecord::firstOrCreate(
                        [
                            'attendance_session_id' => $session->id,
                            'student_id' => $st->id,
                        ],
                        [
                            'status' => $d['status'],
                            'notes' => $d['status'] === 'sick' ? 'Izin sakit flu (surat dokter)' : 'Hadir tepat waktu',
                        ]
                    );
                }
            }
        }
    }
}
