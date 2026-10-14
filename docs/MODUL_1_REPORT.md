# LAPORAN MODUL 1: KONFIGURASI SEKOLAH DAN SISTEM
**Eduvia — E-Learning & School Management Platform (Fase 1: System Foundation)**

---

## 1. NAMA DAN TUJUAN MODUL
* **Nama Modul:** Modul 1 — Konfigurasi Sekolah dan Sistem
* **Tujuan:** Menyediakan pengaturan dasar Eduvia agar identitas sekolah, tahun ajaran, semester, jenjang pendidikan, dan preferensi aplikasi dapat dikelola secara konsisten, aman, tervalidasi, dan menjadi landasan kokoh bagi Modul 2–5.

---

## 2. FITUR YANG DIIMPLEMENTASIKAN
1. **Pengelolaan Profil Identitas Sekolah (`/school/profile`)**:
   - Menampilkan detail profil sekolah lengkap: Nama resmi, Nama singkatan, NPSN (8 digit unik), Multi-Jenjang Pendidikan (SD, SMP, SMA, SMK dalam bentuk array JSON terstruktur), Alamat, Kota/Kabupaten, Provinsi, Kode Pos, Nomor Telepon, Email resmi, Website resmi, Logo sekolah, dan Status keaktifan sekolah.
   - Form ubah profil sekolah (`/school/profile/edit`) dengan validasi ketat di sisi server (`UpdateSchoolProfileRequest`).
   - Manajemen logo sekolah: Unggah logo berformat gambar (PNG, JPG, WebP) dengan batasan ukuran 2MB, penyimpanan aman pada storage publik, penghapusan otomatis file logo lama saat diperbarui, serta endpoint eksplisit untuk menghapus logo (`DELETE /school/profile/logo`).
   - Widget ringkasan periode akademik aktif di halaman profil untuk visibilitas cepat administrator.

2. **Pengelolaan Tahun Ajaran (`/academic-years`)**:
   - Tampilan daftar tabel tahun ajaran dengan tanggal mulai, tanggal selesai, indikator jumlah semester terkait, dan badge status keaktifan.
   - Form tambah dan ubah tahun ajaran (`StoreAcademicYearRequest`, `UpdateAcademicYearRequest`).
   - Validasi nama tahun ajaran unik per sekolah (format contoh: "2025/2026") dan validasi rentang tanggal (`end_date > start_date`).
   - Mekanisme aktivasi tahun ajaran secara atomik (`POST /academic-years/{academicYear}/activate`): Ketika satu tahun ajaran diaktifkan, tahun ajaran lain dalam sekolah yang sama dinonaktifkan secara otomatis dalam transaksi basis data (`DB::transaction`).
   - Penghapusan aman: Tahun ajaran yang sedang berstatus aktif atau yang telah memiliki data semester tidak dapat dihapus sembarangan.

3. **Pengelolaan Semester (`/semesters`)**:
   - Tampilan daftar semester dengan filter dropdown berdasarkan tahun ajaran.
   - Form tambah dan ubah semester (`StoreSemesterRequest`, `UpdateSemesterRequest`).
   - Validasi jenis semester (`ganjil` atau `genap`), keunikan jenis semester per tahun ajaran, dan validasi periode tanggal (`end_date > start_date` serta harus berada dalam batas tanggal tahun ajaran induknya).
   - Mekanisme aktivasi semester secara atomik (`POST /semesters/{semester}/activate`): Mengaktifkan semester terpilih, menonaktifkan semester lain di sekolah yang sama, dan secara otomatis mengaktifkan tahun ajaran induknya jika belum aktif.
   - Proteksi penghapusan: Semester yang sedang berstatus aktif dilindungi dari penghapusan.

4. **Pengaturan Dasar Sistem & Platform (`/settings/system`)**:
   - Tampilan form pengaturan operasional meliputi Zona Waktu Operasional (`Asia/Jakarta`, `Asia/Makassar`, `Asia/Jayapura`, `UTC`), Bahasa Baku Antarmuka (`id`, `en`), dan Format Tampilan Tanggal (`d/m/Y`, `d-m-Y`, `d M Y`, `Y-m-d`).
   - Penyimpanan berbasis key-value bertipe pada tabel `system_settings` dengan scope sekolah (`school_id`).
   - Penegakan kebijakan rahasia sistem: Kredensial sensitif (`APP_KEY`, database credentials, API secrets) diisolasi murni di berkas `.env` dan tidak pernah diekspos melalui antarmuka web.

5. **Konsistensi UI/UX & Brand Identity Eduvia**:
   - Mematuhi pedoman resmi `docs/UI_UX_DESIGN_MEMORY.md`.
   - Warna aksen primer resmi `#E11D2E` (Eduvia Red), warna teks gelap `#1F2937`, kontainer kartu putih murni `#FFFFFF` dengan bayangan halus, dan latar belakang canvas netral `#F3F4F6`.
   - Navigasi sidebar terintegrasi menampilkan nama sekolah aktif dan status tahun ajaran/semester yang sedang berjalan.

---

## 3. FILE YANG DIBUAT ATAU DIUBAH

### Berkas Baru (Created):
- `database/migrations/2026_10_13_032845_create_schools_table.php`
- `database/migrations/2026_10_13_032919_create_academic_years_table.php`
- `database/migrations/2026_10_13_032936_create_semesters_table.php`
- `database/migrations/2026_10_13_033001_create_system_settings_table.php`
- `app/Models/School.php`
- `app/Models/AcademicYear.php`
- `app/Models/Semester.php`
- `app/Models/SystemSetting.php`
- `database/factories/SchoolFactory.php`
- `database/factories/AcademicYearFactory.php`
- `database/factories/SemesterFactory.php`
- `database/factories/SystemSettingFactory.php`
- `app/Http/Requests/School/UpdateSchoolProfileRequest.php`
- `app/Http/Requests/Academic/StoreAcademicYearRequest.php`
- `app/Http/Requests/Academic/UpdateAcademicYearRequest.php`
- `app/Http/Requests/Academic/StoreSemesterRequest.php`
- `app/Http/Requests/Academic/UpdateSemesterRequest.php`
- `app/Http/Requests/Setting/UpdateSystemSettingsRequest.php`
- `app/Http/Controllers/SchoolController.php`
- `app/Http/Controllers/AcademicYearController.php`
- `app/Http/Controllers/SemesterController.php`
- `app/Http/Controllers/SystemSettingController.php`
- `resources/views/school/show.blade.php`
- `resources/views/school/edit.blade.php`
- `resources/views/academic-years/index.blade.php`
- `resources/views/academic-years/create.blade.php`
- `resources/views/academic-years/edit.blade.php`
- `resources/views/semesters/index.blade.php`
- `resources/views/semesters/create.blade.php`
- `resources/views/semesters/edit.blade.php`
- `resources/views/settings/system.blade.php`
- `tests/Feature/SchoolProfileTest.php`
- `tests/Feature/AcademicYearTest.php`
- `tests/Feature/SemesterTest.php`
- `tests/Feature/SystemSettingTest.php`

### Berkas Diperbarui (Updated):
- `routes/web.php` (Pendaftaran grup route terproteksi role admin untuk Modul 1)
- `database/seeders/DatabaseSeeder.php` (Penambahan data awal sekolah, tahun ajaran aktif, semester ganjil/genap, dan pengaturan sistem)
- `resources/views/layouts/app.blade.php` (Pembaruan navigasi sidebar dengan item menu Modul 1 dan banner ringkasan sekolah)

---

## 4. PERUBAHAN DATABASE DAN MIGRATION
1. **`schools`**:
   - `id`, `name`, `short_name`, `npsn` (string 8, unique, nullable), `educational_levels` (json: `['sd','smp','sma','smk']`), `address`, `city`, `province`, `postal_code`, `phone`, `email`, `website`, `logo_path`, `is_active` (boolean, default true), `timestamps`.
2. **`academic_years`**:
   - `id`, `school_id` (foreign key cascade), `name`, `start_date`, `end_date`, `is_active` (boolean, default false), `timestamps`.
   - Index unik gabungan: `[school_id, name]`.
3. **`semesters`**:
   - `id`, `academic_year_id` (foreign key cascade), `name`, `semester_type` (`enum: ganjil, genap`), `start_date`, `end_date`, `is_active` (boolean, default false), `timestamps`.
   - Index unik gabungan: `[academic_year_id, semester_type]`.
4. **`system_settings`**:
   - `id`, `school_id` (foreign key cascade, nullable), `group`, `key`, `value` (text nullable), `type`, `timestamps`.
   - Index unik gabungan: `[school_id, group, key]`.

---

## 5. ENDPOINT DAN ROUTE YANG DITAMBAHKAN
Semua endpoint dilindungi middleware `auth` dan middleware otorisasi `role:super_admin,school_admin`:
- `GET    /school/profile` (`school.profile`) — Tampilan profil sekolah
- `GET    /school/profile/edit` (`school.profile.edit`) — Form ubah profil sekolah
- `PUT    /school/profile` (`school.profile.update`) — Simpan perubahan profil & logo
- `DELETE /school/profile/logo` (`school.logo.remove`) — Hapus file logo sekolah
- `GET    /academic-years` (`academic-years.index`) — Daftar tahun ajaran
- `GET    /academic-years/create` (`academic-years.create`) — Form tambah tahun ajaran
- `POST   /academic-years` (`academic-years.store`) — Simpan tahun ajaran baru
- `GET    /academic-years/{academicYear}/edit` (`academic-years.edit`) — Form ubah tahun ajaran
- `PUT    /academic-years/{academicYear}` (`academic-years.update`) — Simpan perubahan tahun ajaran
- `POST   /academic-years/{academicYear}/activate` (`academic-years.activate`) — Aktivasi tahun ajaran secara atomik
- `DELETE /academic-years/{academicYear}` (`academic-years.destroy`) — Hapus tahun ajaran yang aman
- `GET    /semesters` (`semesters.index`) — Daftar semester (mendukung filter `?academic_year_id=...`)
- `GET    /semesters/create` (`semesters.create`) — Form tambah semester
- `POST   /semesters` (`semesters.store`) — Simpan semester baru
- `GET    /semesters/{semester}/edit` (`semesters.edit`) — Form ubah semester
- `PUT    /semesters/{semester}` (`semesters.update`) — Simpan perubahan semester
- `POST   /semesters/{semester}/activate` (`semesters.activate`) — Aktivasi semester secara atomik
- `DELETE /semesters/{semester}` (`semesters.destroy`) — Hapus semester yang aman
- `GET    /settings/system` (`settings.system`) — Tampilan formulir pengaturan sistem
- `PUT    /settings/system` (`settings.system.update`) — Simpan pengaturan sistem

---

## 6. INTEGRASI DENGAN MODUL SEBELUMNYA (FASE 0)
- Menggunakan arsitektur autentikasi session dan middleware role `super_admin` & `school_admin` dari Fase 0.
- Menggunakan komponen Blade Fase 0 (`<x-card>`, `<x-button>`, `<x-input>`, `<x-badge>`, `<x-table>`, `<x-alert>`).
- Menggunakan token CSS dan palet warna resmi Eduvia (`#E11D2E`).
- Seluruh 27 unit/feature test dari Fase 0 tetap lulus 100% tanpa regresi.

---

## 7. PENGUJIAN YANG DIJALANKAN DAN HASIL AKTUAL
- **Total Test:** 53 test (27 test Fase 0 + 26 test Modul 1).
- **Hasil:** 53 Passed, 0 Failed, 205 Assertions.
- **Rincian Pengujian Modul 1**:
  1. `SchoolProfileTest`:
     - Tamu (guest) dialihkan ke halaman login (Passed).
     - Siswa dan Guru ditolak dengan status 403 Forbidden (Passed).
     - School Admin & Super Admin dapat melihat profil sekolah (Passed).
     - Admin dapat memperbarui profil sekolah dengan data valid (Passed).
     - Validasi menolak format NPSN yang tidak valid (Passed).
     - Validasi mewajibkan nama sekolah dan jenjang pendidikan (Passed).
     - Admin dapat mengunggah dan menghapus logo sekolah di storage (Passed).
  2. `AcademicYearTest`:
     - Otorisasi akses tamu dan non-admin ditolak (Passed).
     - Admin dapat melihat daftar tahun ajaran (Passed).
     - Admin dapat membuat tahun ajaran dengan data valid (Passed).
     - Validasi menolak tanggal akhir mendahului tanggal mulai (Passed).
     - Aktivasi tahun ajaran menonaktifkan tahun ajaran aktif lainnya secara atomik (Passed).
     - Admin dapat memperbarui tahun ajaran (Passed).
     - Tahun ajaran yang aktif tidak dapat dihapus sembarangan (Passed).
  3. `SemesterTest`:
     - Otorisasi akses tamu dan non-admin ditolak (Passed).
     - Admin dapat melihat daftar semester dengan filter tahun ajaran (Passed).
     - Admin dapat membuat semester baru (Passed).
     - Validasi rentang tanggal semester harus konsisten dengan tahun ajaran induk (Passed).
     - Aktivasi semester secara atomik menonaktifkan semester lain dan mengaktifkan tahun ajaran induknya (Passed).
     - Semester aktif dilindungi dari penghapusan (Passed).
  4. `SystemSettingTest`:
     - Tamu dialihkan ke login (Passed).
     - Guru dan Siswa ditolak dengan status 403 (Passed).
     - Admin dapat melihat halaman pengaturan sistem (Passed).
     - Admin dapat memperbarui zona waktu, locale, dan format tanggal (Passed).
     - Validasi menolak zona waktu, locale, atau format tanggal di luar whitelist (Passed).
- **Pengujian Linter & Gaya Kode:**
  - `vendor/bin/pint --format agent` dijalankan: 11 file diperiksa dan distandarisasi tanpa pelanggaran.
- **Pengujian Build Frontend:**
  - `npm run build` dijalankan: 4 modules transformed, Vite bundle berhasil dikompilasi ke `public/build/` dalam 8.27s.
- **Pengujian Tampilan & Browser Subagent:**
  - Navigasi browser ke `/school/profile`, `/academic-years`, `/semesters`, dan `/settings/system`.
  - Verifikasi elemen visual: Badge status aktif, tombol aksi, responsivitas navigasi sidebar, konsistensi aksen merah `#E11D2E` dan kartu putih bersih.

---

## 8. MASALAH YANG DITEMUKAN DAN CARA PENYELESAIANNYA
1. *Isu:* Label tampilan pada `settings/system.blade.php` bertuliskan `"Bahasa Baku Antarmuka (Default Locale)"`, sementara asersi awal pengujian memeriksa teks `"Bahasa Default"`.
   *Solusi:* Memperbarui teks asersi pengujian di `tests/Feature/SystemSettingTest.php` agar secara persis mencerminkan istilah baku Bahasa Indonesia yang digunakan pada antarmuka.
2. *Isu:* Penanganan logo sekolah berpotensi meninggalkan berkas usang (*orphan files*) di storage disk jika logo diperbarui berulang kali.
   *Solusi:* Menambahkan pengecekan `Storage::disk('public')->delete($school->logo_path)` sebelum menyimpan unggahan logo baru serta menyediakan rute penghapusan logo khusus (`school.logo.remove`).

---

## 9. MASALAH YANG BELUM SELESAI
- Tidak ada (*None*). Seluruh kriteria dan pengujian Modul 1 telah terpenuhi secara utuh.

---

## 10. STATUS MODUL
**SELESAI (GATE MODUL 1 PASSED)**

---

## 11. BUKTI & VERIFIKASI
- Hasil Test Suite: `{"tool":"phpunit","result":"passed","tests":53,"passed":53,"assertions":205,"duration_ms":8035}`
- Hasil Pint Linter: `{"tool":"pint","result":"fixed","files":[...]}`
- Hasil Vite Build: `✓ built in 8.27s` (assets `app-C9cF6MuI.css`, `app-BN7BXyTH.js`)
- Rekaman Browser Subagent: `modul1_ui_verify_1791863383781.webp`
