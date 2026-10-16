# EDUVIA MOBILE ECOSYSTEM — FEATURE MATRIX
**Document ID:** `EDV-MOB-FEAT-001`  
**Version:** 1.0.0 (Tahap 0 Baseline)  
**Scope:** Eduvia Web, Eduvia Parent App, Eduvia Student App  

---

## 1. Ikhtisar Komparatif Fitur Antar Platform

| Modul / Fitur | Status Web Eduvia | Eduvia Parent App | Eduvia Student App | Kebutuhan API Mobile | Otorisasi & Aturan Keamanan |
| :--- | :---: | :---: | :---: | :--- | :--- |
| **Autentikasi & Sesi** | ✅ Tersedia (Web Session) | ✅ Wajib (Sanctum Token) | ✅ Wajib (Sanctum Token) | `POST /api/v1/auth/login`<br/>`POST /api/v1/auth/logout`<br/>`GET /api/v1/auth/me` | Validasi kredensial, role filtering, pencabutan token saat logout. Hardware Secure Storage. |
| **Penautan Akun Anak (Link Code)** | ✅ Tersedia via `ParentPortalService` | ✅ Wajib (Klaim Kode) | ❌ Tidak Diizinkan | `POST /api/v1/parent/children/claim-code` | Validasi kode undangan unik, kedaluwarsa, pemakaian maksimal, dan kesesuaian sekolah. |
| **Pemilihan Anak Aktif (Child Switcher)** | ✅ Tersedia (Session) | ✅ Wajib (Multi-child switcher) | ❌ N/A (Single Account) | `GET /api/v1/parent/children` | Hanya menampilkan siswa dengan relasi terverifikasi `status = 'active'` pada `parent_student_relations`. |
| **Dasbor Utama (Home)** | ✅ Role-based View | ✅ Ringkasan Kehadiran, Jadwal, Tugas, Pengumuman Anak | ✅ Jadwal Hari Ini, Tugas Mendatang, Materi Baru, Status CBT | `GET /api/v1/parent/children/{id}/dashboard`<br/>`GET /api/v1/student/dashboard` | Scoped data berdasarkan enrollment kelas aktif siswa. |
| **Profil Siswa & Akademik** | ✅ Tersedia | ✅ Info NISN, NIS, Kelas, Wali Kelas | ✅ Profil Siswa, Info NISN, Status Kelas | `GET /api/v1/student/profile` | Read-only. Hanya menampilkan data publik/terbuka untuk siswa & orang tua. |
| **Jadwal Pelajaran (Timetable)** | ✅ Tersedia | ✅ Jadwal Mingguan Anak | ✅ Jadwal Harian & Mingguan Siswa | `GET /api/v1/parent/children/{id}/schedules`<br/>`GET /api/v1/student/schedules` | Mengambil dari `LessonSchedule` aktif kelas siswa. |
| **Presensi & Kehadiran** | ✅ Tersedia | ✅ Rekap Hadir, Terlambat, Izin, Sakit, Alpa (%) | ✅ Rekap Kehadiran Pribadi | `GET /api/v1/parent/children/{id}/attendance`<br/>`GET /api/v1/student/attendance` | Mengambil data resmi dari `AttendanceRecord` terverifikasi. |
| **Materi Pembelajaran** | ✅ Tersedia | ❌ N/A (Fokus Siswa) | ✅ Daftar Dokumen, Video, & Reader | `GET /api/v1/student/materials`<br/>`POST /api/v1/student/materials/{id}/progress` | Hanya materi berstatus `published` untuk kelas siswa. Tracking progress baca. |
| **Tugas & Pengumpulan** | ✅ Tersedia | ✅ Daftar Tugas, Batas Waktu, Nilai Terpublikasi | ✅ Instruksi, Upload Jawaban/File, Riwayat | `GET /api/v1/parent/children/{id}/assignments`<br/>`GET /api/v1/student/assignments`<br/>`POST /api/v1/student/assignments/{id}/submit` | Validasi ekstensi/ukuran berkas server-side, pencegahan double submission, feedback guru. |
| **Ujian Online (CBT)** | ✅ Tersedia (`cbt.student`) | ❌ N/A (Hanya lihat nilai akhir) | ✅ Pelaksanaan CBT Lengkap, Timer Server, Autosave | `GET /api/v1/student/exams`<br/>`POST /api/v1/student/exams/{id}/start`<br/>`POST /api/v1/student/exams/{id}/attempts/{aid}/save-answer`<br/>`POST /api/v1/student/exams/{id}/attempts/{aid}/submit` | Enforce waktu server, soal tanpa kunci jawaban, auto-finalize attempt saat habis waktu. |
| **Nilai & E-Rapor Digital** | ✅ Tersedia | ✅ Nilai Resmi & Viewer PDF Rapor Terbit | ✅ Nilai Resmi & Viewer PDF Rapor Terbit | `GET /api/v1/parent/children/{id}/report-cards`<br/>`GET /api/v1/student/report-cards`<br/>`GET /api/v1/reports/{id}/download` | HANYA status `published`. Nilai draft guru DILARANG bocor ke mobile. |
| **Pengumuman Sekolah** | ✅ Tersedia | ✅ Pengumuman Sekolah & Kelas Anak | ✅ Pengumuman Sekolah & Rombel | `GET /api/v1/parent/children/{id}/announcements`<br/>`GET /api/v1/student/announcements` | Filter berdasarkan `school_id`, kelas, dan jenjang. |
| **Pesan Guru - Orang Tua** | ✅ Tersedia | ✅ Chat Resmi dengan Wali Kelas / Guru Mapel | ❌ Tidak Diizinkan (Komunikasi Khusus Ortu) | `GET /api/v1/parent/conversations`<br/>`POST /api/v1/parent/conversations`<br/>`POST /api/v1/parent/conversations/{id}/messages` | Terhubung dengan model `ParentTeacherConversation` dan notifikasi web/email. |
| **Keuangan & Tagihan SPP** | ✅ Tersedia | ✅ Rincian Tagihan, Upload Bukti Transfer, Kuitansi | ⚠️ Ringkasan Tagihan (Read-only) | `GET /api/v1/parent/children/{id}/finance/invoices`<br/>`POST /api/v1/parent/children/{id}/finance/invoices/{iid}/pay` | Verifikasi server-side oleh staf keuangan, pencegahan status bayar fiktif dari sisi klien. |

---

## 2. Rincian Fitur Khusus: Eduvia Parent App

### A. Fitur Utama & Kebutuhan Pengguna
1. **Multi-Child Switching (Pengelolaan Banyak Anak):**  
   - Orang tua yang memiliki lebih dari satu anak bersekolah di yayasan/sekolah yang sama dapat berpindah anak secara instan.
   - Indikator identitas anak (Foto profil, Nama lengkap, Jenjang, Kelas, Nama Wali Kelas) selalu terpampang jelas di header aplikasi.
2. **Klaim Kode Undangan Siswa (Onboarding):**  
   - Form input kode penautan sekolah (contoh: `LINK-XXXX-YYYY`) untuk mengaktifkan hubungan orang tua dan anak baru secara resmi.
3. **Pemberitahuan & Notifikasi Waktu Nyata:**  
   - Notifikasi kehadiran anak (apabila siswa terlambat atau tidak hadir).
   - Pengumuman mendadak dari kepala sekolah atau wali kelas.
   - Terbitnya tagihan SPP bulanan atau konfirmasi penerimaan pembayaran.
4. **Pembayaran Transparan & Bukti Sah:**  
   - Orang tua dapat melihat rekening tujuan resmi sekolah, menyalin nomor rekening/VA, mengunggah foto struk transfer, dan mengunduh kuitansi resmi bertanda tangan digital.

---

## 3. Rincian Fitur Khusus: Eduvia Student App

### A. Fitur Utama & Desain Adaptif Jenjang
1. **Antarmuka Ramah Usia (SD s/d SMK):**  
   - Tipografi yang jelas dan keterbacaan tinggi.
   - Pilihan visual kontras tinggi untuk kenyamanan membaca materi dalam waktu lama.
2. **Jadwal Pelajaran & Countdown Kelas Berikutnya:**  
   - Siswa melihat mata pelajaran aktif hari ini, ruang kelas, nama guru, dan jam mulai/selesai.
3. **Pusat Tugas & Pengumpulan Berkas:**  
   - Detail instruksi tugas, rubrik penilaian, lampiran guru.
   - Dukungan unggah berkas (PDF, DOCX, JPG, PNG) atau penulisan jawaban esai teks langsung di ponsel.
4. **Mesin CBT Mobile Responsif:**  
   - Navigasi nomor soal fleksibel (kisi-kisi soal dengan warna status: Terjawab, Belum, Ragu-ragu).
   - Autosave instan di latar belakang setiap kali siswa memilih opsi pilihan ganda atau mengetik jawaban esai.
   - Timer hitung mundur melayang (*floating timer*) yang berubah warna menjadi merah saat waktu tersisa kurang dari 5 menit.
   - Konfirmasi penyerahan ujian untuk menghindari pengumpulan yang tidak disengaja.

---

## 4. Analisis Risiko Teknis & Mitigasi

| Risiko Teknis | Dampak | Strategi Mitigasi Teruji |
| :--- | :--- | :--- |
| **IDOR pada akses data anak** | Orang tua mengakses nilai atau riwayat anak lain dengan mengubah `child_id`. | Validasi wajib di setiap endpoint controller: `ParentStudentRelation::where('parent_id', $user->id)->where('student_id', $childId)->where('status', 'active')->firstOrFail()`. |
| **Koneksi terputus saat CBT** | Jawaban hilang saat siswa mengerjakan ujian di area minim sinyal. | Simpan jawaban sementara di SQLite/SecureStore lokal, kirim ke server melalui antrean autosave dengan status sync (Tersimpan di Cloud vs Menunggu Koneksi). |
| **Kebocoran soal & kunci jawaban** | Klien mobile membedah respons JSON dan membaca kunci jawaban soal. | Serializer API CBT menggunakan DTO `FrozenQuestionResource` yang secara ketat membuang field `correct_answer`, `rubric`, dan `explanation` sebelum dikirim ke klien. |
| **Manipulasi status pembayaran** | Pengguna mengklaim status tagihan lunas tanpa verifikasi pihak sekolah. | Klien mobile hanya dapat mengirim bukti pembayaran (`STATUS_PENDING_VERIFICATION`). Status lunas (`STATUS_PAID`) hanya dapat diterbitkan oleh staf keuangan di backend. |
| **Kelebihan beban server (Over-fetching)** | Beban tinggi pada database saat ribuan siswa mengakses jadwal bersamaan. | Penerapan pagination standar pada daftar data, caching respons data statis menggunakan HTTP ETag / Cache-Control, serta indeks database pada `school_id`, `student_id`, dan `classroom_id`. |
