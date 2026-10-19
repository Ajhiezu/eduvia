# EDUVIA PHASE 6: PILOT RUNBOOK & IMPLEMENTATION BLUEPRINT
## Rencana Strategis Pelaksanaan Uji Coba Terkendali di Sekolah Nyata (SMA Eduvia Unggulan Surabaya)
**Status Dokumen:** Draf Kerja Baseline (Menunggu Persetujuan Manusia - Tahap 6A)  
**Tanggal Penyusunan:** 19 Oktober 2026  
**Peran Penyusun:** Product Manager (Pilot Lead), Implementation Specialist, SRE, Lead Developer, UX Researcher, Data Analyst  
**Sekolah Target Pilot:** SMA Eduvia Unggulan Surabaya (600 Siswa, 45 Guru, 5 Staf TU, 20 Rombel)  
**Durasi Pilot:** 8–12 Minggu Terbagi dalam 6 Gelombang Bertahap  

> [!NOTE]
> **DISCLAIMER HUKUM:**  
> Seluruh instrumen perjanjian, dokumen pemrosesan data, dan formulir persetujuan orang tua dalam dokumen ini merupakan draf kerja teknis untuk membantu manajemen yayasan, bukan nasihat hukum formal (*legal counsel*). Wajib ditinjau dan divalidasi oleh konsultan hukum berizin sebelum disirkulasikan ke pihak sekolah atau orang tua.

---

## 1. PEMERIKSAAN BASELINE & STATUS INFRASTRUKTUR GIT

* **Branch Aktif:** `main` (Semua pekerjaan dilakukan langsung di `main` tanpa membuat branch baru).
* **Git Status:** Clean working tree, tidak ada perubahan lokal yang tertinggal.
* **Status Deploy Otomatis (CI/CD):** **TIDAK ADA PIPELINE DEPLOY OTOMATIS**. Repositori tidak memiliki folder `.github/workflows` atau script continuous deployment. Seluruh deployment ke staging/produksi dilakukan secara manual melalui perintah manusia yang disengaja.
* **Status Temuan Keamanan Fase 5:** **100% TEMUAN KRITIS & TINGGI TELAH TERTUTUP** (SEC-FIND-01 s/d SEC-FIND-08 selesai dan memiliki tes regresi hijau).
* **Status Pengujian Sistem Baseline:**
  - **Backend Laravel PHPUnit:** 16/16 tests lolos (125 assertions) — Suite Keamanan, Uji Beban, dan Observabilitas.
  - **Mobile-parent Flutter:** 1/1 widget smoke test lulus.
  - **Mobile-student Flutter:** 3/3 role navigation & widget tests lulus.
* **Checkpoint Tag:** `phase6-baseline` berhasil ditandai pada commit terakhir di `main`.

---

## 2. LIMA RISIKO TERBESAR DALAM PILOT & MITIGASI AWAL

Berdasarkan penilaian 6 peran profesional, berikut adalah 5 risiko tertinggi yang dihadapi selama uji coba di SMA Eduvia Unggulan Surabaya:

| No | Risiko Spesifik Pilot | Sumber Penyebab | Potensi Dampak | Mitigasi Awal yang Direncanakan |
| :---: | :--- | :--- | :--- | :--- |
| **1** | **Kualitas Data Awal Rendah (Legacy Data Hygiene)** | Data lama di Excel/buku kertas memiliki NISN kosong, NIK typo, atau nomor WhatsApp ortu tidak aktif. | Gagal impor, siswa tidak tertaut ke orang tua, TU kewalahan memvalidasi manual. | Perkakas impor 6B dilengkapi *Pre-import Validation* & *Duplicate Detector* yang menampilkan baris galat secara spesifik sebelum disimpan; rekonsiliasi bertanda tangan PIC. |
| **2** | **Beban Ganda Guru (Double Entry Resistance)** | Guru harus mengisi presensi di buku absensi kelas lama DAN di aplikasi Eduvia selama paralel run. | Guru menolak mengisi harian dan baru mengisi sekaligus di akhir pekan (data tidak realtime). | Desain alur presensi mobile cepat (< 2 menit per rombel); pelatihan terfokus pada "Champion Guru"; reward pengakuan bagi rombel tercepat. |
| **3** | **Kekhawatiran Privasi Data Anak & Keuangan** | Orang tua ragu memasang aplikasi karena takut data anak disalahgunakan atau penipuan SPP. | Adopsi orang tua rendah (< 50%), komplain ke pihak yayasan. | Surat edaran resmi yayasan dengan penjelasan UU PDP Pasal 25 yang transparan; materi komunikasi awam di 6C; opsi pembayaran manual tetap tersedia. |
| **4** | **Pentest Eksternal Pihak Ketiga Belum Berjalan** | Audit internal Fase 5 tuntas, namun audit pihak ketiga independen belum dieksekusi. | Potensi kerentanan *zero-day* pada alur pembayaran atau hak akses khusus yang belum teridentifikasi. | **Modul Finansial Online (VA/QRIS) dan Ujian CBT Berisiko Tinggi DITUNDA** ke Gelombang 4 & 5; tahap awal difokuskan pada presensi dan komunikasi akademik. |
| **5** | **Kepadatan Wi-Fi Sekolah di Jam Sibuk (06:45–07:15)** | Ratusan perangkat mengakses router sekolah bersamaan saat jam masuk pagi. | Request presensi timeout, aplikasi mobile terasa lag/membeku. | Optimasi batch endpoint presensi (latensi 34ms); offline caching pada Flutter SQLite; opsi presensi via kuota seluler mandiri guru. |

---

## 3. RANCANGAN PILOT LENGKAP (PILOT DESIGN SPECIFICATION)

### A. Tujuan & Hipotesis Terukur (Measurable Hypotheses)

Pilot dirancang sebagai eksperimen berbasis bukti (*evidence-based proof*), dengan 4 hipotesis kunci:

* **Hipotesis 1 (Efisiensi Presensi Guru):**  
  *"Guru pengampu atau wali kelas dapat menyelesaikan pencatatan presensi harian 1 kelas (30 siswa) dalam waktu kurang dari 2 menit melalui ponsel, memangkas 60% waktu dibanding rekap manual kertas (5–7 menit)."*
* **Hipotesis 2 (Adopsi Orang Tua):**  
  *"Sedikitnya 70% orang tua murid kelas X, XI, dan XII mengaktifkan aplikasi `Mobile-parent` dan menautkan akun anaknya melalui kode klaim keluarga dalam 4 minggu pertama sosialisasi."*
* **Hipotesis 3 (Keterhubungan Pembelajaran):**  
  *"Sedikitnya 85% siswa aktif memeriksa agenda tugas dan jadwal pelajaran melalui `Mobile-student` minimal 3 hari dalam seminggu."*
* **Hipotesis 4 (Transparansi Keuangan - Fase Lanjutan):**  
  *"Waktu konfirmasi dan penerbitan kwitansi pembayaran SPP sekolah terpangkas dari 3 hari kerja (rekap bukti kertas) menjadi kurang dari 4 jam kerja pada penagihan bulan kedua."*

---

### B. Metrik Keberhasilan & Ambang Batas Keputusan (Success Metrics & Gates)

Keputusan untuk **Lanjut (Go) / Perbaiki Dulu (Hold & Fix) / Mundur (Abort)** dievaluasi mingguan berdasarkan ambang batas kuantitatif:

| Metrik Kunci | Target Ideal (Lanjut) | Ambang Tahan (Perbaiki Dulu) | Ambang Mundur (Stop Gate) |
| :--- | :--- | :--- | :--- |
| **Guru Aktif Mingguan (WAU)** | >= 85% guru mengisi presensi tepat waktu | 65% – 84% aktif | < 65% guru aktif dalam 2 minggu berturut-turut |
| **Orang Tua Terhubung (Claimed)** | >= 70% siswa tertaut akun orang tua di Mg 4 | 50% – 69% tertaut | < 50% tertaut pada minggu ke-4 |
| **Waktu Pengisian Presensi Rombel** | < 2 menit per kelas | 2 – 4 menit per kelas | > 5 menit per kelas (lebih lambat dari kertas) |
| **Insiden Kritis (P0: Data Leak / Money Loss)** | **0 Insiden** | 0 Insiden (dengan alert mitigasi) | **>= 1 Insiden P0 Tidak Tertangani** |
| **Tiket Bantuan Terbuka (P1 / P2)** | 100% selesai dalam 24 jam kerja | Selesai dalam 48 jam | > 10 tiket P1 menumpuk tanpa solusi > 48 jam |
| **Kepuasan Pengguna (CSAT Survei)** | Skor >= 4.0 / 5.0 | Skor 3.0 – 3.9 / 5.0 | Skor < 3.0 / 5.0 |

---

### C. Gelombang Aktivasi Bertahap (Phased Rollout Waves)

Setiap gelombang berjalan secara berurutan dan **hanya dapat dibuka jika kriteria lolos gelombang sebelumnya telah terpenuhi**:

```
[ GELOMBANG 1 ] ──► [ GELOMBANG 2 ] ──► [ GELOMBANG 3 ] ──► [ GELOMBANG 4 ] ──► [ GELOMBANG 5 ] ──► [ GELOMBANG 6 ]
Inti Akademik         Aplikasi Ortu &       Nilai Tugas &        Keuangan Manual     CBT Kuis             Modul AI &
(Data, Presensi)      Siswa (Mobile)        Buku Rapor           & SPP (VA Nanti)    Formatif Kecil       Approval Yayasan
(Durasi: 2 Minggu)    (Durasi: 2 Minggu)    (Durasi: 2 Minggu)   (Durasi: 2 Minggu)  (Durasi: 2 Minggu)   (Durasi: 2 Minggu)
```

#### Rincian Prasyarat, Kriteria Lolos, & Kriteria Mundur per Gelombang:

1. **Gelombang 1: Inti Akademik & Presensi Harian (Minggu 1–2)**
   - **Fitur Aktif:** Data Master Siswa/Guru/Kelas, Jadwal Pelajaran, Presensi Masuk Pagi & Jam Pembelajaran, Pengumuman Sekolah.
   - **Prasyarat:** Impor data master 100% tervalidasi dan direkonsiliasi; pelatihan 45 guru dan 5 staf TU tuntas; jurnal kelas kertas tetap diisi sebagai backup (paralel run).
   - **Kriteria Lolos:** >= 85% presensi terisi harian di sistem selama 10 hari sekolah berturut-turut; nol data presensi hilang.
   - **Kriteria Mundur:** Jika server sering hang saat jam masuk pagi (07:00 WIB) sehingga mengganggu ketertiban apel/kegiatan sekolah.

2. **Gelombang 2: Portal Mobile Siswa & Orang Tua (Minggu 3–4)**
   - **Fitur Aktif:** Aplikasi `Mobile-student` & `Mobile-parent` (Dashboard, Riwayat Kehadiran Realtime, Pengumuman, Izin Sakit Mobile).
   - **Prasyarat:** Surat edaran resmi sekolah ke orang tua dibagikan; lembar kode klaim anak dicetak aman dan dibagikan secara privat; FAQ disosialisasikan.
   - **Kriteria Lolos:** >= 70% orang tua berhasil mengklaim akun anak; notifikasi izin sakit dari orang tua masuk ke dashboard guru.
   - **Kriteria Mundur:** Laporan massal kode klaim gagal atau salah tautan anak (> 5% kasus).

3. **Gelombang 3: Nilai Harian, Tugas, & E-Rapor (Minggu 5–6)**
   - **Fitur Aktif:** Pemberian tugas harian, pengumpulan berkas, pembobotan nilai, pratinjau rapor digital.
   - **Prasyarat:** Guru mapel telah mengikuti workshop pembobotan nilai Kurikulum Merdeka di Eduvia; data nilai tugas sebelumnya telah diarsip di Excel guru.
   - **Kriteria Lolos:** >= 80% guru aktif menginput nilai tugas; perhitungan rata-rata rapor klop 100% dengan hitungan Excel manual guru.
   - **Kriteria Mundur:** Rumus agregasi nilai rapor menghasilkan selisih angka dibanding standar pedoman kurikulum sekolah.

4. **Gelombang 4: Keuangan & SPP Terkendali (Minggu 7–8)**
   - **Fitur Aktif:** Penagihan invoice SPP, unggah bukti transfer manual, verifikasi staf keuangan, penerbitan kwitansi digital. *(Pembayaran otomatis VA/QRIS Midtrans tetap OFF sampai izin legal dan pentest luar negeri tuntas).*
   - **Prasyarat:** Rekonsiliasi saldo tagihan awal tahun ajaran disepakati kepala tata usaha; staf kasir terlatih memverifikasi bukti bayar.
   - **Kriteria Lolos:** >= 100 transaksi SPP diverifikasi tanpa selisih nominal kas fisik/bank; nol keluhan tagihan ganda.
   - **Kriteria Mundur:** Terjadi duplikasi invoice atau perselisihan nominal tagihan siswa.

5. **Gelombang 5: Ujian CBT Formatif Terkendali (Minggu 9–10)**
   - **Fitur Aktif:** Bank soal guru, ujian kuis CBT online (maksimal 1 kelas per sesi, 30 siswa simultan, kuis harian berisiko rendah, **BUKAN** ujian kelulusan/UAS).
   - **Prasyarat:** Uji koneksi Wi-Fi di laboratorium komputer/ruang kelas; simulasi pengerjaan kuis fiktif oleh perwakilan guru.
   - **Kriteria Lolos:** Ujian formatif selesai tanpa ada jawaban siswa hilang akibat autosave gagal; auto-grading pilihan ganda akurat.
   - **Kriteria Mundur:** Terjadi kehilangan jawaban siswa > 1 orang akibat kegagalan sinkronisasi offline.

6. **Gelombang 6: Modul Sensitif & Lanjutan (Minggu 11–12)**
   - **Fitur Aktif:** Asisten AI Guru (Lesson Plan & Pembuat Soal), Alur Persetujuan Bertingkat (Dispensasi SPP), Dashboard Analisis Yayasan.
   - **Prasyarat:** Kuota token AI disetujui bendahara yayasan; pelatihan etika AI guru selesai; konsultan hukum menyetujui draf privasi.
   - **Kriteria Lolos:** Guru memanfaatkan AI sebagai asisten draf dan mengeditnya secara manual; approval dispensasi disetujui sesuai rantai wewenang.

---

### D. Rencana Paralel Run & Jalur Mundur (Rollback Plan)

Sekolah tidak boleh mengalami gangguan operasional pendidikan:
* **Prinsip Paralel Run:**
  - Selama Gelombang 1 & 2, presensi manual kertas tetap diisi oleh ketua kelas dan guru piket.
  - Selama Gelombang 4, pembukuan kas fisik sekolah tetap mencatat nomor kwitansi manual di samping nomor kwitansi sistem Eduvia.
* **Jalur Mundur Darurat (Rollback Procedure):**
  - Setiap modul memiliki saklar feature flag per tenant (`TenantFeatureFlagService`). Jika modul presensi mengalami kegagalan teknis, admin dapat menonaktifkan modul tersebut via command line dalam hitungan detik.
  - Tersedia fitur **Ekspor Data Darurat** (CSV/Excel) untuk menarik seluruh data presensi atau keuangan yang sudah sempat diinput di Eduvia agar sekolah dapat melanjutkan administrasi tanpa kehilangan rekam jejak.
  - Kapan sistem lama boleh dimatikan? **Hanya setelah sekolah menandatangani Berita Acara Penerimaan (BAP) Pasca-Pilot di akhir Minggu ke-12**.

---

### E. Struktur Dukungan & Triase Insiden di Lapangan

```
[ Siswa / Ortu / Guru ] ──► [ Champion Guru Sekolah ] ──► [ Tim Onboarding L2 ] ──► [ SRE / Dev Lead L3 ]
 (Tanya / Lapor Masalah)      (Solusi Cepat 10 Menit)       (Tiket Teknis < 1 Jam)    (Bugfix/Hotfix P0/P1)
```

* **Level 1 (Sekolah): "Champion Guru"**
  - 2 orang guru muda percontohan yang dibekali pelatihan intensif dan panduan FAQ cepat untuk membantu rekan guru yang lupa sandi atau bingung navigasi.
* **Level 2 (Eduvia Onboarding Support):**
  - Grup WhatsApp Khusus Tim Inti Pilot (PIC Sekolah, Kepala Sekolah, Implementation Lead).
  - Jam Operasional Siaga: 06:30 – 16:30 WIB (Senin – Jumat).
* **Level 3 (SRE & Lead Developer):**
  - Hotline darurat P0 untuk insiden kebocoran data atau server crash (SLA respons < 15 menit).

---

### F. Komunikasi Publik & Penanganan Keberatan Orang Tua

1. **Surat Pemberitahuan Resmi Sekolah:**
   - Diterbitkan dengan kop resmi SMA Eduvia Unggulan Surabaya, ditandatangani Kepala Sekolah.
   - Memuat: Tujuan digitalisasi, tautan unduh aplikasi resmi, jaminan pelindungan data anak (UU PDP), dan nomor helpdesk resmi sekolah.
2. **Penanganan Keberatan Orang Tua (Opt-Out / Resistance):**
   - Jika ada orang tua yang menolak menggunakan aplikasi seluler atau tidak memiliki smartphone, sekolah **tetap melayani laporan perkembangan anak via buku fisik dan kartu SPP manual**. Orang tua tidak boleh dipaksa atau diskriminasi.

---

### G. Kebijakan Data Pilot & Kerahasiaan

1. **Karantina Data:** Data siswa asli SMA Eduvia Unggulan hanya tersimpan di database resmi sekolah dan tidak boleh diekspor ke laptop pribadi tim pengembang tanpa izin tertulis.
2. **Larangan PII di Git & Log:** Seluruh bug reproduksi wajib menggunakan data fiktif (*synthetic data*). Log sistem wajib bebas dari NIK dan nomor rekening.
3. **Akhir Pilot:** Jika sekolah memutuskan tidak melanjutkan sistem Eduvia, seluruh data sekolah wajib diekspor lengkap dan diserahkan kepada pihak sekolah dalam kurun waktu 14 hari kalender, dilanjutkan pemusnahan salinan (*secure deletion*) dari server pengujian.

---

### H. Kriteria Henti Darurat (Emergency Abort)

Pilot **DIHENTIKAN SEKETIKA SECARA KESELURUHAN** jika:
1. Terjadi insiden kebocoran data siswa antar-sekolah (data SMA dilihat oleh SMP atau sebaliknya).
2. Terjadi insiden kehilangan saldo kas atau tagihan fiktif yang tidak dapat dilacak jejak auditnya.
3. Terjadi kebocoran integritas soal ujian sebelum jadwal pengerjaan dimulai.
4. Perintah resmi tertulis penghentian dari Ketua Pengurus Yayasan atau Kepala Sekolah.

---

## 4. DAFTAR KEPUTUSAN & PRASYARAT HUKUM YANG DIPERLUKAN

Sebelum manusia (tim operasional sekolah) memulai migrasi data riil di lapangan, dokumen-dokumen berikut harus disiapkan dan ditandatangani:

1. **Perjanjian Kerja Sama Uji Coba Pilot (Pilot Agreement)** antara Yayasan Eduvia dan penyedia platform *(perlu ditinjau konsultan hukum)*.
2. **Perjanjian Pemrosesan Data (Data Processing Agreement / DPA)** yang mendefinisikan batas tanggung jawab sekolah (Pengendali Data) dan platform (Prosesor Data) sesuai UU PDP *(perlu ditinjau konsultan hukum)*.
3. **Formulir Persetujuan Tertulis Orang Tua (Parental Consent Form)** untuk pemrosesan data anak di bawah umur *(perlu ditinjau konsultan hukum)*.
4. **Keputusan Tertulis User:** Penundaan fitur pembayaran otomatis Midtrans dan CBT UAS massal hingga hasil audit eksternal independen tuntas.

---

## 5. REKOMENDASI TAHAP SELANJUTNYA (NEXT STEPS)

Jika Rancangan Pilot Tahap 6A ini disetujui, langkah berikutnya adalah:
* **Tahap 6B (Pembangunan Perkakas Pendukung Pilot):** Membangun script impor data CSV sekolah dengan validasi idempoten, antrean umpan balik aplikasi, metrik agregat bebas PII, dan skrip pre-flight check aktivasi gelombang.
* **Tahap 6C (Materi Pelatihan & Panduan Pengguna):** Menyusun buku panduan ringkas langkah-demi-langkah per peran (Guru, Ortu, Siswa, TU).
* **Tahap 6D (Rehearsal Staging):** Melakukan gladi bersih seluruh alur onboarding menggunakan data sintetis kotor di staging.
