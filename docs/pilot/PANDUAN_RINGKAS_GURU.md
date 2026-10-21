# PANDUAN RINGKAS GURU & WALI KELAS
## Uji Coba Terkendali (Pilot Program) — Eduvia Platform
**Lokasi Pilot:** SMA Eduvia Unggulan Surabaya  
**Sasaran:** Guru Mata Pelajaran & Wali Kelas (45 Tenaga Pendidik)  
**Versi Aplikasi:** Eduvia Web & Mobile Teacher v1.0.0-pilot  

---

## 1. PENDAHULUAN & TUJUAN PILOT
Selamat datang di program uji coba resmi platform **Eduvia**. Sebagai tenaga pendidik, peran Bapak/Ibu adalah ujung tombak dalam memvalidasi keandalan dan kenyamanan sistem pada kegiatan belajar mengajar harian.

Tujuan utama panduan ini adalah memastikan Bapak/Ibu dapat:
1. Menyelesaikan pencatatan presensi kelas harian dalam waktu **kurang dari 2 menit**.
2. Mengunggah materi ajar dan memberikan tugas terstruktur dengan mudah.
3. Mengetahui langkah antisipasi dan pelaporan cepat saat menghadapi kendala koneksi atau teknis di ruang kelas.

---

## 2. LOGIN PERDANA & PENGATURAN KEAMANAN

### Langkah Akses:
1. Buka peramban (Chrome / Edge) pada laptop atau aplikasi **Eduvia Teacher** pada ponsel Android Anda.
2. Masukkan alamat: `https://eduvia.sch.id/login` (atau alamat staging uji coba yang diberikan oleh PIC Sekolah).
3. Masukkan **Email Resmi** dan **Kata Sandi Sementara** yang telah dibagikan oleh staf Tata Usaha.
4. Pada login perdana, sistem akan mewajibkan penggantian kata sandi baru:
   - Minimal 8 karakter.
   - Kombinasi huruf besar, huruf kecil, dan angka.
   - *Jangan membagikan kata sandi kepada siswa atau orang lain.*

> [!TIP]
> Aktifkan fitur *Biometric Login* (Sidik Jari / Face ID) pada aplikasi ponsel Anda agar tidak perlu mengetik sandi berulang kali saat masuk kelas.

---

## 3. ALUR PRESENSI HARIAN KELAS (TARGET < 2 MENIT)

Presensi harian siswa dapat dilakukan langsung dari ponsel saat Bapak/Ibu berada di ruang kelas:

```
[Buka Aplikasi] ➜ [Pilih Kelas & Jam Mengajar] ➜ [Buka Sesi Presensi] ➜ [Verifikasi Hadir/S/I/A] ➜ [Simpan & Finalisasi]
```

### Langkah Operasional:
1. Masuk ke menu **Presensi Kelas**.
2. Pilih rombongan belajar (misal: `10 MIPA 1`) dan mata pelajaran yang sedang berlangsung.
3. Klik tombol **Buat Sesi Presensi Baru**. Tanggal dan jam otomatis terisi sesuai waktu server.
4. **Prinsip Cepat (Default Hadir):** Seluruh siswa pada daftar secara otomatis berstatus **Hadir (H)**.
5. Bapak/Ibu hanya perlu mengubah status siswa yang berhalangan:
   - **S (Sakit):** Jika ada surat dokter / pemberitahuan resmi dari orang tua.
   - **I (Izin):** Jika ada dispensasi kegiatan sekolah atau permohonan keluarga.
   - **A (Alpa):** Jika siswa tidak hadir tanpa keterangan yang sah.
6. Masukkan catatan singkat jika diperlukan (misal: *"Sakit flu di UKS jam ke-3"*).
7. Klik **Simpan & Finalisasi Presensi**.
8. Sistem akan mengirimkan rekapitulasi data kehadiran ke wali kelas dan notifikasi aman kepada orang tua secara otomatis.

---

## 4. PEMBAGIAN MATERI & PENUGASAN (GELOMBANG 3)

1. Buka menu **Materi & Tugas**.
2. Klik **Tambah Materi**:
   - Tulis judul materi dan deskripsi capaian pembelajaran.
   - Lampirkan berkas (PDF, presentasi PPT, atau dokumen Word maksimal 10 MB).
   - Tentukan apakah materi terbuka untuk seluruh kelas atau kelas tertentu saja.
3. Klik **Buat Tugas Baru**:
   - Tentukan batas waktu pengumpulan (*due date*) dengan tanggal dan jam yang jelas.
   - Pilih jenis pengumpulan: Teks langsung atau unggah berkas tugas.
   - Siswa akan menerima notifikasi pengingat pengumpulan tugas di aplikasi mereka.

---

## 5. PROSEDUR FALLBACK: JIKA KONEKSI INTERNET MATI (OFFLINE)

Jika koneksi Wi-Fi atau seluler di ruang kelas mengalami gangguan total saat jam pelajaran:

> [!IMPORTANT]
> **JANGAN MEMAKSAKAN APLIKASI SAAT OFFLINE HINGGA MENGGANGGU WAKTU MENGAJAR.**
> KBM tetap menjadi prioritas nomor satu.

1. **Gunakan Lembar Cadangan Fisik (Paper Backup):**
   - Catat siswa yang tidak hadir pada formulir kertas presensi darurat yang tersedia di meja kelas.
2. **Sinkronisasi Susulan:**
   - Setelah kembali ke ruang guru atau saat koneksi internet pulih, buka menu **Presensi** dan masukkan catatan kehadiran hari tersebut.
   - Beri centang/catatan *"Input Susulan Karena Kendala Jaringan"*.
3. Sistem mengizinkan input presensi susulan hingga pukul **17:00 WIB** pada hari yang sama tanpa penalti validasi.

---

## 6. CARA MELAPORKAN KENDALA (IN-APP FEEDBACK)

Jika Bapak/Ibu menemukan tombol yang macet, tampilan yang membingungkan, atau pesan galat:

1. Klik ikon **Bantuan & Masukan (Ikon Tanda Tanya / Seru)** di pojok kanan atas aplikasi.
2. Pilih Kategori:
   - **Bug / Kerusakan:** Jika fungsi aplikasi gagal atau eror.
   - **Tampilan Bingung (UX Friction):** Jika alur tombol sulit dipahami.
   - **Saran Perbaikan:** Ide untuk membuat fitur lebih cepat dan nyaman.
3. Berikan judul ringkas dan jelaskan kendalanya.
4. *(Opsional)* Unggah tangkapan layar (*screenshot*) masalah.
5. Tekan **Kirim Umpan Balik**.
   - Tim SRE dan pengembang Eduvia akan langsung menerima laporan Anda tanpa mengungkap data pribadi siswa.
   - Tim teknis akan menindaklanjuti dalam waktu maksimal 2 jam kerja.

---

## 7. KONTAK PENDAMPINGAN LAPANGAN (LEVEL 1 SUPPORT)
Jika membutuhkan bantuan langsung di tempat:
- **Koordinator Guru Pendamping (PIC):** Bpk. Hendra Gunawan, S.Pd (Ruang Guru Lt. 2)
- **Helpdesk Tata Usaha (TU):** Ibu Rina (Gedung Utama Lt. 1) / WhatsApp Internal: `0812-XXXX-1001`
- **Jam Layanan:** 06:45 – 16:00 WIB (Senin – Jumat)
