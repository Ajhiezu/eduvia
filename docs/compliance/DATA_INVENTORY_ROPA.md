# EDUVIA TECHNICAL RECORD OF PROCESSING ACTIVITIES (ROPA)
## Inventarisasi & Pemetaan Pemrosesan Data Pribadi (UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi)

> [!NOTE]
> **DISCLAIMER:**  
> Dokumen ini merupakan draf kerja teknis untuk membantu manajemen yayasan dan konsultan hukum, bukan nasihat hukum formal (*legal counsel*). Perlu ditinjau dan divalidasi oleh konsultan hukum berizin sebelum diberlakukan resmi.

**Peran Platform:** Prosesor Data Pribadi (*Data Processor*) untuk Yayasan / Sekolah selaku Pengendali Data Pribadi (*Data Controller*).  
**Tanggal Penyusunan:** 19 Oktober 2026  
**Versi:** 1.0 (Audit Kesiapan Pra-Peluncuran Fase 5G)  

---

## 1. TABEL INVENTARISASI DATA PRIBADI (TECHNICAL DATA INVENTORY)

| Nama Kolom / Field | Kategori Data (UU PDP) | Subjek Data | Tujuan Pemrosesan Teknis | Dasar Hukum (Legal Basis) | Lokasi Penyimpanan Basis Data | Masa Retensi | Pihak Ketiga Penerima |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `users.name` | Data Spesifik (Anak) / Umum (Dewasa) | Siswa, Guru, Ortu, Staf | Identifikasi akun, daftar kelas, rapor, login. | Persetujuan Ortu (Pasal 25) & Kewajiban Hukum Pendidikan | MariaDB `users` | Selama menjadi siswa + retensi rapor 30 tahun | Cloud Hosting, Email Gateway |
| `users.email` | Data Umum | Semua Pengguna | Kredensial login, notifikasi sistem, reset kata sandi. | Perjanjian Layanan / Kontrak | MariaDB `users` | Selama akun aktif | Mailgun / SES (Penyedia Email) |
| `users.phone` | Data Umum | Siswa, Guru, Ortu | Otentikasi SMS/WA, kontak darurat sekolah. | Persetujuan Subjek Data | MariaDB `users` | Selama akun aktif | Gateway SMS/WA (jika terhubung) |
| `student_profiles.nis` | Data Umum | Siswa | Penomoran induk administrasi internal sekolah. | Kewajiban Regulasi Pendidikan | MariaDB `student_profiles` | Permanen (Arsip Induk) | Kemendikbudristek (Dapodik) |
| `student_profiles.nisn` | Data Spesifik (Identitas Nasional Anak) | Siswa | Penomoran nasional tunggal siswa Indonesia. | Kewajiban Hukum Pendidikan (Dapodik) | MariaDB `student_profiles` | Permanen (Buku Induk) | Kemendikbudristek |
| `student_profiles.gender` | Data Umum | Siswa | Pengelompokan asrama/kelas dan pelaporan resmi. | Kewajiban Regulasi | MariaDB `student_profiles` | Permanen | Dapodik |
| `student_profiles.birth_date` | Data Spesifik (Usia Anak) | Siswa | Verifikasi batas usia pendaftaran (PPDB). | Persetujuan Ortu & Regulasi PPDB | MariaDB `student_profiles` | Permanen | Dapodik |
| `student_profiles.address` | Data Umum | Siswa & Ortu | Penentuan zonasi PPDB, pengiriman surat resmi. | Persetujuan Subjek Data | MariaDB `student_profiles` | Selama bersekolah | Jasa Ekspedisi (bila ada) |
| `parent_student_relations.*` | Data Spesifik (Hubungan Keluarga) | Orang Tua & Siswa | Otorisasi hak asuh, akses tagihan, klaim kode anak. | Persetujuan Ortu (Parental Consent) | MariaDB `parent_student_relations` | Selama bersekolah | Tidak ada |
| `attendance_records.status` | Data Spesifik (Kesehatan Ringan) | Siswa | Pencatatan sakit/izin/hadir, monitoring disiplin. | Kepentingan Vital & Regulasi | MariaDB `attendance_records` | 5 Tahun pasca kelulusan | Tidak ada |
| `invoices.*` & `payments.*` | Data Spesifik (Keuangan Keluarga) | Orang Tua / Siswa | Penagihan SPP, audit keuangan yayasan, pelaporan BOS. | Kontrak Layanan & UU KUP (Perpajakan) | MariaDB `invoices`, `payments` | 10 Tahun (Standar Akuntansi) | Midtrans (Payment Gateway) |
| `payments.proof_path` | Data Spesifik (Bukti Finansial) | Orang Tua | Verifikasi pembayaran manual via transfer bank. | Kontrak Layanan | Object Storage (`payment-proofs/`) | 5 Tahun | Tidak ada |
| `exam_answers.*` | Data Akademik | Siswa | Penilaian CBT, evaluasi kompetensi mata pelajaran. | Kontrak Pendidikan | MariaDB `exam_answers` | 5 Tahun | Tidak ada |
| `student_report_cards.*` | Data Akademik (Rapor) | Siswa | Evaluasi semesteran, penentuan kenaikan/kelulusan. | Kewajiban Hukum Pendidikan | MariaDB `student_report_cards` | Permanen / 30 Tahun | Kemendikbudristek |
| `ai_generation_drafts.*` | Konteks Bahan Belajar | Guru | Bantuan pembuatan soal & modul ajar AI. | Kepentingan Sah Guru | MariaDB `ai_generation_drafts` | 1 Tahun | Google Gemini API (setelah scrubbed) |

---

## 2. IDENTIFIKASI DATA ANAK (< 18 TAHUN) & PARENTAL CONSENT

### A. Klasifikasi Hukum Data Anak
Berdasarkan **Pasal 4 Ayat (2) Huruf b UU PDP**, data anak diklasifikasikan sebagai **Data Pribadi yang Bersifat Spesifik**. Pemrosesan data pribadi anak wajib diselenggarakan secara khusus dengan persetujuan dari orang tua atau wali yang sah (Pasal 25).

### B. Mekanisme Persetujuan Orang Tua (*Parental Consent Flow*):
1. **Pendaftaran Akun Siswa Mandiri:**
   - Siswa tidak dapat mengklaim hak akses penuh pada portal mobile tanpa tautan verifikasi dari orang tua (`parent_student_relations.is_verified = true`).
2. **Consent Registry Terpusat (`user_consents`):**
   - Saat orang tua menghubungkan akun anak melalui *claim code* pada aplikasi `Mobile-parent`, sistem mencatat:
     * `user_id`: ID Siswa.
     * `parent_user_id`: ID Orang Tua yang memberikan persetujuan.
     * `consent_type`: `child_data_processing` dan `ai_data_processing`.
     * `version`: Versi dokumen kebijakan privasi saat persetujuan diberikan (e.g. `1.0`).
     * `ip_address` & `user_agent`: Bukti jejak digital penandatanganan elektronik persetujuan.
3. **Penyaringan PII Sebelum Pemrosesan Eksternal (AI Shield):**
   - Layanan `PiiScrubber` memindai dan menghapus NISN, NIK, dan nama siswa sebelum permintaan diteruskan ke Google Gemini API.

---

## 3. IDENTIFIKASI PIHAK KETIGA PEMROSES SUB-DATA (SUB-PROCESSORS)

| Nama Pihak Ketiga | Layanan yang Disediakan | Kategori Data yang Diterima | Lokasi Server / Yurisdiksi | Mekanisme Pengamanan Data |
| :--- | :--- | :--- | :--- | :--- |
| **Midtrans (PT Midtrans)** | Payment Gateway (VA, QRIS, Kartu) | Order ID, Nama Pembayar, Nominal Tagihan, Email | Indonesia (Jakarta) | Enkripsi TLS 1.3, PCI-DSS Level 1, Tokenization |
| **Google Gemini API** | AI Large Language Model (Modul Ajar) | Teks Prompt Soal & Modul Belajar Guru | Global (USA / Singapura) | Teks di-scrubbing PII (tanpa NIK/NISN), data transit TLS 1.3 |
| **Cloud Object Storage (S3 / MinIO)** | Penyimpanan Bukti Bayar & Tugas | Berkas Gambar PDF/JPG Bukti Transfer | Indonesia / Asia Tenggara | Server-Side Encryption (AES-256), Private Buckets |
