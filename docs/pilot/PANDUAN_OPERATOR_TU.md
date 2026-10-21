# PANDUAN STANDAR OPERASIONAL OPERATOR TATA USAHA (TU)
## Onboarding Data, Manajemen Batch & Dukungan Level 1 Pilot Eduvia
**Lokasi Pilot:** SMA Eduvia Unggulan Surabaya  
**Sasaran:** Staf Tata Usaha, Operator Dapodik/SIM, & Helpdesk Internal Sekolah  
**Sistem:** Web Panel Administrasi Eduvia v1.0.0-pilot  

---

## 1. PERAN & TANGGUNG JAWAB OPERATOR TU
Staf Tata Usaha adalah pengelola integritas data (*Data Custodian*) sekolah. Dalam program uji coba pilot terkendali ini, tugas utama Operator TU meliputi:
1. Mempersiapkan dan memvalidasi berkas CSV data siswa, guru, dan rombongan belajar.
2. Melakukan pratinjau (*preview*) dan eksekusi impor batch dengan nol duplikasi.
3. Mencetak surat/label resmi berisikan **Akun & Kode Klaim Anak** untuk orang tua.
4. Menjadi garda pertama (Level 1 Support) dalam menangani keluhan lupa kata sandi atau kendala operasional harian guru/wali murid.
5. Menjalankan prosedur pembatalan (*rollback*) batch impor jika ditemukan kesalahan input massal.

---

## 2. STANDARISASI & PENGUNDUHAN TEMPLAT CSV

Sistem Eduvia menyediakan templat berkas CSV berstandar internasional yang telah disesuaikan dengan kompatibilitas Microsoft Excel bahasa Indonesia (menggunakan enkripsi **UTF-8 dengan BOM** agar karakter dan simbol tidak rusak).

### Prosedur Unduh Templat:
1. Masuk ke Web Portal Eduvia dengan akun `school_admin` atau `staff`.
2. Buka menu: **Operasional Sekolah > Manajemen Pilot & Migrasi**.
3. Klik tab **Unduh Templat CSV**:
   - `templat_impor_students_eduvia.csv` (Data Siswa).
   - `templat_impor_teachers_eduvia.csv` (Data Guru).
4. Simpan berkas di komputer kerja yang aman.

### Format Kolom Data Siswa (Wajib & Opsional):
| Nama Kolom | Status | Format / Contoh | Keterangan |
| :--- | :--- | :--- | :--- |
| `name` | Wajib | Ahmad Fauzan | Nama lengkap tanpa singkatan berlebihan |
| `email` | Wajib | ahmad.fauzan@eduvia.test | Email unik untuk akun login siswa |
| `phone` | Opsional | 081234567891 | Nomor ponsel / WhatsApp siswa |
| `nis` | Opsional | 1001 | Nomor Induk Siswa internal sekolah |
| `nisn` | Wajib | 0081234567 | 10 digit NISN resmi Kemendikbudristek |
| `gender` | Wajib | L atau P | L = Laki-laki, P = Perempuan |
| `birth_place` | Opsional | Surabaya | Kota kelahiran |
| `birth_date` | Opsional | 2008-05-14 | Format standar: `YYYY-MM-DD` |
| `classroom` | Wajib | 10 MIPA 1 | Nama rombel harus tepat sama dengan data master |

---

## 3. ALUR PRATINJAU (PREVIEW) & DETEKSI KESALAHAN DATA

Sebelum data masuk ke dalam basis data aktif sekolah, Operator TU **WAJIB** melalui gerbang pratinjau (*pre-flight validation*).

```
[Unggah CSV] ➜ [Validasi Sistem Otomatis] ➜ [Analisis Laporan Kesalahan] ➜ [Perbaikan] ➜ [Eksekusi Impor]
```

### Langkah Kerja:
1. Di halaman **Manajemen Pilot & Migrasi**, buka menu **Impor Data Mandiri**.
2. Pilih tipe entitas: **Siswa** atau **Guru**.
3. Pilih berkas CSV yang telah diisi dari komputer Anda.
4. Klik tombol **Validasi & Pratinjau Berkas**.
5. Sistem akan memeriksa tiga tingkat pemeriksaan secara instan:
   - **Pemeriksaan Format:** Kelengkapan kolom dan format tanggal.
   - **Pemeriksaan Duplikat Internal Berkas:** Memastikan tidak ada NISN atau Email ganda di dalam satu file.
   - **Pemeriksaan Duplikat Basis Data Sekolah:** Memastikan data belum pernah diimpor sebelumnya.
6. Periksa kartu ringkasan hasil pratinjau:
   - ✅ **Total Baris Valid:** Siap diimpor.
   - ⚠️ **Baris Duplikat / Eror:** Ditolak dengan rincian nomor baris dan penyebab kesalahan.
7. Jika terdapat eror, perbaiki berkas CSV di komputer Anda, lalu ulangi proses unggah hingga tingkat kesalahan **0 baris**.

---

## 4. EKSEKUSI IMPOR BATCH & LAPORAN REKONSILIASI

1. Setelah pratinjau bersih (0 baris salah), klik tombol **Eksekusi Impor Data**.
2. Sistem akan memproses seluruh baris dalam satu transaksi atomik.
3. Setelah selesai, layar akan menampilkan **Laporan Rekonsiliasi Impor**:
   - **Kode Batch Unik:** (Contoh: `BATCH-20261019-X9A2`).
   - **Jumlah Rekord Sumber:** Misal: 600 siswa.
   - **Jumlah Akun Terbuat:** Tepat 600 akun.
   - **Discrepancy (Selisih):** **0 (Nol)**.
   - **Kata Sandi Sementara:** Terbit secara otomatis dan terenkripsi aman.
4. Operator TU dapat mengunduh berkas ringkasan PDF atau mencetak slip tanda terima aktivasi akun untuk dibagikan kepada masing-masing siswa dan orang tua.

---

## 5. PROSEDUR SURGICAL ROLLBACK (PEMBATALAN BATCH)

Apabila di kemudian hari disadari bahwa berkas yang diimpor adalah berkas usang atau salah kelas, Operator TU dapat membatalkan batch tersebut secara aman dan bedah (*surgical*):

> [!WARNING]
> Prosedur Rollback hanya akan menghapus data yang secara spesifik dibuat oleh batch tersebut, tanpa menghapus akun siswa lama atau merusak data kelas lain.

1. Buka menu **Riwayat Batch Impor**.
2. Temukan kode batch yang bermasalah.
3. Klik tombol **Batalkan Batch (Rollback)**.
4. Masukkan alasan pembatalan (misal: *"Salah memilih berkas tahun ajaran lama"*).
5. Masukkan kata sandi administrator Anda untuk konfirmasi keamanan.
6. Sistem akan menghapus seluruh data siswa dari batch tersebut dan mencatat tindakan rollback di dalam log audit keamanan sekolah.

---

## 6. LAYANAN DUKUNGAN TINGKAT PERTAMA (LEVEL 1 HELPDESK)

### Kasus A: Siswa / Guru Lupa Kata Sandi
1. Buka menu **Pengguna & Akun > Cari Pengguna**.
2. Cari berdasarkan Nama Lengkap atau NISN.
3. Klik tombol **Atur Ulang Sandi**.
4. Sistem akan menghasilkan sandi sementara baru (atau mengirimkan tautan reset ke email terdaftar).
5. Ingatkan pengguna untuk mengganti sandi tersebut saat pertama kali login kembali.

### Kasus B: Orang Tua Kehilangan Kode Klaim Anak
1. Buka menu **Data Siswa > Detail Siswa**.
2. Buka tab **Relasi Wali Murid**.
3. Klik tombol **Regenerasi Kode Klaim**.
4. Berikan kode baru berdurasi aktif 7x24 jam kepada orang tua secara langsung atau via WhatsApp resmi sekolah yang terverifikasi.

### Kasus C: Eskalasi ke Level 2 (PIC Sekolah & Tim SRE Eduvia)
Jika ditemukan kendala sistemik (misal: seluruh guru tidak bisa membuka menu presensi, atau muncul pesan HTTP 500 Server Error):
1. Catat jam terjadinya masalah dan modul yang terdampak.
2. Ambil tangkapan layar (*screenshot*) pesan galat.
3. Hubungi Koordinator Lapangan Pilot (PIC) atau kirimkan tiket darurat ke Telegram/WhatsApp Emergency Support SRE Eduvia: `0811-XXXX-0099`.
