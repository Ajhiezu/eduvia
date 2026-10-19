# EDUVIA GO-LIVE READINESS MATRIX
## Matriks Evaluasi Kesiapan Peluncuran Produksi Antar-Modul (Fase 1 s/d Fase 4)
**Status Dokumen:** Resmi (Fase 5H Baseline)  
**Tanggal Evaluasi:** 19 Oktober 2026  
**Penilai:** Principal System Architect, Lead Security Engineer, SRE, & Compliance Lead  
**Keputusan Rekomendasi:** **SIAP DILUNCURKAN SECARA BERTAHAP (PILOT READY)**  

---

## 1. MATRIKS KESIAPAN MODUL (MODULE READINESS EVALUATION)

| Modul Sistem | Cakupan Fitur Utama | Status Kesiapan | Risiko Tersisa (Residual Risk) | Mitigasi Sementara / Syarat Operasional |
| :--- | :--- | :--- | :--- | :--- |
| **Modul 1: Pondasi SaaS Multi-Sekolah** | Multi-tenancy, RBAC 6 peran, PPDB, Presensi, Rombel, Kepegawaian, Website CMS Sekolah. | **SIAP (READY)** | Risiko human-error admin salah memasukkan NPSN atau tahun ajaran aktif. | Panduan setup wizard terintegrasi; validasi format NPSN 8 digit. |
| **Modul 2: Komunikasi & Pembelajaran Inti** | CBT Online (Bank Soal, Exam Session, Autosave, Grading), Rapor Digital, Portal Orang Tua, Jadwal Belajar. | **SIAP (READY)** | Lonjakan trafik autosave jawaban saat 600 siswa submit serentak. | Benchmark lolos (latensi < 40ms); optimasi indeks `ea_attempt_question_unique` aktif; backup batch sync offline tersedia di Flutter. |
| **Modul 3: Operasional & Keuangan Sekolah** | Keuangan (SPP, Beasiswa, Invoice, Kwitansi, Reversal, Penagihan), Logistik/Aset, Perpustakaan, Persuratan. | **SIAP (READY)** | Kesalahan rekonsiliasi manual transfer bank jika gambar bukti buram. | Staf keuangan wajib melakukan verifikasi visual nominal sebelum klik *Verify*; log audit mencatat user verifikator. |
| **Modul 4A: Asisten AI Guru & Tata Kelola Kuota** | Generator RPP / Modul Ajar, Bank Soal CBT, Rubrik, PII Scrubber, Anti-Prompt Injection, Kuota Token Yayasan. | **SIAP DENGAN CATATAN** | Ketergantungan pada stabilitas API Google Gemini luar negeri; potensi model hallucination pada mata pelajaran lokal. | Guru diwajibkan mereview dan mengedit draf sebelum disimpan permanen (*human-in-the-loop*); kuota token dibatasi per sekolah; PII scrubber aktif menyamarkan NIK/NISN. |
| **Modul 4B: Mesin Alur Persetujuan (Approval Engine)** | Persetujuan Berjenjang (Keringanan SPP, Pengadaan Aset, Surat Tugas, Delegasi Wewenang, Audit Trail). | **SIAP (READY)** | Delegasi wewenang disalahgunakan jika masa berlaku terlalu panjang. | Telah dipasang batas waktu kadaluarsa otomatis (`valid_until`) dan pembatasan peran delegasi (SEC-FIND-06). |
| **Modul 4C: Payment Gateway & Rekonsiliasi Otomatis** | Integrasi Midtrans Core API (VA BCA/BNI/BRI/Mandiri, QRIS Gopay), Webhook Handler, Buku Besar Kas. | **SIAP (READY)** | Potensi network blip saat orang tua melakukan checkout pembayaran. | Idempotency key mencegah tagihan ganda; status tagihan otomatis disinkronkan saat webhook tiba atau via tombol refresh status. |
| **Modul 4D: Mobile Client Apps (Parent & Student)** | Flutter Apps: Dashboard, Presensi Realtime, Rapor Mobile, CBT Mobile, Notifikasi FCM, Chat Guru. | **SIAP (READY)** | Variasi perangkat Android lama dengan memori RAM < 2GB. | Hardening manifest selesai (`allowBackup=false`); offline SQLite caching untuk modul jadwal & agenda belajar. |

---

## 2. KRITERIA PEMBATALAN PELUNCURAN (STOP-SHIP CRITERIA)

Peluncuran produksi **WAJIB DIBATALKAN ATAU DITUNDA SEKETIKA** apabila salah satu kondisi di bawah ini terjadi sebelum atau saat tahap pilot berlangsung:

1. **Kebocoran Isolasi Tenant (Cross-Tenant Leak):** Ditemukan celah di mana pengguna dari satu sekolah dapat membaca atau mengubah data sekolah lain tanpa izin (IDOR pada data akademik/keuangan).
2. **Kegagalan Integritas Finansial:** Terjadi *double-charging* saldo orang tua atau invoice tercatat lunas tanpa mutasi dana yang valid pada payment gateway / buku besar.
3. **Data Loss Ujian CBT:** Jawaban siswa hilang lebih dari 1% selama sesi ujian serentak karena kegagalan mekanisme autosave atau koneksi basis data.
4. **Kebocoran Kredensial Kritis:** Ditemukannya secret key Midtrans, API key Gemini, atau master database credential pada repository publik atau respons API client.
5. **Kegagalan Restore Bencana:** Uji coba restore database gagal mencapai RTO < 30 menit atau RPO > 15 menit.
6. **Pelanggaran Keras UU PDP:** Teridentifikasinya transmisi data NIK atau nama anak secara terbuka ke pihak ketiga yang tidak berhak tanpa enkripsi atau tanpa persetujuan orang tua.

---

## 3. REKOMENDASI TAHAPAN PELUNCURAN (PHASED ROLLOUT ROADMAP)

Untuk meminimalkan risiko operasional, peluncuran Eduvia direkomendasikan berjalan dalam 3 fase bertahap:

```
[ Minggu 1–2: FASE PILOT ] ────────► [ Minggu 3: EVALUASI ] ────────► [ Minggu 4+: EKSPANSI ]
SMA Eduvia Unggulan Surabaya         Review Metrik SRE & Feedback     4 Unit Sekolah Lainnya
(1 Sekolah, ~600 Siswa Aktif)        Koreksi Minor & Tuning DB        (Total 5 Sekolah, 3.000 Siswa)
```

### Tahap 1: Pilot Terkendali (Minggu 1 – Minggu 2)
* **Sekolah Target:** 1 Sekolah Percontohan — **SMA Eduvia Unggulan Surabaya** (ID Sekolah: 1).
* **Profil Pengguna:** ~600 Siswa, 45 Guru, 5 Staf Tata Usaha & Keuangan.
* **Fitur yang Diaktifkan:**
  - Presensi Pagi Siswa via Web & Mobile.
  - CBT Ujian Tengah Semester (UTS) mata pelajaran tertentu.
  - Pembayaran SPP Online via Midtrans (Virtual Account).
  - Portal Orang Tua untuk pemantauan presensi dan tagihan.
* **Tujuan:** Menguji stabilitas server di dunia nyata, memvalidasi pemahaman guru terhadap UI, dan mengukur akurasi notifikasi WhatsApp/FCM ke orang tua.

### Tahap 2: Evaluasi & Stabilisasi (Minggu 3)
* **Aktivitas:**
  1. Analisis log error SRE dan metrik latensi p95 pada dashboard pemantauan.
  2. Rekonsiliasi akuntansi 100% antara mutasi bank dengan buku besar kas Eduvia.
  3. Pengumpulan survei kepuasan guru dan orang tua murid.
  4. Penyesuaian konfigurasi PHP-FPM / MySQL jika ditemukan titik jenuh (*bottleneck*).

### Tahap 3: Peluncuran Penuh Yayasan (Minggu 4 dan Seterusnya)
* **Sekolah Target:** Perluasan ke 4 unit sekolah lainnya (SD, SMP, SMK, dan SMA 2).
* **Kapasitas Penuh:** 5 Sekolah, 3.000 Siswa, 180 Guru, 30 Staf.
* **Pengaktifan Fitur Tambahan:**
  - Asisten AI Guru (dengan kuota token teralokasi per sekolah).
  - Modul Persuratan Digital & Logistik Aset Rombel.
  - Pengesahan Berjenjang (Approval Engine) untuk dispensasi SPP yayasan.
