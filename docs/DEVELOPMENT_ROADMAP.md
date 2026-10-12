# EDUVIA PRODUCT DEVELOPMENT ROADMAP
**Platform:** Eduvia E-Learning Platform (SD, SMP, SMA, SMK)  
**Strategy:** Phased Milestone Delivery with Strict Definition of Done (DoD)  

---

## 1. Ikhtisar Tahapan Pengembangan

Untuk menjamin stabilitas sistem, integritas basis data, serta kepuasan pengguna akhir di lingkungan sekolah, Eduvia dibangun melalui tahapan fase terstruktur di mana setiap fase harus diselesaikan, diuji, dan dinyatakan matang sebelum melangkah ke fase berikutnya.

```
+-------------------------------------------------------------------+
| FASE 0: Foundation & Architecture (SELESAI & DIVERIFIKASI)        |
| Setup stack, RBAC model, Security headers, Design system, Tests.  |
+-------------------------------------------------------------------+
                                  |
                                  v
+-------------------------------------------------------------------+
| FASE 1: Core Institutional & Academic Data Management             |
| Konfigurasi Sekolah, Tahun Ajaran, Rombel/Kelas, Siswa & Guru.    |
+-------------------------------------------------------------------+
                                  |
                                  v
+-------------------------------------------------------------------+
| FASE 2: Content & Learning Delivery                               |
| Modul Materi Pembelajaran, Penugasan Siswa, dan Pengumpulan Tugas |
+-------------------------------------------------------------------+
                                  |
                                  v
+-------------------------------------------------------------------+
| FASE 3: Assessment, CBT & Examination                             |
| Bank Soal, Ujian Daring Computer-Based Test (CBT), Timer & Nilai  |
+-------------------------------------------------------------------+
                                  |
                                  v
+-------------------------------------------------------------------+
| FASE 4: Evaluation, Grading & Academic Records                    |
| Buku Nilai Guru, Rekap Presensi, Rapor Kurikulum Merdeka & K13    |
+-------------------------------------------------------------------+
                                  |
                                  v
+-------------------------------------------------------------------+
| FASE 5: Institutional Expansion & Portal Ekosistem                |
| Portal Orang Tua, Fitur SMK (PKL/Magang), PPDB Online             |
+-------------------------------------------------------------------+
```

---

## 2. Rincian Fase & Deliverables

### FASE 0: Project Foundation & Architecture (STATUS: SELESAI)
- **Cakupan:**
  - Inisialisasi arsitektur Modular Monolith berbasis Laravel 12 + PHP 8.3 + MySQL.
  - Setup environment bersih, `.env.example`, `.gitignore`, dan Git repository.
  - Skema tabel pengguna, peran (`UserRole`), dan kapabilitas (`Permission`).
  - Lapis keamanan: `SecurityHeadersMiddleware`, rate-limiting throttle, CSRF, Bcrypt rounds 12.
  - Living Design System: 13+ komponen Blade terpadu, token Tailwind CSS v4, dan interaktivitas Alpine.js.
  - Observability: API health check endpoint `/api/v1/health`.
  - Automated testing suite: 25 pengujian lulus (113 assertions).
- **Definition of Done:** Terpenuhi 100%.

---

### FASE 1: Core Institutional & Academic Management (TARGET BERIKUTNYA)
- **Modul yang Dibangun:**
  1. *School Profile & Tenancy Context:* Profil identitas sekolah, NPSN, jenjang (SD/SMP/SMA/SMK).
  2. *Academic Year & Semester:* Manajemen tahun ajaran aktif, periode semester ganjil/genap.
  3. *Staff & Teacher Registry:* Manajemen pendidik, NIP/NUPTK, bidang keahlian.
  4. *Student Registry:* Data induk siswa, NIS/NISN, riwayat kelas.
  5. *Classroom & Course Assignment:* Struktur rombongan belajar (misal: "X-RPL-1", "VII-B"), mata pelajaran, dan penugasan guru pengampu.
- **Batasan:** Belum mencakup unggah materi pembelajaran atau ujian CBT.

---

### FASE 2: Content & Learning Delivery
- **Modul yang Dibangun:**
  1. *Learning Material Management:* Unggah modul digital (PDF, slide presentasi, tautan video pembelajaran).
  2. *Interactive Discussion Board:* Forum tanya jawab per topik materi pelajaran.
  3. *Assignment & Submissions:* Pembuatan tugas terstruktur dengan tenggat waktu, pengumpulan berkas oleh siswa, dan catatan umpan balik dari guru.

---

### FASE 3: Assessment, CBT & Examination
- **Modul yang Dibangun:**
  1. *Question Bank:* Bank soal berbasis kategori (Pilihan Ganda, Esai, Benar/Salah).
  2. *CBT Exam Engine:* Pelaksanaan ujian dengan timer anti-curang, acak nomor soal/opsi, dan auto-save respon siswa.
  3. *Instant Grading & Analytics:* Koreksi otomatis untuk soal pilihan ganda dan analisis ketercapaian kompetensi dasar.

---

### FASE 4: Evaluation, Grading & Academic Records
- **Modul yang Dibangun:**
  1. *Teacher Gradebook:* Penginputan nilai formatif, sumatif, dan kehadiran.
  2. *Attendance Tracking:* Presensi harian dan per jam pelajaran.
  3. *Academic Report Generator:* Cetak lembar laporan hasil belajar (Rapor) sesuai format Kurikulum Merdeka dan Kurikulum 2013.

---

### FASE 5: Institutional Expansion & Specialized Features
- **Modul yang Dibangun:**
  1. *Parent Portal:* Akses pantau presensi dan progres nilai bagi orang tua/wali murid.
  2. *Vocational Features (SMK):* Manajemen Praktik Kerja Lapangan (PKL), jurnal harian magang, dan monitoring guru pembimbing industri.
  3. *PPDB / SPMB Online:* Pendaftaran peserta didik baru dan verifikasi berkas daring.
