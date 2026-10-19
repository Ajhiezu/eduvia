# MASUKAN TEKNIS KEBIJAKAN PRIVASI EDUVIA (PRIVACY POLICY TECHNICAL INPUTS)
## Draf Materi Penyusunan Pernyataan Privasi untuk Pengguna (Siswa, Orang Tua, & Tenaga Pendidik)

> [!NOTE]
> **DISCLAIMER:**  
> Dokumen ini merupakan draf kerja teknis untuk membantu manajemen yayasan dan konsultan hukum, bukan nasihat hukum formal (*legal counsel*). Perlu ditinjau dan divalidasi oleh konsultan hukum berizin sebelum diberlakukan resmi.

**Pengendali Data:** Yayasan / Sekolah Pengguna Eduvia  
**Prosesor Data Platform:** Tim Pengembang & Penyedia Platform Eduvia  
**Bahasa:** Bahasa Indonesia Lugas, Terbuka, & Tidak Menyesatkan  
**Versi Draf:** 1.0 (Oktober 2026)  

---

## 1. KOMITMEN PRIVASI KAMI

Kami menyadari sepenuhnya bahwa data yang dikelola dalam platform **Eduvia** mencakup data anak-anak, rekam jejak akademik, dan informasi keuangan keluarga Anda. Eduvia berkomitmen mematuhi ketentuan **Undang-Undang Republik Indonesia Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP)** dengan prinsip keterbukaan, pembatasan tujuan (*purpose limitation*), dan keamanan sistem berlapis.

Kebijakan ini menjelaskan:
1. Data apa saja yang kami kumpulkan dan mengapa kami membutuhkannya.
2. Bagaimana data anak di bawah umur dilindungi secara khusus.
3. Siapa saja pihak ketiga tepercaya yang membantu pemrosesan data.
4. Apa saja hak-hak Anda atas data pribadi Anda dan bagaimana cara menggunakannya.

---

## 2. DATA APA SAJA YANG KAMI KUMPULKAN?

Kami hanya mengumpulkan data yang benar-benar dibutuhkan untuk menunjang kegiatan belajar mengajar dan tata kelola sekolah:

### A. Data Peserta Didik (Anak)
* **Identitas Pokok:** Nama lengkap, Nomor Induk Siswa (NIS), Nomor Induk Siswa Nasional (NISN), tempat dan tanggal lahir, jenis kelamin.
* **Kegiatan Belajar:** Kehadiran kelas (presensi), tugas harian, jawaban ujian CBT, nilai ulangan, dan buku rapor semester.
* **Catatan Kedisiplinan & Konseling:** Catatan presensi sakit/izin dengan lampiran surat, serta pembinaan siswa.

### B. Data Orang Tua / Wali Murid
* **Kontak & Relasi:** Nama orang tua/wali, nomor telepon/WhatsApp, alamat email, dan alamat rumah.
* **Administrasi Keuangan:** Tagihan uang sekolah (SPP), riwayat pembayaran, serta bukti transfer bank yang diunggah untuk konfirmasi pembayaran.

### C. Data Tenaga Pendidik & Staf Sekolah
* **Kepegawaian:** Nama guru/staf, NIP/NUPTK, email resmi, penugasan mata pelajaran, jadwal mengajar, dan riwayat presensi kerja.

---

## 3. BAGAIMANA DATA ANAK DILINDUNGI? (PERLINDUNGAN KHUSUS)

Sesuai Pasal 25 UU PDP, data anak (< 18 tahun) adalah data yang memerlukan perlindungan khusus:
1. **Persetujuan Orang Tua (Parental Consent):** Pendaftaran dan aktivasi akun siswa pada aplikasi mobile harus ditautkan dan disetujui oleh orang tua/wali sah melalui kode verifikasi keluarga (*Family Link Code*).
2. **Kerahasiaan Nilai Siswa:** Nilai ujian dan buku rapor anak **TIDAK PERNAH** dipublikasikan ke publik atau siswa lain. Nilai hanya dapat dilihat oleh siswa yang bersangkutan, orang tuanya, guru pengampu, wali kelas, dan kepala sekolah.
3. **Pemberdayaan Kecerdasan Buatan (AI) yang Bertanggung Jawab:** Fitur asisten AI guru (pembuat soal dan modul ajar) **TIDAK MENGGUNAKAN** nama asli siswa, NIK, atau identitas anak. Sistem Eduvia secara otomatis menyamarkan data pribadi sebelum dikirimkan ke model AI eksternal.

---

## 4. DAFTAR PIHAK KETIGA PEMROSES DATA (SUB-PROCESSORS)

Untuk menjalankan layanan sekolah digital yang andal, kami bekerja sama dengan mitra pihak ketiga yang memenuhi standar keamanan industri:

| Nama Mitra | Layanan / Peran | Jenis Data yang Diproses | Standar Keamanan Mitra |
| :--- | :--- | :--- | :--- |
| **Midtrans (PT Midtrans)** | Pemrosesan Pembayaran Online (Virtual Account & QRIS) | Nomor tagihan, nama pembayar, nominal SPP | Bersertifikat PCI-DSS Level 1 & diawasi Bank Indonesia |
| **Google Cloud (Gemini API)** | Asisten AI Penyusunan Materi & Bank Soal Guru | Naskah bahan ajar dan kisi-kisi soal (Tanpa PII Siswa) | Enkripsi data transit TLS 1.3 & Zero Human Retention |
| **Cloud Object Storage Provider** | Penyimpanan Aman Berkas Unggahan (Bukti Bayar & Tugas) | Berkas dokumen dan gambar lampiran | Enkripsi Server-Side AES-256 pada Private Storage |

*Kami **TIDAK PERNAH** menjual, menyewakan, atau membagikan data pribadi siswa dan keluarga kepada pengiklan, lembaga pinjaman online, atau pihak komersial manapun.*

---

## 5. HAK-HAK ANDA SEBAGAI SUBJEK DATA (UU PDP)

Sebagai pemilik data pribadi (atau orang tua dari siswa anak), Anda memiliki hak hukum yang dijamin oleh sistem Eduvia:

1. **Hak Akses & Portabilitas Data (Pasal 7):** Anda berhak meminta salinan seluruh data pribadi dan riwayat akademik Anda dalam format digital yang terstruktur (ekspor data JSON/PDF).
2. **Hak Perbaikan / Pemutakhiran (Pasal 6):** Anda berhak memperbarui alamat rumah, nomor kontak, atau data profil yang keliru melalui aplikasi mobile atau petugas tata usaha sekolah.
3. **Hak Penghapusan & Anonimisasi (Pasal 8):** Setelah siswa lulus atau pindah sekolah, Anda berhak mengajukan permohonan penghapusan akun. Data identitas langsung disamarkan (*anonymized*), sementara buku induk rapor disimpan sesuai amanat undang-undang kearsipan pendidikan nasional.
4. **Hak Menarik Persetujuan (Withdrawal of Consent):** Anda berhak mencabut persetujuan pemrosesan data opsional (misal notifikasi promosi kegiatan sekolah) kapan saja melalui menu pengaturan aplikasi.

---

## 6. SALURAN KOMUNIKASI PRIVASI & DATA PROTECTION OFFICER (DPO)

Jika Anda memiliki pertanyaan, keberatan, atau ingin menggunakan hak subjek data Anda, silakan hubungi unit pelindungan data kami:

* **Jabatan Kontak:** Data Protection Officer (DPO) / Tim Kepatuhan Privasi Yayasan
* **Email Resmi:** `privasi@eduvia.sch.id` (atau melalui email admin privasi sekolah masing-masing)
* **Alamat Kantor:** Kantor Pengelola Yayasan Pendidikan Eduvia
* **Waktu Respons Layanan Subjek Data:** Maksimal 3 × 24 jam kerja sejak permohonan resmi diverifikasi identitasnya.

---

## 7. PERUBAHAN KEBIJAKAN PRIVASI

Kebijakan Privasi ini dapat diperbarui sewaktu-waktu seiring penyempurnaan fitur dan peraturan perundang-undangan yang berlaku. Apabila terjadi perubahan penting, kami akan memberikan pemberitahuan resmi melalui aplikasi atau pengumuman dashboard sebelum perubahan tersebut berlaku efektif.
