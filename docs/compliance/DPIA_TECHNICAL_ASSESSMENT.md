# EDUVIA DATA PROTECTION IMPACT ASSESSMENT (DPIA / RIPD)
## Penilaian Dampak Pelindungan Data Pribadi — Analisis Risiko Teknis Platform Eduvia

> [!NOTE]
> **DISCLAIMER:**  
> Dokumen ini merupakan draf kerja teknis untuk membantu manajemen yayasan dan konsultan hukum, bukan nasihat hukum formal (*legal counsel*). Perlu ditinjau dan divalidasi oleh konsultan hukum berizin sebelum diberlakukan resmi.

**Nama Sistem:** Eduvia Multi-School Management Platform  
**Target Pengguna:** Peserta Didik (Anak), Orang Tua / Wali Murid, Tenaga Pendidik, Staf Keuangan  
**Tanggal Evaluasi:** 19 Oktober 2026  
**Auditor Teknis:** Security Engineer & Compliance Technical Analyst  
**Versi Dokumen:** 1.0 (Kesiapan Pra-Peluncuran Fase 5G)  

---

## 1. METODOLOGI ANALISIS RISIKO TEKNIS

Evaluasi risiko dilakukan dengan menilai dua parameter:
1. **Kemungkinan Terjadi (Likelihood):** Rendah (1), Sedang (2), Tinggi (3).
2. **Tingkat Keparahan Dampak (Severity):** Rendah (1), Sedang (2), Kritis (3).
3. **Tingkat Risiko Total (Risk Level):** `Likelihood × Severity` (1–2: Rendah, 3–4: Sedang, 6–9: Tinggi/Kritis).

---

## 2. ANALISIS 5 RISIKO PRIVASI TERBESAR & MITIGASI TEKNIS

### Risiko 1: Kebocoran Data Nilai & Rapor Akademik Anak
* **Deskripsi:** Pihak yang tidak berwenang (siswa lain, publik, atau peretas) mengakses riwayat nilai tugas, ujian CBT, atau buku rapor digital siswa.
* **Tingkat Kemungkinan (Awal):** Sedang (2)
* **Tingkat Keparahan:** Kritis (3) — Data perkembangan psikologis dan masa depan anak.
* **Tingkat Risiko Awal:** **Tinggi (6)**
* **Mitigasi Teknis yang Sudah Terpasang:**
  1. Route `/reports/cards/*` dilindungi middleware otorisasi berbasis peran (`role:super_admin,school_admin,teacher,parent,student`).
  2. Implementasi `DigitalReportCardPolicy` yang secara ketat memverifikasi bahwa siswa hanya dapat melihat rapornya sendiri (`$user->id === $reportCard->student_id`).
  3. Orang tua hanya dapat melihat rapor anak kandung yang relasinya telah terverifikasi (`ParentStudentRelation::where('is_verified', true)`).
  4. Global scope `SchoolTenantScope` mencegah siswa atau orang tua dari sekolah lain menembus ID rapor sekolah berbeda (menghasilkan 404/403).
* **Mitigasi Tambahan yang Direkomendasikan:**
  - Penambahan tanda air (*watermark dynamic*) berupa nama siswa dan timestamp pengunduhan pada berkas PDF rapor untuk mencegah penyebaran tangkapan layar liar oleh pihak ketiga.
* **Tingkat Risiko Residual:** **Rendah (2)**

---

### Risiko 2: Kebocoran Data Keuangan Keluarga (SPP & Bukti Transfer)
* **Deskripsi:** Detail kemampuan finansial keluarga, besaran beasiswa/potongan anak, atau gambar bukti transfer bank dapat diakses oleh pihak luar.
* **Tingkat Kemungkinan (Awal):** Sedang (2)
* **Tingkat Keparahan:** Kritis (3) — Reputasi keluarga dan potensi eksploitasi penipuan finansial.
* **Tingkat Risiko Awal:** **Tinggi (6)**
* **Mitigasi Teknis yang Sudah Terpasang:**
  1. Direktori penyimpanan bukti transfer (`payment-proofs/`) disimpan dalam disk lokal privat atau private S3 bucket, **BUKAN** folder publik web.
  2. Akses unduh bukti transfer dikontrol melalui endpoint terproteksi (`/finance/payments/{payment}/proof`) yang memvalidasi hak akses staf admin sekolah yang bersangkutan.
  3. Pembayaran online menggunakan tokenisasi payment gateway Midtrans (PCI-DSS Level 1); Eduvia **TIDAK PERNAH** menyimpan nomor kartu kredit/debit atau CVV di database.
  4. Pengesahan potongan biaya melalui modul persetujuan terpusat (`ApprovalWorkflowService`) dengan jejak audit permanen.
* **Mitigasi Tambahan yang Direkomendasikan:**
  - Masking otomatis pada 4 digit tengah nomor rekening saat ditampilkan pada antarmuka admin.
* **Tingkat Risiko Residual:** **Rendah (2)**

---

### Risiko 3: Data Anak Terkirim ke Model AI / LLM Eksternal Tanpa Izin
* **Deskripsi:** Guru menggunakan asisten AI untuk membuat soal atau modul ajar dengan memasukkan data sensitif nama anak, NISN, atau catatan kepribadian anak, yang kemudian terkirim ke server penyedia LLM luar negeri.
* **Tingkat Kemungkinan (Awal):** Tinggi (3)
* **Tingkat Keparahan:** Kritis (3) — Pelanggaran langsung Pasal 25 UU PDP & potensi sanksi administratif.
* **Tingkat Risiko Awal:** **Kritis (9)**
* **Mitigasi Teknis yang Sudah Terpasang:**
  1. **Dual PII Protection Layer (`PiiScrubber`):**
     - Deteksi otomatis NIK (16 digit) dan NISN (10 digit).
     - Otomatis melakukan penyamaran (*scrubbing*) nama lengkap siswa dan guru menjadi token generik `[SISWA]` dan `[GURU]` sebelum prompt dikirim ke API Gemini.
     - Blokir prompt (*pre-execution exception*) jika terdeteksi data NIK mentah.
  2. **Prompt Injection Shield:** Memblokir upaya *jailbreak* atau instruksi berbahaya yang mencoba memaksa model membocorkan data sekolah lain.
  3. **Consent Filter:** Layanan AI hanya memproses materi ajar dan bank soal mata pelajaran guru, **TIDAK PERNAH** menerima dataset profil anak sebagai input pembobotan rapor.
* **Mitigasi Tambahan yang Direkomendasikan:**
  - Audit trail berkala terhadap riwayat draf AI (`ai_generation_drafts`) untuk memastikan kepatuhan konten prompt guru.
* **Tingkat Risiko Residual:** **Rendah (2)**

---

### Risiko 4: Mantan Guru / Staf yang Berhenti Masih Dapat Mengakses Data Sekolah
* **Deskripsi:** Guru atau staf yang telah dimutasi atau mengundurkan diri tetap memiliki token aktif atau password untuk mengintip data siswa dari rumah.
* **Tingkat Kemungkinan (Awal):** Sedang (2)
* **Tingkat Keparahan:** Tinggi (2)
* **Tingkat Risiko Awal:** **Sedang (4)**
* **Mitigasi Teknis yang Sudah Terpasang:**
  1. Flag `is_active` pada model `User`: Saat admin sekolah menonaktifkan akun staf, seluruh personal access token Sanctum (`personal_access_tokens`) dicabut secara instan.
  2. Middleware otentikasi secara aktif memeriksa status `$user->is_active === true`; jika nonaktif, sesi HTTP langsung di-abort dengan status 401/403.
  3. Modul delegasi wewenang (`ApprovalWorkflowController`) memiliki masa kadaluarsa otomatis (`valid_until`), mencegah delegasi abadi.
* **Mitigasi Tambahan yang Direkomendasikan:**
  - Otomatisasi pengingat peninjauan akun berkala (*quarterly access review*) bagi super admin yayasan.
* **Tingkat Risiko Residual:** **Rendah (1)**

---

### Risiko 5: Akses Silang Antar-Sekolah (Cross-Tenant Data Leakage)
* **Deskripsi:** Admin atau guru dari SMA A dapat memanipulasi ID URL untuk melihat nilai atau data keuangan siswa di SMP B di bawah yayasan yang sama.
* **Tingkat Kemungkinan (Awal):** Tinggi (3)
* **Tingkat Keparahan:** Kritis (3) — Runtuhnya prinsip isolasi yayasan multi-sekolah.
* **Tingkat Risiko Awal:** **Kritis (9)**
* **Mitigasi Teknis yang Sudah Terpasang:**
  1. **Enforcement Global Scope (`SchoolTenantScope` & `BelongsToSchool`):**
     - Diterapkan pada seluruh 90+ model data sekolah (termasuk 19 model yang diperkuat pada Fase 5C).
     - Setiap query `SELECT`, `UPDATE`, `DELETE` secara otomatis menyertakan klausul `WHERE school_id = ?`.
  2. Route model binding yang mengakses data sekolah lain langsung menghasilkan respon **404 Not Found**, menghilangkan celah enumerasi ID.
  3. Middleware `EnsureSchoolTenant` memvalidasi kesesuaian sekolah pada header dan sesi pengguna.
* **Mitigasi Tambahan yang Direkomendasikan:**
  - Automated CI regression tests yang berjalan di pipeline setiap ada commit baru untuk menguji isolasi multi-tenant.
* **Tingkat Risiko Residual:** **Rendah (1)**

---

## 3. KESIMPULAN PENILAIAN DAMPAK (DPIA VERDICT)

Dari 5 risiko privasi utama yang diidentifikasi:
* **Seluruh risiko kritis (Risiko 1, 2, 3, 5) berhasil diturunkan menjadi tingkat risiko residual RENDAH** melalui lapisan pengamanan teknis arsitektur (*Defense-in-Depth*).
* Sistem dinilai **MEMENUHI PERSYARATAN KELAYAKAN TEKNIS PELINDUNGAN DATA PRIBADI** untuk memasuki tahap peluncuran produksi (*Production Go-Live*).
