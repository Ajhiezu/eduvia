# DRAF PERJANJIAN KERJA SAMA UJI COBA (PILOT) & PERJANJIAN PEMROSESAN DATA PRIBADI (DATA PROCESSING AGREEMENT / DPA)
## Implementasi Terkendali Platform Eduvia di SMA Eduvia Unggulan Surabaya
**Nomor Dokumen:** EDV-DPA-PILOT-2026-001  
**Dasar Hukum:** UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP) & PP No. 71 Tahun 2019 (PSTE)  
**Status:** Draf Operasional Resmi untuk Penandatanganan Pra-Pilot  

---

## BAGIAN I: PERJANJIAN KERJA SAMA UJI COBA TERKENDALI (PILOT AGREEMENT)

Perjanjian ini dibuat pada hari ini, **Senin, tanggal 19 Oktober 2026**, oleh dan antara:

1. **PT EDUVIA TEKNOLOGI NUSANTARA**, berkedudukan di Surabaya, dalam hal ini diwakili oleh Direktur Utama, bertindak untuk dan atas nama penyedia platform Eduvia (selanjutnya disebut **"Penyedia Platform / Prosesor Data"**).
2. **SMA EDUVIA UNGGULAN SURABAYA**, berkedudukan di Surabaya, dalam hal ini diwakili oleh Kepala Sekolah, bertindak untuk dan atas nama satuan pendidikan (selanjutnya disebut **"Sekolah / Pengendali Data"**).

Kedua belah pihak secara bersama-sama disebut **"Para Pihak"**.

### Pasal 1: Tujuan & Ruang Lingkup Pilot
1. Para Pihak sepakat melaksanakan uji coba terkendali platform manajemen sekolah dan e-learning Eduvia pada semester berjalan selama **8 (delapan) minggu kalender**.
2. Lingkup uji coba mencakup:
   - Gelombang 1: Administrasi master, jadwal pelajaran, dan presensi harian kelas 10.
   - Gelombang 2: Portal mobile siswa dan wali murid (pemantauan kehadiran & permohonan izin).
   - Gelombang 3: Distribusi materi pembelajaran dan penugasan terstruktur.
   - Gelombang 4: Komunikasi guru-wali murid dan publikasi nilai tugas.
   - Gelombang 5: Ujian formatif CBT terkendali di laboratorium sekolah.
   - Gelombang 6: Modul pendampingan operasional sekolah.
3. Modul berisiko tinggi (Gateway Pembayaran Otomatis & Ujian Sumatif Kelulusan) **TIDAK DIAKTIFKAN** selama masa uji coba awal demi perlindungan operasional sekolah.

### Pasal 2: Biaya & Komitmen Layanan
1. Seluruh penyediaan infrastruktur cloud, lisensi perangkat lunak, dan pendampingan teknis selama masa uji coba diberikan **tanpa biaya lisensi (Free Pilot Program)**.
2. Penyedia menjamin ketersediaan layanan (*Service Level Objective / SLO*) sebesar **99.5%** pada hari dan jam sekolah (Senin – Jumat, pukul 06:30 – 17:00 WIB).
3. Sekolah berhak menghentikan uji coba secara sepihak dan seketika (*Emergency Abort*) apabila terjadi insiden kebocoran data atau kegagalan sistem berulang yang mengganggu proses belajar mengajar.

---

## BAGIAN II: DATA PROCESSING AGREEMENT (DPA)

Berdasarkan ketentuan Undang-Undang No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP):

### Pasal 3: Kedudukan Para Pihak & Kepemilikan Data
1. **Sekolah bertindak sebagai PENGENDALI DATA PRIBADI (*Data Controller*)**, yang memegang hak kepemilikan mutlak dan menentukan dasar hukum, tujuan, serta instruksi pemrosesan data peserta didik, tenaga pendidik, dan wali murid.
2. **Penyedia Platform bertindak sebagai PROSESOR DATA PRIBADI (*Data Processor*)**, yang hanya memproses data pribadi semata-mata atas instruksi tertulis Sekolah guna penyediaan fungsi sistem Eduvia.
3. **Kepemilikan Data:** Seluruh data sekolah, data profil siswa, catatan kehadiran, rekam jejak akademik, dan komunikasi keluarga adalah milik sah Sekolah. Penyedia **dilarang keras** menjual, memindahtangankan, atau memanfaatkan data tersebut untuk tujuan periklanan, pemrofilan komersial, atau melatih model kecerdasan buatan pihak ketiga tanpa persetujuan eksplisit.

### Pasal 4: Kategori Data & Perlindungan Data Anak
1. Kategori data yang diproses meliputi data pribadi umum (nama, NISN, kelas, alamat email, nomor telepon) dan data spesifik anak di bawah umur (usia < 18 tahun).
2. Sesuai **Pasal 25 UU PDP**, pemrosesan data pribadi anak wajib mendapatkan persetujuan tertulis dari orang tua atau wali yang sah sebelum akun diaktifkan. Formulir persetujuan tercantum pada **Lampiran A**.

### Pasal 5: Keamanan Teknis & Pengendalian Akses
1. **Enkripsi:**
   - Seluruh data saat transit (*in-transit*) wajib dienkripsi menggunakan protokol **TLS 1.3**.
   - Seluruh basis data dan cadangan data saat diam (*at-rest*) wajib dienkripsi menggunakan algoritma **AES-256**.
2. **Residensi Data:**
   - Seluruh server produksi, basis data, dan media penyimpanan wajib berlokasi fisik di dalam wilayah Republik Indonesia, sesuai ketentuan PP No. 71 Tahun 2019.
3. **Akses Tim Teknis / SRE (Scrubbed Diagnostics):**
   - Akses personil pengembang terhadap data produksi dibatasi hanya untuk keperluan investigasi gangguan (*troubleshooting*), wajib diotorisasi tertulis oleh PIC Sekolah, dibatasi durasi maksimal **60 menit**, dan menggunakan tampilan data tersamar (*anonymized/PII-scrubbed*).
   - Setiap akses teknis tercatat permanen pada sistem audit trail (*immutable audit logs*).

### Pasal 6: Penanganan Insiden & Notifikasi Kebocoran Data
1. Apabila terjadi kegagalan sistem, upaya peretasan, atau insiden kebocoran data pribadi (*Data Breach*), Penyedia wajib memberitahukan secara tertulis kepada Pengendali Data dalam waktu selambat-lambatnya **3 x 24 jam (tiga kali dua puluh empat jam)** sejak diketahuinya insiden, sesuai mandat **Pasal 46 UU PDP**.
2. Pemberitahuan wajib memuat: rincian data yang terdampak, langkah penanggulangan darurat yang telah dilakukan, serta rekomendasi mitigasi risiko bagi subjek data.

### Pasal 7: Pengakhiran Pilot, Ekspor & Penghapusan Data
1. Apabila setelah masa uji coba berakhir Sekolah memutuskan untuk tidak melanjutkan penggunaan sistem:
   - Penyedia wajib menyerahkan seluruh data cadangan lengkap (*Full Database Dump*) dalam format terbuka (SQL / CSV) kepada Sekolah dalam waktu maksimal 7 (tujuh) hari kerja.
   - Setelah konfirmasi serah-terima diterima, Penyedia wajib melakukan **pemusnahan data permanen (Secure Data Wipe)** dari seluruh media penyimpanan dan server cloud dalam kurun waktu 14 (empat belas) hari kerja dan menerbitkan **Berita Acara Pemusnahan Data**.

---

## BAGIAN III: TANDA TANGAN KESEPAKATAN

Dibuat dalam rangkap 2 (dua) bermeterai cukup dan memiliki kekuatan hukum yang sama:

**Pihak Pertama (Penyedia Platform / Prosesor)**  
PT EDUVIA TEKNOLOGI NUSANTARA  

*(Meterai Rp 10.000)*  
_______________________________  
**Direktur Utama**  
Tanggal: 19 Oktober 2026  

---

**Pihak Kedua (Sekolah / Pengendali Data)**  
SMA EDUVIA UNGGULAN SURABAYA  

*(Meterai Rp 10.000)*  
_______________________________  
**Kepala Sekolah**  
Tanggal: 19 Oktober 2026  

---

## LAMPIRAN A: FORMULIR PERSETUJUAN TERTULIS ORANG TUA / WALI
### (PARENTAL CONSENT FORM — SESUAI PASAL 25 UU PDP NO. 27 TAHUN 2022)

Saya yang bertanda tangan di bawah ini:

- **Nama Lengkap Orang Tua / Wali:** __________________________________________________
- **Nomor Induk Kependudukan (NIK):** __________________________________________________
- **Nomor WhatsApp / HP:** __________________________________________________
- **Alamat Tempat Tinggal:** __________________________________________________

Sebagai Orang Tua / Wali yang sah dari peserta didik:
- **Nama Lengkap Siswa:** __________________________________________________
- **Nomor Induk Siswa Nasional (NISN):** __________________________________________________
- **Kelas / Rombongan Belajar:** __________________________________________________

Dengan ini menyatakan bahwa:
1. Saya telah menerima, membaca, dan memahami informasi mengenai penggunaan sistem digital **Eduvia** oleh SMA Eduvia Unggulan Surabaya untuk keperluan pencatatan kehadiran, pengumuman sekolah, distribusi materi belajar, dan komunikasi guru.
2. Saya memahami bahwa data pribadi putra/putri saya disimpan secara aman di server berlokasi di Indonesia dan hanya digunakan semata-mata untuk kepentingan operasional pendidikan sekolah.
3. Saya menyetujui pemrosesan data pribadi putra/putri saya dalam sistem Eduvia selama terdaftar aktif sebagai siswa di SMA Eduvia Unggulan Surabaya.

*(Pilihlah salah satu opsi di bawah ini)*:
- [  ] **SETUJU** data anak diproses dalam platform Eduvia untuk keperluan absensi & akademik.
- [  ] **TIDAK SETUJU** *(Siswa akan tetap dilayani menggunakan pencatatan presensi manual/kertas tanpa diskriminasi hak pendidikan)*.

Surabaya, ___________________ 2026  
Orang Tua / Wali Murid,  


*(Tanda Tangan & Nama Terang)*  
____________________________________  
