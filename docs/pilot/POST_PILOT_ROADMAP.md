# PANDUAN PENGEMBANGAN PASCA-PILOT (POST-PILOT OPERATIONAL ROADMAP)
## Rencana Strategis Ekspansi & Penerapan Skala Penuh Multi-Sekolah
**Platform:** Eduvia Multi-Tenant SaaS (Yayasan Pendidikan Nusantara Utama)  
**Horison Waktu:** 6 Bulan Pasca-Pilot (November 2026 – April 2027)  
**Status:** Disetujui Berdasarkan Keputusan Strategis Yayasan ([`YAYASAN_DECISION_MEMO.md`](file:///c:/Antigravity/Eduvia/docs/pilot/YAYASAN_DECISION_MEMO.md))  

---

## 1. VISI STRATEGIS PASCA-PILOT
Setelah keberhasilan program pilot 8 pekan di SMA Eduvia Unggulan Surabaya yang membuktikan keandalan sistem (**99.95% uptime**, **89.2% adopsi orang tua**, dan **pemangkasan waktu presensi menjadi 52 detik**), fokus strategis kini beralih ke:
1. **Pelepasan Bertahap Modul yang Ditangguhkan:** Membuka modul Finansial (SPP Online / Midtrans) dan Ujian Sumatif Kelulusan dengan protokol mitigasi risiko bertahap.
2. **Ekspansi Multi-Sekolah Tingkat Yayasan:** Melakukan *rollout* terstandardisasi ke jenjang SMP dan SD di bawah naungan yayasan yang sama.
3. **Penyempurnaan Berkelanjutan (Continuous Improvement):** Mengembangkan analitik performa siswa berbasis AI dan dashboard eksekutif pimpinan yayasan.

---

## 2. TIMELINE 6 BULAN PASCA-PILOT (ROADMAP MILESTONES)

```
2026                          2027
NOV        DES        JAN        FEB        MAR        APR
[BULAN 1]  [BULAN 2]  [BULAN 3]  [BULAN 4]  [BULAN 5]  [BULAN 6]
   │          │          │          │          │          │
   ▼          ▼          ▼          ▼          ▼          ▼
Transisi    Uji Coba   Full SPP   Onboarding   PPDB       Onboarding
Total SMA   SPP VA     Online SMA SMP Eduvia   Online     SD Eduvia
Paperless   Restricted CBT Skala  (450 Siswa)  Yayasan    (350 Siswa)
Presensi    2 Kelas    Besar                  Terpadu    Dashboard Yys
```

---

### BULAN 1 (NOVEMBER 2026): STABILISASI & TRANSISI 100% PAPERLESS DI SMA
* **Tindakan Kunci:**
  1. Penghentian total buku presensi manual kertas. Eduvia resmi menjadi *Single Source of Truth* presensi harian siswa dan kehadiran guru.
  2. Implementasi modul **Buku Rapor Digital (Digital Gradebook)** untuk pengolahan nilai tugas tengah semester ganjil.
  3. Pelatihan lanjutan untuk guru mata pelajaran mengenai penyusunan rubrik asesmen berbasis Kurikulum Merdeka.
* **Target Keberhasilan:** WAU Guru bertahan di atas **95%**, zero incident operasional harian.

---

### BULAN 2 (DESEMBER 2026): PILOT TERBATAS MODUL FINANSIAL (ONLINE SPP)
* **Tindakan Kunci:**
  1. Mengaktifkan koneksi ke payment gateway berlisensi Bank Indonesia (Midtrans Production) khusus Virtual Account (BCA, Mandiri, BNI, BRI) dan QRIS.
  2. Pelaksanaan **Uji Coba Terbatas Pembayaran SPP** hanya untuk 2 rombel percontohan (10 MIPA 1 dan 11 MIPA 1) pada periode tagihan bulan Desember.
  3. Menyediakan staf pendamping khusus di loket Tata Usaha untuk melayani dan mendampingi orang tua yang ingin mencoba pembayaran instan dari aplikasi ponsel mereka.
* **Target Keberhasilan:** 100% transaksi pembayaran via VA/QRIS terekonsiliasi otomatis ke buku besar kas (*general ledger*) sekolah tanpa selisih manual.

---

### BULAN 3 (JANUARI 2027): PELUNCURAN PENUH SPP ONLINE & UJIAN CBT SKALA BESAR
* **Tindakan Kunci:**
  1. Membuka opsi pembayaran SPP online untuk seluruh 600 siswa SMA Eduvia Unggulan Surabaya.
  2. Pelaksanaan **Ujian Penilaian Akhir Semester (PAS / Sumatif)** serentak menggunakan lab komputer dan perangkat tablet sekolah dengan sistem integritas pengawasan CBT Eduvia.
  3. Aktivasi fitur cetak kuitansi dan bukti pembayaran digital ber-meterai elektronik bagi wali murid.
* **Target Keberhasilan:** Penurunan antrean pembayaran kasir fisik di sekolah hingga **> 65%**.

---

### BULAN 4 (FEBRUARI 2027): ONBOARDING SEKOLAH KEDUA (SMP EDUVIA UNGGULAN)
* **Tindakan Kunci:**
  1. Mengaktifkan tenant baru: `SMP Eduvia Unggulan Surabaya` (~450 siswa, 32 guru, 15 rombel).
  2. Memanfaatkan modul onboarding yang telah teruji (`PilotOnboardingImportService`) untuk migrasi data siswa dan guru dari Dapodik menggunakan templat CSV standar.
  3. Pelatihan staf TU dan guru SMP dengan menggunakan materi video dan dokumen panduan yang telah matang pada Fase 6C.
* **Target Keberhasilan:** Onboarding data SMP selesai dalam **3 hari kerja**, dengan tingkat adopsi guru > 85% pada pekan pertama.

---

### BULAN 5 (MARET 2027): PORTAL PENERIMAAN PESERTA DIDIK BARU (PPDB ONLINE)
* **Tindakan Kunci:**
  1. Mengaktifkan modul publik **PPDB Multi-Sekolah** (`/ppdb`) untuk pendaftaran siswa baru Tahun Ajaran 2027/2028 untuk jenjang SMA, SMP, dan SD Nusantara.
  2. Formulir pendaftaran digital dengan unggah berkas akta lahir, kartu keluarga, dan rapor asal secara aman terenkripsi.
  3. Integrasi pembayaran biaya pendaftaran via QRIS instan dan seleksi tes berbasis CBT online.
* **Target Keberhasilan:** 1.000+ calon peserta didik baru mendaftar secara online tanpa perlu antre fisik di sekolah.

---

### BULAN 6 (APRIL 2027): ONBOARDING SD & FOUNDATION EXECUTIVE DASHBOARD
* **Tindakan Kunci:**
  1. Onboarding sekolah ketiga: `SD Eduvia Nusantara` (~350 siswa, 25 guru, 12 rombel) dengan penyesuaian antarmuka yang ramah anak sekolah dasar.
  2. Peluncuran **Foundation Executive Dashboard (Dashboard Pengurus Yayasan)**:
     - Tampilan terpadu metrik kehadiran harian lintas sekolah (SMA, SMP, SD).
     - Rekapitulasi arus kas penerimaan SPP yayasan secara *real-time*.
     - Laporan rasio keterisian rombel dan perbandingan performa akademik.
* **Target Keberhasilan:** Seluruh ekosistem pendidikan Yayasan Nusantara Utama beroperasi dalam satu platform terintegrasi.

---

## 3. MODEL TATA KELOLA, SRE & DUKUNGAN BERKELANJUTAN
Untuk menjaga performa dan keamanan jangka panjang:
1. **Audit Keamanan Berkala (Quarterly Security Audit):** Melakukan peninjauan hak akses (*least privilege access audit*) dan pengujian kerentanan berkala setiap awal kuartal.
2. **Kepatuhan Privasi Data (Data Protection Officer / DPO):** Menunjuk petugas DPO yayasan yang bertanggung jawab memvalidasi persetujuan orang tua siswa baru setiap tahun ajaran.
3. **Penyelarasan Kapasitas Cloud (Autoscaling):** Menyiapkan arsitektur autoscaling pada jam-jam lonjakan puncak (misal: saat pengumuman kelulusan dan hari pertama pembukaan pendaftaran PPDB).
