# EDUVIA — DATA DUMMY MULTI-JENJANG MEMORY & SPECIFICATION (SSOT)
## Single Source of Truth: Data Dummy Lengkap SD, SMP, dan SMA

Dokumen ini merupakan **Single Source of Truth (SSOT)** dan **Persistent Project Memory** untuk seluruh struktur data dummy multi-jenjang (K-12: SD, SMP, SMA) pada platform **Eduvia (E-Learning & School Management System)**. Seluruh pengembang, penguji sistem (QA), dan tim implementasi wajib merujuk dokumen ini untuk skenario testing, demonstrasi fitur, dan verifikasi integritas data.

---

## 1. IKHTISAR SISTEM DATA DUMMY

* **Sekolah Induk (Tenant):** Yayasan Perguruan Nusantara Utama
  * `school_id`: `1` (Slug: `nusantara-utama`, NPSN: `20104567`)
  * `educational_levels`: `["sd", "smp", "sma"]`
* **Tahun Ajaran & Semester:** 
  * Tahun Ajaran: `2026/2027` (Aktif)
  * Semester Ganjil: `2026-07-15` s/d `2026-12-20` (Aktif)
  * Semester Genap: `2027-01-05` s/d `2027-06-20` (Mendatang)
* **Kata Sandi Global (Default Password):** `password123` *(Semua akun: Kepsek, Guru, Siswa, Orang Tua)*
* **File Seeder Sumber:** [`database/seeders/AllLevelsDummyDataSeeder.php`](file:///c:/Antigravity/Eduvia/database/seeders/AllLevelsDummyDataSeeder.php)
* **Master Seeder:** Terintegrasi di [`database/seeders/DatabaseSeeder.php`](file:///c:/Antigravity/Eduvia/database/seeders/DatabaseSeeder.php) (Step 9)

---

## 2. REKAPITULASI KUOTA & DISTRIBUSI ENTITAS

```
========================================================================================
JENJANG       ROMBEL      SISWA/KELAS     TOTAL SISWA     TOTAL ORANG TUA     TOTAL GURU
========================================================================================
SD            6 Kelas         36              216              216             10 Guru
SMP          27 Kelas         32              864              864             30 Guru
SMA          30 Kelas         32              960              960             35 Guru
----------------------------------------------------------------------------------------
TOTAL        63 Rombel        —             2.040 Siswa      2.040 Ortu        75 Guru
========================================================================================
+ 3 Kepala Sekolah Terpisah (SD, SMP, SMA)
+ 1.113 Jadwal Pelajaran Mingguan Berulang (Looping 1 Tahun)
```

---

## 3. SPESIFIKASI DATA PER JENJANG

### A. Jenjang SD (Sekolah Dasar)
* **Tingkat & Rombel:** 6 Rombel (Kelas 1, Kelas 2, Kelas 3, Kelas 4, Kelas 5, Kelas 6).
* **Kapasitas Rombel:** 36 siswa per rombel (Total: 216 siswa).
* **Rentang Kelahiran Siswa:** Tahun 2014 s/d 2019 (Usia 7–12 tahun).
* **Kepala Sekolah:**
  * **Nama:** Dra. Hj. Siti Aminah, M.Pd.
  * **Email:** `kepsek.sd@eduvia.test`
  * **NIP:** `197005121995032001` | **Unit:** SD Nusantara Utama
  * **Pendidikan:** S2 Manajemen Pendidikan Dasar
* **Struktur Guru SD (10 Guru Terpisah):**
  * Guru Kelas 1: `guru.sd.kelas1@eduvia.test` (Wali Kelas 1)
  * Guru Kelas 2: `guru.sd.kelas2@eduvia.test` (Wali Kelas 2)
  * Guru Kelas 3: `guru.sd.kelas3@eduvia.test` (Wali Kelas 3)
  * Guru Kelas 4: `guru.sd.kelas4@eduvia.test` (Wali Kelas 4)
  * Guru Kelas 5: `guru.sd.kelas5@eduvia.test` (Wali Kelas 5)
  * Guru Kelas 6: `guru.sd.kelas6@eduvia.test` (Wali Kelas 6)
  * Guru Pendidikan Agama Islam: `guru.sd.pai@eduvia.test`
  * Guru PJOK: `guru.sd.pjok@eduvia.test`
  * Guru Seni & Budaya: `guru.sd.seni@eduvia.test`
  * Guru Bahasa Inggris: `guru.sd.bing@eduvia.test`
* **Mata Pelajaran Real SD (Kurikulum Merdeka):**
  * `PAI-SD`: Pendidikan Agama Islam dan Budi Pekerti
  * `PPKN-SD`: Pendidikan Pancasila
  * `BIND-SD`: Bahasa Indonesia
  * `MAT-SD`: Matematika
  * `IPAS-SD`: Ilmu Pengetahuan Alam dan Sosial (IPAS)
  * `PJOK-SD`: Pendidikan Jasmani, Olahraga, dan Kesehatan
  * `SENI-SD`: Seni dan Budaya
  * `BING-SD`: Bahasa Inggris
* **Pola Kredensial Siswa & Orang Tua SD:**
  * Siswa: `sd-kelas[1-6].siswa[01-36]@eduvia.test` (Contoh: `sd-kelas1.siswa01@eduvia.test`)
  * Orang Tua: `sd-kelas[1-6].ortu[01-36]@eduvia.test` (Contoh: `sd-kelas1.ortu01@eduvia.test`)
  * NIS: `10101` s/d `10636`

---

### B. Jenjang SMP (Sekolah Menengah Pertama)
* **Tingkat & Rombel:** 27 Rombel (9 Rombel per tingkat):
  * **Kelas 7:** 7A, 7B, 7C, 7D, 7E, 7F, 7G, 7H, 7I
  * **Kelas 8:** 8A, 8B, 8C, 8D, 8E, 8F, 8G, 8H, 8I
  * **Kelas 9:** 9A, 9B, 9C, 9D, 9E, 9F, 9G, 9H, 9I
* **Kapasitas Rombel:** 32 siswa per rombel (Total: 864 siswa).
* **Rentang Kelahiran Siswa:** Tahun 2011 s/d 2013 (Usia 13–15 tahun).
* **Kepala Sekolah:**
  * **Nama:** Drs. H. Bambang Irawan, M.Si.
  * **Email:** `kepsek.smp@eduvia.test`
  * **NIP:** `196808201993031002` | **Unit:** SMP Nusantara Utama
  * **Pendidikan:** S2 Administrasi Pendidikan
* **Struktur Guru SMP (30 Guru Terpisah):**
  * Email Guru: `guru.smp.01@eduvia.test` s/d `guru.smp.30@eduvia.test`
  * Terdistribusi merata sebagai Wali Kelas 7A–9I dan guru mata pelajaran bidang studi.
* **Mata Pelajaran Real SMP (Kurikulum Merdeka):**
  * `PAI-SMP`: Pendidikan Agama Islam dan Budi Pekerti
  * `PPKN-SMP`: Pendidikan Pancasila
  * `BIND-SMP`: Bahasa Indonesia
  * `MAT-SMP`: Matematika
  * `IPA-SMP`: Ilmu Pengetahuan Alam (IPA Terpadu)
  * `IPS-SMP`: Ilmu Pengetahuan Sosial (IPS Terpadu)
  * `BING-SMP`: Bahasa Inggris
  * `INF-SMP`: Informatika
  * `PJOK-SMP`: PJOK
  * `SENI-SMP`: Seni dan Prakarya
  * `BK-SMP`: Bimbingan dan Konseling
* **Pola Kredensial Siswa & Orang Tua SMP:**
  * Siswa: `smp-[7a-9i].siswa[01-32]@eduvia.test` (Contoh: `smp-7a.siswa01@eduvia.test`, `smp-9i.siswa32@eduvia.test`)
  * Orang Tua: `smp-[7a-9i].ortu[01-32]@eduvia.test` (Contoh: `smp-7a.ortu01@eduvia.test`)
  * NIS: `20001` s/d `22632`

---

### C. Jenjang SMA (Sekolah Menengah Atas)
* **Tingkat & Rombel:** 30 Rombel (10 Rombel per tingkat: 5 MIPA & 5 IPS):
  * **Kelas 10 (Fase E):**
    * MIPA: X MIPA 1, X MIPA 2, X MIPA 3, X MIPA 4, X MIPA 5
    * IPS: X IPS 1, X IPS 2, X IPS 3, X IPS 4, X IPS 5
  * **Kelas 11 (Fase F - Peminatan):**
    * MIPA: XI MIPA 1, XI MIPA 2, XI MIPA 3, XI MIPA 4, XI MIPA 5
    * IPS: XI IPS 1, XI IPS 2, XI IPS 3, XI IPS 4, XI IPS 5
  * **Kelas 12 (Fase F - Tingkat Akhir):**
    * MIPA: XII MIPA 1, XII MIPA 2, XII MIPA 3, XII MIPA 4, XII MIPA 5
    * IPS: XII IPS 1, XII IPS 2, XII IPS 3, XII IPS 4, XII IPS 5
* **Kapasitas Rombel:** 32 siswa per rombel (Total: 960 siswa).
* **Rentang Kelahiran Siswa:** Tahun 2008 s/d 2010 (Usia 16–18 tahun).
* **Kepala Sekolah:**
  * **Nama:** Dr. Hendro Prasetyo, M.Pd.
  * **Email:** `kepsek.sma@eduvia.test`
  * **NIP:** `196511101990031003` | **Unit:** SMA Nusantara Utama
  * **Pendidikan:** S3 Kurikulum dan Teknologi Pembelajaran
* **Struktur Guru SMA (35 Guru Terpisah):**
  * Email Guru: `guru.sma.01@eduvia.test` s/d `guru.sma.35@eduvia.test`
  * Mencakup guru rumpun MIPA (Fisika, Kimia, Biologi, Matematika Lanjut), rumpun IPS (Ekonomi, Sosiologi, Geografi, Sejarah), serta rumpun Mapel Umum dan BK.
* **Mata Pelajaran Real SMA:**
  * **Kelompok Mapel Umum:**
    * `PAI-SMA`: Pendidikan Agama Islam dan Budi Pekerti
    * `PPKN-SMA`: Pendidikan Pancasila
    * `BIND-SMA`: Bahasa Indonesia
    * `MAT-SMA`: Matematika (Wajib)
    * `SEJ-SMA`: Sejarah Indonesia
    * `BING-SMA`: Bahasa Inggris
    * `PJOK-SMA`: PJOK
    * `SENI-SMA`: Seni Budaya
    * `INF-SMA`: Informatika
    * `BK-SMA`: Bimbingan dan Konseling
  * **Kelompok Peminatan MIPA:**
    * `FIS-SMA`: Fisika
    * `KIM-SMA`: Kimia
    * `BIO-SMA`: Biologi
    * `MATL-SMA`: Matematika Tingkat Lanjut
  * **Kelompok Peminatan IPS:**
    * `EKO-SMA`: Ekonomi dan Akuntansi
    * `SOS-SMA`: Sosiologi
    * `GEO-SMA`: Geografi
    * `SEJL-SMA`: Sejarah Tingkat Lanjut
* **Pola Kredensial Siswa & Orang Tua SMA:**
  * Siswa: `sma-[10-12][mipa/ips][1-5].siswa[01-32]@eduvia.test` (Contoh: `sma-10mipa1.siswa01@eduvia.test`, `sma-12ips5.siswa32@eduvia.test`)
  * Orang Tua: `sma-[10-12][mipa/ips][1-5].ortu[01-32]@eduvia.test` (Contoh: `sma-10mipa1.ortu01@eduvia.test`)
  * NIS: `30001` s/d `32932`

---

## 4. JADWAL PELAJARAN MINGGUAN (RECURRING WEEKLY TIMETABLE)

Sistem penjadwalan Eduvia menggunakan arsitektur **Template Mingguan Terhubung**:
* **Tabel:** `lesson_schedules` terhubung secara foreign key ke `teaching_assignments`.
* **Hari Aktif Belajar:**
  * `1` = Senin (07:15 – 14:30 WIB)
  * `2` = Selasa (07:15 – 14:30 WIB)
  * `3` = Rabu (07:15 – 14:30 WIB)
  * `4` = Kamis (07:15 – 14:30 WIB)
  * `5` = Jumat (07:15 – 10:30 WIB — setengah hari untuk ibadah Sholat Jumat)
* **Sifat Looping (1 Tahun Ajaran):**
  Karena kalender akademik sekolah berulang setiap pekannya, jadwal mingguan ini secara otomatis berlaku berulang (*recurring loop*) untuk seluruh pertemuan semester ganjil dan genap tanpa perlu duplikasi jadwal harian secara manual.
* **Total Jadwal Aktif:** **1.113 slot jadwal pelajaran** tersimpan dan terpetakan rapi ke masing-masing rombel dan ruang kelas.

---

## 5. SKEMA INTEGRITAS BASIS DATA

Data dummy ini mematuhi standar relasional Eduvia secara komprehensif:

1. **`users`**:
   * Seluruh akun memiliki email valid, `email_verified_at` terisi, dan `is_active = true`.
   * Peran (`role`) menggunakan Enum `App\Enums\UserRole` (`school_admin`, `teacher`, `student`, `parent`).
2. **`staff_profiles`**:
   * Seluruh guru dan kepala sekolah memiliki NIP/NUPTK unik, status kepegawaian (PNS/GTY), unit kerja, dan jabatan `App\Enums\StaffPosition`.
3. **`student_profiles`**:
   * Setiap siswa memiliki NIS dan NISN unik resmi Kemendikbudristek format (10 digit).
   * Data kontak darurat dan alamat terisi lengkap.
4. **`parent_student_relations`**:
   * Terhubung dengan status `active` dan flag `is_primary_contact = true`.
   * Tipe hubungan ditentukan secara realistis (`father` / `mother`).
5. **`classroom_students`**:
   * Penempatan siswa (`enrollment_status = 'active'`) terikat dengan `academic_year_id` 2026/2027.
6. **`teaching_assignments` & `lesson_schedules`**:
   * Memastikan tidak ada penugasan guru fiktif; seluruh guru pengampu benar-benar mengajar mata pelajaran yang sesuai.

---

## 6. CARA MENJALANKAN & MERESET DATA

### A. Menjalankan Seeder Tertarget
Untuk memperbarui atau mere-seed hanya data dummy multi-jenjang:
```bash
php artisan db:seed --class=AllLevelsDummyDataSeeder
```

### B. Menjalankan Seluruh Seeder dari Awal
```bash
php artisan migrate:fresh --seed
```

### C. Skrip Verifikasi Jumlah Data (One-Liner Verification)
Jalankan perintah ini di CLI untuk memeriksa integritas data kapan saja:
```bash
php -r '
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$school = App\Models\School::where("slug", "nusantara-utama")->first();
echo "Classes: " . App\Models\Classroom::where("school_id", $school->id)->count() . "\n";
echo "Students: " . App\Models\User::where("school_id", $school->id)->where("role", "student")->count() . "\n";
echo "Parents: " . App\Models\User::where("school_id", $school->id)->where("role", "parent")->count() . "\n";
echo "Teachers: " . App\Models\User::where("school_id", $school->id)->where("role", "teacher")->count() . "\n";
echo "Schedules: " . App\Models\LessonSchedule::where("school_id", $school->id)->count() . "\n";
'
```

---

## 7. CHEATSHEET LOGIN CEPAT UNTUK TESTING

| Kategori Akun | Email Contoh | Password | Akses Halaman |
| :--- | :--- | :---: | :--- |
| **Kepsek SD** | `kepsek.sd@eduvia.test` | `password123` | Dashboard Admin SD & Supervisi |
| **Kepsek SMP** | `kepsek.smp@eduvia.test` | `password123` | Dashboard Admin SMP & Supervisi |
| **Kepsek SMA** | `kepsek.sma@eduvia.test` | `password123` | Dashboard Admin SMA & Supervisi |
| **Wali Kelas 1 SD** | `guru.sd.kelas1@eduvia.test` | `password123` | Presensi & Rapor Kelas 1 SD |
| **Guru SMP (Matematika)** | `guru.smp.04@eduvia.test` | `password123` | Manajemen Nilai & Tugas SMP |
| **Guru SMA (Fisika)** | `guru.sma.11@eduvia.test` | `password123` | Bank Soal CBT & Jadwal MIPA |
| **Siswa SMP (Kelas 7A)** | `smp-7a.siswa01@eduvia.test` | `password123` | Portal Siswa, Jadwal, Tugas |
| **Orang Tua Siswa 7A** | `smp-7a.ortu01@eduvia.test` | `password123` | Monitoring Anak, Absensi, Tagihan |
| **Siswa SMA (X MIPA 1)** | `sma-10mipa1.siswa01@eduvia.test` | `password123` | Portal Siswa SMA, CBT Online |
| **Orang Tua Siswa X MIPA 1** | `sma-10mipa1.ortu01@eduvia.test` | `password123` | Portal Orang Tua SMA |
