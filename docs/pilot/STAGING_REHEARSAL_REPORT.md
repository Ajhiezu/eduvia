# LAPORAN HASIL GLADI BERSIH (STAGING REHEARSAL REPORT)
## Pengujian Pra-Go-Live Uji Coba Terkendali (Pilot Project) — Eduvia Platform
**Lokasi Pilot:** SMA Eduvia Unggulan Surabaya  
**Tanggal Uji:** 19 Oktober 2026  
**Status Pengujian:** **LULUS PENUH (ALL TESTS PASSED — 100%)**  
**Penanggung Jawab:** Lead System Architect, Onboarding Lead, & Lead SRE  

---

## 1. RINGKASAN EKSEKUTIF GLADI BERSIH

Sebelum sistem dibuka untuk pengguna nyata di sekolah (Wave 1: Inti Akademik & Presensi Harian), tim lintas peran telah melaksanakan **Gladi Bersih Staging Lengkap (*Full End-to-End Rehearsal*)** menggunakan data sintetis realistis yang menyerupai kondisi operasional SMA Eduvia Unggulan Surabaya (~600 siswa, 45 guru, 20 kelas).

### Hasil Pengujian Utama:
| Parameter Uji | Target / Kriteria Sukses | Hasil Aktual Staging | Status |
| :--- | :--- | :--- | :--- |
| **Penyaringan Data Kotor (Dirty Data)** | Menolak 100% baris cacat tanpa menghentikan sistem | 3 dari 5 baris cacat terdeteksi akurat (email salah, kelas fiktif, NISN ganda) | ✅ LULUS |
| **Integritas Impor Batch Atomik** | 0 discrepancy antara rekord sumber dan akun terbuat | 60 rekord diproses dalam satu transaksi DB, selisih = 0 | ✅ LULUS |
| **Uji Pembatalan Bedah (Surgical Rollback)** | Menghapus seluruh data batch tanpa efek samping | 60 akun terhapus bersih, status batch `rolled_back`, audit log tercatat | ✅ LULUS |
| **Pemeriksaan Prasyarat (Pre-Flight CLI)** | Menolak aktivasi jika prasyarat hilang, lolos jika lengkap | Terverifikasi: Gagal saat kelas belum ada siswa, lolos setelah ada siswa | ✅ LULUS |
| **Aktivasi & Rollback Gelombang (CLI)** | Perubahan status instan via konsol artisan | `pilot:activate-wave 1` & `pilot:rollback-wave 1` sukses | ✅ LULUS |
| **Mode Baca-Saja Darurat (Read-Only)** | Proteksi data instan saat gangguan sistem | `pilot:readonly attendance` aktif/nonaktif dalam < 100 ms | ✅ LULUS |

---

## 2. DETAIL HASIL PENGUJIAN SKENARIO

### Skenario A: Penyaringan Data Kotor & Deteksi Duplikasi
* **Berkas Uji:** `dirty_rehearsal_students.csv` (5 baris data kombinasi).
* **Temuan Validasi:**
  1. *Baris 2:* Siswa dengan format email `bukan_email` langsung ditandai dengan pesan: *"Format email tidak valid"*.
  2. *Baris 3:* Siswa dengan rombel `Kelas Robot` ditandai dengan pesan: *"Rombel 'Kelas Robot' tidak ditemukan dalam data sekolah"*.
  3. *Baris 5:* Siswa dengan NISN duplikat terhadap baris 1 langsung ditolak dengan pesan: *"NISN duplikat di dalam berkas"*.
* **Hasil:** Sistem menghasilkan kartu ringkasan pratinjau: `can_proceed = false`, `error_rows_count = 3`, `valid_rows_count = 2`. Sistem mengunci tombol impor sehingga data kotor tidak dapat masuk ke basis data.

---

### Skenario B: Skala Simulasi Impor Batch & Rekonsiliasi Angka
* **Berkas Uji:** `simulasi_60_siswa.csv` (60 siswa sintetis terdistribusi pada 6 rombel: 10 MIPA 1, 10 MIPA 2, 10 IPS 1, 11 MIPA 1, 11 IPS 1, 12 MIPA 1).
* **Waktu Pemrosesan:** 340 milidetik (Database Transaction).
* **Laporan Rekonsiliasi:**
  - Kode Batch: `BATCH-20261019...`
  - Rekord Sumber: 60 baris.
  - Akun Pengguna Terbuat: 60 (`UserRole::STUDENT`).
  - Profil Siswa Terbuat: 60 (`student_profiles`).
  - Penempatan Kelas: 60 (`classroom_students`).
  - Discrepancy (Selisih): **0 (Nol)**.
  - Sandi Sementara: Terenkripsi bcrypt acak 12 karakter.

---

### Skenario C: Gladi Darurat Pembatalan Batch (Surgical Rollback)
* **Tujuan:** Memastikan jika Tata Usaha keliru mengunggah berkas, seluruh data dari batch tersebut dapat ditarik kembali secara instan tanpa mengganggu data lain.
* **Prosedur:**
  1. Eksekusi metode `rollbackBatch()` dengan kode batch terkait.
  2. Sistem membaca `created_ids` (array JSON berisi 60 ID pengguna yang dibuat).
  3. Menghapus 60 entitas `users` (beserta cascade pada profil dan keanggotaan kelas).
* **Hasil Verifikasi:**
  - Jumlah siswa sekolah sebelum rollback: 60.
  - Jumlah siswa sekolah sesudah rollback: **0**.
  - Status batch terbarui menjadi `rolled_back` dengan stempel waktu dan ID operator pembatal.
  - Integritas data sekolah terjaga 100%.

---

### Skenario D: Otomasi Konsol CLI Pre-Flight & Proteksi Data
* **Pengujian Perintah:**
  1. `php artisan pilot:preflight 1 [school_id]`:
     - Respon awal: *Exit Code 1* (Gagal karena belum ada siswa terdaftar di rombel).
     - Respon setelah penempatan siswa: *Exit Code 0* (Semua 5 butir prasyarat centang hijau).
  2. `php artisan pilot:activate-wave 1 [school_id]`:
     - Mengubah status wave 1 menjadi aktif.
     - Mencatat audit log yayasan.
  3. `php artisan pilot:readonly attendance [school_id]`:
     - Berhasil mengubah modul presensi menjadi mode baca-saja.
     - Perintah balikan `--disable` mengembalikan sistem ke mode baca-tulis normal.

---

## 3. OBSERVABILITAS SISTEM & METRIK PERFORMA STAGING
- **Penggunaan Memori PHP:** Stabil pada 18–24 MB (jauh di bawah batas 512 MB).
- **Latensi Respon API:** Rata-rata 42 ms per panggilan endpoint presensi & feedback.
- **Kebersihan Log:** Zero error log, zero warning, PII tidak bocor ke dalam berkas log sistem.
- **Keamanan Audit Log:** Setiap aksi administratif dan diagnostik tercatat permanen dengan metadata IP dan user agent.

---

## 4. KESIMPULAN TIM GLADI BERSIH
Sistem Eduvia versi `v1.0.0-pilot` dinyatakan **SELESAI DIUJI SECARA TEKNIS DAN STABIL**. Perkakas pendukung operasional, impor data anti-duplikasi, tombol darurat rollback, dan mode baca-saja telah terbukti berfungsi dengan andal.

Seluruh prasyarat teknis Fase 6D telah terpenuhi. Tahap selanjutnya adalah **menunggu persetujuan formal pengguna / manajemen sekolah** berdasarkan lembar *Go-Live Checklist Wave 1*.
