# EDUVIA SAAS PLATFORM — PII INVENTORY & DATA PRIVACY COMPLIANCE
## Dokumentasi Kepatuhan Undang-Undang Pelindungan Data Pribadi (UU No. 27 Tahun 2022)

**Status:** Resmi (Phase 1 Baseline)  
**Terakhir Diperbarui:** 18 Oktober 2026  
**Klasifikasi:** Internal Yayasan & Administrator Sekolah  

---

## 1. LATAR BELAKANG & PRINSIP DASAR

Eduvia adalah platform SaaS tata kelola pendidikan terintegrasi yang memproses data pribadi anak (peserta didik), orang tua/wali murid, dan tenaga pendidik. Berdasarkan **UU RI No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP)**:
1. **Pasal 4 Ayat (2) Huruf b & Pasal 25:** Data anak dikategorikan sebagai **Data Pribadi yang Bersifat Spesifik**, sehingga pemrosesannya wajib mendapatkan persetujuan eksplisit dari orang tua dan/atau wali anak.
2. **Prinsip Akuntabilitas:** Pengendali data (sekolah & yayasan) serta prosesor data (platform Eduvia) wajib mencatat riwayat persetujuan (*consent*), menerapkan pengamanan teknis (enkripsi & kontrol akses granular), dan memfasilitasi hak subjek data.

---

## 2. MATRIKS INVENTARISASI DATA PRIBADI (PII INVENTORY)

| Kategori Data | Elemen Data Spesifik | Subjek Data | Klasifikasi UU PDP | Lokasi Penyimpanan (Tabel/Field) | Tujuan Pemrosesan |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Identitas Pokok Siswa** | Nama lengkap, NIS, NISN, NIK, Tempat/Tgl Lahir, Jenis Kelamin, Agama | Siswa | Spesifik (Anak) + Umum | `users.name`, `student_profiles.*`, `admission_applicants.*` | Administrasi sekolah, validasi Dapodik Kemendikbudristek |
| **Kontak Siswa** | Email, No. HP, Alamat Tempat Tinggal | Siswa | Umum | `users.email`, `users.phone`, `student_profiles.address` | Komunikasi akademik, notifikasi tugas |
| **Identitas Orang Tua / Wali** | Nama Ayah/Ibu/Wali, NIK, No. HP/WhatsApp, Pekerjaan, Alamat | Orang Tua / Wali | Umum | `student_profiles.parent_*`, `parent_student_relations.*`, `admission_applicants.parent_*` | Komunikasi perkembangan anak, persetujuan hukum, penagihan SPP |
| **Data Akademik & Penilaian** | Nilai Tugas, Nilai Ujian CBT, Rapor Semester, Catatan Sikap | Siswa | Spesifik (Perkembangan Anak) | `student_report_cards.*`, `subject_gradebooks.*`, `exam_attempts.*` | Evaluasi pembelajaran, penerbitan buku induk & ijazah |
| **Data Kehadiran / Presensi** | Kehadiran harian, status sakit (surat dokter), izin, alpha | Siswa | Spesifik (Kesehatan Ringan) | `attendance_records.*`, `attendance_sessions.*` | Pemantauan kedisiplinan dan keselamatan siswa di sekolah |
| **Data Keuangan Keluarga** | Tagihan SPP, Riwayat Pembayaran, Bukti Transfer Bank, Potongan Beasiswa | Orang Tua & Siswa | Spesifik (Finansial) | `invoices.*`, `payments.*`, `student_discounts.*`, `receipts.*` | Akuntansi sekolah, pertanggungjawaban dana yayasan |
| **Data Bimbingan Konseling** | Catatan kasus konseling, riwayat pembinaan psikologis | Siswa | Spesifik (Sangat Rahasia) | `counseling_notes.*` *(Roadmap)* | Pendampingan psikososial peserta didik |
| **Identitas Guru & Staf** | Nama, NIP/NUPTK, NIK, No. HP, Email, Riwayat Penugasan Mengajar | Guru & Staf | Umum | `users.*`, `staff_profiles.*`, `teaching_assignments.*` | Manajemen kepegawaian, verifikasi pengajaran |

---

## 3. DASAR HUKUM PEMROSESAN (LEGAL BASIS)

1. **Persetujuan Eksplisit Orang Tua / Wali (UU PDP Pasal 20 & 25):**
   - Setiap siswa di bawah usia 18 tahun didaftarkan dengan persetujuan orang tua/wali sah yang tercatat di tabel `user_consents`.
2. **Kewajiban Hukum Pendidikan (Legal Obligation):**
   - Pemrosesan data pokok peserta didik dan penerbitan rapor diwajibkan oleh UU No. 20 Tahun 2003 tentang Sistem Pendidikan Nasional dan integrasi Dapodik.
3. **Kewajiban Hukum Perpajakan & Keuangan (UU KUP):**
   - Bukti pembayaran dan pembukuan keuangan wajib disimpan untuk audit yayasan dan perpajakan sekolah.

---

## 4. JADWAL MASA RETENSI DATA (DATA RETENTION POLICY)

> [!IMPORTANT]
> **ASUMSI HUKUM & OPERASIONAL:**  
> Periode retensi berikut diselaraskan dengan standar regulasi kearsipan pendidikan Indonesia (UU No. 43 Tahun 2009 tentang Kearsipan dan Permendikbudristek terkait Dokumen Kelulusan):

| Kategori Data | Periode Retensi Minimum | Tindakan Pasca-Retensi | Justifikasi Regulasi |
| :--- | :--- | :--- | :--- |
| **Buku Induk & Arsip Rapor Final** | **Permanen / Minimum 30 Tahun** | Pengarsipan statis terenkripsi | Verifikasi ijazah dan legalisir alumni |
| **Dokumen Keuangan & SPP** | **10 Tahun** | Penghapusan berkas lampiran, anonimisasi agregat | UU Ketentuan Umum Perpajakan (KUP) |
| **Log Presensi Harian Siswa** | **5 Tahun** setelah kelulusan rombel | Agregasi statistik, pembersihan log harian | Akreditasi sekolah & audit kehadiran |
| **Catatan Konseling / BK** | **5 Tahun** setelah siswa lulus | Pemusnahan permanen (*secure shredding*) | Perlindungan privasi psikologis alumni |
| **Berkas Calon Siswa (PPDB Ditolak)** | **Maksimal 1 Tahun** setelah tahun ajaran berjalan | Penghapusan dokumen fisik & digital | UU PDP (Prinsip minimisasi data) |
| **Audit Log Keamanan Sistem** | **3 Tahun** | Pengarsipan terkompresi / rotasi log | Forensik keamanan & kepatuhan siber |

---

## 5. HAK SUBJEK DATA (DATA SUBJECT RIGHTS) DALAM SISTEM

Platform Eduvia mengimplementasikan service `App\Services\DataSubjectRightsService` untuk melayani:
1. **Hak Akses & Portabilitas Data (UU PDP Pasal 7):**
   - Fungsi `exportUserData(User $user)` mengekspor seluruh profil, riwayat akademik, dan status persetujuan dalam format terstruktur JSON/PDF.
2. **Hak Penghapusan & Anonimisasi (UU PDP Pasal 8):**
   - Fungsi `anonymizeUserData(User $user, User $operator)` menyamarkan data pribadi (Nama disamarkan, NIK & Kontak dihapus, Email diganti identifier anonim).
   - **Perlindungan Integritas Yayasan:** Data transaksi keuangan dan nilai akademik historis tetap dipertahankan dalam status anonim untuk mencegah rusaknya pembukuan kas sekolah dan riwayat kelas.
3. **Pencatatan Persetujuan (Consent Registry):**
   - Seluruh status persetujuan (*granted*, *revoked*, waktu, IP, parental consent link) dicatat secara terpusat pada tabel `user_consents`.
