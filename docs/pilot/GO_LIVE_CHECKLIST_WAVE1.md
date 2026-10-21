# GO-LIVE READINESS CHECKLIST — GELOMBANG 1 (WAVE 1)
## Inti Akademik & Presensi Harian — SMA Eduvia Unggulan Surabaya
**Target Tanggal Peluncuran:** Pekan 1 Pilot (Oktober 2026)  
**Lingkup:** Rombongan Belajar Kelas 10 (Siswa, Wali Kelas, Guru Mapel, Tata Usaha)  
**Tujuan Gate:** Memastikan 100% kelayakan teknis, operasional, legal, dan dukungan sebelum peluncuran nyata.  

---

## DAFTAR PERIKSA KESIAPAN GO-LIVE (20 BUTIR WAJIB)

### Kategori A: Kesiapan Teknis & Infrastruktur (Technical & Infrastructure)
- [ ] **1. Baseline Stabilitas Terverifikasi:** Seluruh rangkaian tes otomatis (Backend 26/26 test suite dan Flutter mobile app) berstatus hijau (100% Passed) tanpa regresi.
- [ ] **2. Sertifikat SSL & Protokol Keamanan:** Domain produksi/staging menggunakan HTTPS aktif dengan TLS 1.3 dan header keamanan lengkap (HSTS, CSP, X-Frame-Options, X-Content-Type-Options).
- [ ] **3. Cadangan Basis Data Terjadwal (Daily Backup):** Mekanisme snapshot basis data harian otomatis telah teruji dan prosedur pemulihan data darurat (*RTO < 30 menit*) telah tervalidasi.
- [ ] **4. Mekanisme Pembatasan Trafik (Rate Limiting):** Perlindungan endpoint sensitif (`throttle:5,1` pada login dan kode klaim) aktif untuk mencegah brute-force.
- [ ] **5. Modul Berisiko Tinggi Dinonaktifkan:** Gateway Pembayaran Otomatis (Midtrans) dan Ujian Sumatif Kelulusan telah dipastikan terkunci nonaktif untuk Gelombang 1.

---

### Kategori B: Kesiapan Data & Onboarding (Data & Enrollment)
- [ ] **6. Struktur Master Akademik Aktif:** Tahun ajaran `2026/2027` dan semester ganjil berstatus aktif di dalam sistem.
- [ ] **7. Rombongan Belajar Terdaftar:** Seluruh ruang kelas dan rombel sasaran pilot (Kelas 10 MIPA & IPS) telah terdaftar dengan kapasitas yang tepat.
- [ ] **8. Data Guru & Akun Pengguna:** 45 akun guru telah dibuat, dipetakan ke rombel mengajar masing-masing, dan slip sandi sementara telah disiapkan.
- [ ] **9. Pra-Validasi Impor Siswa:** Berkas CSV data siswa telah divalidasi via web preview dengan **0 duplikasi NISN/email** dan laporan rekonsiliasi selisih 0 (*zero discrepancy*).

---

### Kategori C: Kesiapan Pengguna & Materi Pelatihan (Training & Enablement)
- [ ] **10. Panduan Ringkas Guru Didistribusikan:** Dokumen `PANDUAN_RINGKAS_GURU.md` telah dibagikan secara fisik/digital kepada seluruh guru kelas 10.
- [ ] **11. Panduan Wali Murid Siap Edar:** Dokumen `PANDUAN_RINGKAS_ORANG_TUA.md` telah disiapkan bersama lembaran surat resmi kode klaim anak.
- [ ] **12. Briefing Tata Usaha (Operator Data):** Staf Tata Usaha telah dilatih menjalankan SOP impor CSV, penanganan lupa sandi, dan eksekusi rollback darurat.
- [ ] **13. Lembar Cadangan Presensi Fisik (Paper Fallback):** Lembar presensi kertas telah dicetak dan tersedia di meja kelas sebagai cadangan jika internet padam.

---

### Kategori D: Legalitas & Kepatuhan Data Pribadi (UU PDP No. 27/2022)
- [ ] **14. Perjanjian Pilot & DPA Ditandatangani:** Draf dokumen `DRAFT_PERJANJIAN_PILOT_DPA.md` telah disetujui dan ditandatangani oleh pimpinan Yayasan/Eduvia dan Kepala Sekolah.
- [ ] **15. Formulir Persetujuan Orang Tua (Parental Consent):** Lembar formulir persetujuan pemrosesan data anak telah dibagikan kepada orang tua siswa kelas 10.
- [ ] **16. Residensi Data Terjamin di Indonesia:** Server fisik dan cloud storage berlokasi di Jakarta/Surabaya (memenuhi PP 71/2019).
- [ ] **17. Kebijakan Sanitasi Log (Zero PII Logging):** Sistem verifikasi log memastikan tidak ada data pribadi anak (nama, NISN, telepon) yang tercatat dalam log audit mentah atau telemetri.

---

### Kategori E: Operasional Dukungan & Mitigasi Insiden (Support & SRE)
- [ ] **18. Helpdesk Lapangan (Level 1) Siaga:** Nomor WhatsApp Tata Usaha sekolah (`0812-XXXX-1002`) aktif dan bertugas di meja piket pada jam sekolah (06:45 – 16:00 WIB).
- [ ] **19. Saluran Eskalasi Darurat (Level 2 & 3) Siap:** Grup koordinasi darurat Telegram antara PIC Sekolah dan Tim SRE Eduvia telah terhubung dengan waktu tanggap < 15 menit.
- [ ] **20. Kriteria Pembatalan Darurat (Abort Criteria) Disepakati:** Seluruh pihak memahami bahwa jika terjadi kebocoran data atau kegagalan sistem > 4 jam, sistem akan dialihkan seketika ke mode baca-saja dan kembali ke presensi manual.

---

## LEMBAR PERSETUJUAN RESMI GO-LIVE (SIGN-OFF GATE)

Dengan menandatangani lembar ini, para pihak menyatakan bahwa seluruh 20 butir daftar periksa di atas telah terpenuhi dengan baik dan sistem **RESMI DISETUJUI UNTUK MEMULAI OPERASIONAL GELOMBANG 1**:

| Peran | Nama Pejabat | Tanda Tangan | Tanggal |
| :--- | :--- | :--- | :--- |
| **Product Manager (Pilot Lead)** | ___________________________ | [                         ] | ___/___/2026 |
| **Lead SRE / Support Engineer** | ___________________________ | [                         ] | ___/___/2026 |
| **Koordinator Onboarding Sekolah** | ___________________________ | [                         ] | ___/___/2026 |
| **Kepala Sekolah (Pengendali Data)** | ___________________________ | [                         ] | ___/___/2026 |

> [!IMPORTANT]
> **CATATAN SISTEM:**
> Pelaksanaan Gelombang 1 hanya boleh dimulai setelah seluruh tanda tangan di atas lengkap dan perintah `php artisan pilot:activate-wave 1 [school_id]` dijalankan atas instruksi resmi pengguna.
