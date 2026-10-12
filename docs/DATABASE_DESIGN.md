# EDUVIA DATABASE FOUNDATION & FUTURE DATA DESIGN
**Version:** 1.0.0 (Phase 0 Foundation)  
**Database Management System:** MySQL / MariaDB (InnoDB Engine)  
**Encoding:** `utf8mb4` with collation `utf8mb4_unicode_ci`  

---

## 1. Prinsip dan Konvensi Basis Data

### A. Konvensi Penamaan (Naming Conventions)
1. **Nama Tabel:** Huruf kecil jamak (*plural snake_case*), contoh: `users`, `schools`, `academic_years`, `classrooms`, `subject_courses`.
2. **Primary Key:** Kolom `id` bertipe unsigned Big Integer Auto Increment (`$table->id()`).
3. **Foreign Key:** Nama model tunggal diikuti `_id` (`$table->foreignId('school_id')->constrained()->cascadeOnDelete()`).
4. **Boolean Flags:** Diawali dengan `is_` atau `has_`, contoh: `is_active`, `is_published`, `has_completed`.
5. **Timestamps:** Standar Laravel `created_at` dan `updated_at` bertipe TIMESTAMP nullable.
6. **Soft Deletes:** Menggunakan `deleted_at` nullable hanya pada entitas yang memiliki implikasi hukum/riwayat akademik (misal: rapor, transkrip nilai siswa).

### B. Integritas Data & Indeks
1. **Integritas Relasional:** Mengandalkan *Foreign Key Constraints* pada level engine InnoDB untuk mencegah *orphaned records*.
2. **Indexing Strategy:**
   - Seluruh kolom yang menjadi filter pencarian rutin diberi index (misal: `role`, `is_active`, `email`, `nisn`, `nip`).
   - Kombinasi unik didefinisikan secara eksplisit via compound unique index (misal: `unique(['student_id', 'academic_year_id', 'classroom_id'])`).
3. **Transaksi Basis Data:** Seluruh operasi multi-tabel (seperti penugasan siswa massal, penerbitan nilai ujian) wajib dibungkus dalam `DB::transaction(function () { ... })` dengan mekanisme rollback otomatis jika terjadi kegagalan.

---

## 2. Pemisahan Lingkungan Basis Data

| Lingkungan | Database Name | Karakteristik & Kegunaan |
| :--- | :--- | :--- |
| **Development** | `eduvia_dev` | Lingkungan kerja lokal developer dengan data dummy terstruktur dari DatabaseSeeder. |
| **Testing** | `eduvia_testing` | Digunakan eksklusif oleh PHPUnit / automated testing runner, dibersihkan dan di-refresh per sesi pengujian. |
| **Staging** | `eduvia_staging` | Replikasi terisolasi lingkungan produksi untuk User Acceptance Testing (UAT). |
| **Production** | `eduvia_prod` | Basis data operasional sekolah dengan proteksi backup berkala, replikasi read-replica jika dibutuhkan, dan restricted credential access. |

---

## 3. Strategi Backup dan Pemulihan (Disaster Recovery)

1. **Scheduled Daily Dump:** Backup otomatis harian via script cron menggunakan `mysqldump` terenkripsi dengan rotasi retensi:
   - Harian: disimpan selama 7 hari.
   - Mingguan: disimpan selama 4 minggu.
   - Bulanan: disimpan selama 12 bulan (arsip akhir semester/tahun ajaran).
2. **Point-In-Time Recovery (PITR):** Mengaktifkan *Binary Logging (binlog)* di server MySQL produksi untuk pemulihan hingga titik waktu transaksi spesifik jika terjadi kesalahan manusia.
3. **Prosedur Restore:**
   ```bash
   # Ekspor terenkripsi
   mysqldump -u [user] -p --single-transaction --quick eduvia_prod > backup_$(date +%F).sql

   # Impor pemulihan ke basis data target
   mysql -u [user] -p eduvia_restored < backup_YYYY-MM-DD.sql
   ```

---

## 4. Struktur Terpasang Fase 0 (Foundation Schema)

Pada Fase 0, **HANYA** tabel fondasi framework dan pengguna yang dibuat:
- `users`: Identitas pengguna, email unik, peran (`role`), nomor telepon, foto profil, dan status keaktifan (`is_active`).
- `password_reset_tokens`: Pengelolaan token lupa kata sandi.
- `sessions`: Penyimpanan sesi web terenkripsi berbasis database.
- `cache` & `cache_locks`: Engine caching aplikasi.
- `jobs` & `failed_jobs`: Infrastruktur antrean asynchronous.

---

## 5. Rancangan Entitas Data Masa Depan (Future Domain Schema)

> [!IMPORTANT]
> Entitas berikut adalah **Cetak Biru (Blueprint)** konseptual yang telah diselaraskan untuk pengembangan Fase 1 dan modul berikutnya. Tabel di bawah ini **TIDAK DIBUAT** pada Fase 0 untuk menjaga fondasi tetap ramping dan terverifikasi.

```
                          +------------------------+
                          |        schools         |
                          +------------------------+
                                      | 1
                                      |
                                      v N
+--------------------+    +------------------------+    +-----------------------+
|   academic_years   |--->|       classrooms       |<---|     school_levels     |
+--------------------+ 1  +------------------------+ 1  +-----------------------+
                                      |                      (SD, SMP, SMA, SMK)
                                      | 1
                                      v N
+--------------------+    +------------------------+    +-----------------------+
|  subject_courses   |--->|    classroom_courses   |<---|         users         |
+--------------------+ 1  +------------------------+ 1  | (Guru / Siswa / Wali) |
                                      |                 +-----------------------+
                                      v 1
                  +---------------------------------------+
                  |  learning_materials & assessments     |
                  +---------------------------------------+
```

### A. Modul Organisasi Sekolah & Tahun Ajaran (Rencana Fase 1)
- `schools`: `id`, `npsn`, `name`, `level` (sd, smp, sma, smk), `address`, `phone`, `logo`, `is_active`.
- `academic_years`: `id`, `school_id`, `name` (e.g. "2026/2027"), `semester` (ganjil, genap), `start_date`, `end_date`, `is_active`.

### B. Modul Kepegawaian & Kesiswaan (Rencana Fase 1)
- `teachers`: `id`, `user_id`, `nip`, `nuptk`, `specialization_field`, `gender`, `status`.
- `students`: `id`, `user_id`, `nis`, `nisn`, `gender`, `birth_place`, `birth_date`, `admission_year`.
- `student_parents`: `id`, `student_id`, `parent_user_id`, `relationship_type` (ayah, ibu, wali).

### C. Modul Akademik & Rombongan Belajar (Rencana Fase 1)
- `classrooms`: `id`, `school_id`, `academic_year_id`, `grade_level` (1-12), `name` (e.g. "VII-A", "X-RPL-1"), `homeroom_teacher_id`.
- `classroom_students`: `id`, `classroom_id`, `student_id`, `status` (aktif, mutasi, lulus).
- `subject_courses`: `id`, `school_id`, `code`, `name`, `curriculum_type` (Kurikulum Merdeka / K13).
- `teaching_assignments`: `id`, `classroom_id`, `subject_course_id`, `teacher_id`, `weekly_hours`.

### D. Modul Pembelajaran & Penilaian (Rencana Fase 2 & 3)
- `learning_materials`: `id`, `teaching_assignment_id`, `title`, `content_type` (pdf, video, rich_text), `file_path`, `is_published`.
- `assignments`: `id`, `teaching_assignment_id`, `title`, `description`, `deadline`, `max_score`.
- `submissions`: `id`, `assignment_id`, `student_id`, `file_path`, `submitted_at`, `score`, `teacher_feedback`.
- `cbt_exams`: `id`, `teaching_assignment_id`, `title`, `duration_minutes`, `start_time`, `end_time`, `is_published`.
- `grade_records`: `id`, `classroom_student_id`, `subject_course_id`, `academic_year_id`, `knowledge_score`, `skill_score`, `final_grade`.
