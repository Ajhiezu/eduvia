# EDUVIA MOBILE ECOSYSTEM — SYSTEM ARCHITECTURE
**Document ID:** `EDV-MOB-ARCH-001`  
**Version:** 1.0.0 (Tahap 0 — Architecture & Audit Baseline)  
**Target Systems:** Eduvia Parent App & Eduvia Student App  
**Status:** Approved Architectural Blueprint  

---

## 1. Ringkasan Eksekutif & Filosofi Arsitektur

Eduvia adalah platform terintegrasi untuk manajemen sekolah dan pembelajaran digital jenjang SD/MI, SMP/MTs, SMA/MA, dan SMK/MAK. Ekosistem mobile Eduvia terdiri dari dua aplikasi klien terpisah:
1. **Eduvia Parent**: Aplikasi untuk orang tua/wali murid dengan fokus pada pemantauan kehadiran, jadwal, pengumuman, nilai/rapor resmi, komunikasi dengan guru, dan tagihan/pembayaran sekolah.
2. **Eduvia Student**: Aplikasi untuk siswa dengan fokus pada dasbor belajar adaptif, jadwal pelajaran harian, materi pembelajaran digital, pengumpulan tugas, dan Computer-Based Testing (CBT) mobile terintegrasi.

### Prinsip Utama (Non-Negotiable Architecture Invariants)
1. **Single Source of Truth (SSOT):** Seluruh data, aturan bisnis, dan perhitungan akademik berpusat pada satu backend Laravel 12 (`C:\Antigravity\Eduvia`) dan satu basis data MySQL. Tidak ada basis data bayangan, server duplikat, atau replikasi logika bisnis di sisi klien.
2. **Zero Breaking Changes on Eduvia Web:** Aplikasi web Eduvia tetap berfungsi tanpa gangguan. Penambahan API mobile memperkaya fungsionalitas tanpa merusak controller web, routing sesi, atau skema basis data eksisting.
3. **No Direct Database Access:** Klien mobile berkomunikasi secara eksklusif melalui secure HTTPS REST API dengan format standar `ApiResponse`.
4. **Strict Server-Side Authorization & Multi-Tenancy:** Akses dibatasi secara ketat berdasarkan `school_id` dan relasi resmi pengguna (misal: verifikasi hubungan orang tua dan anak via tabel `parent_student_relations` dengan `status = 'active'`). Tidak ada otorisasi berbasis heuristik (seperti kesamaan nama, email, atau nomor telepon).
5. **CBT & Academic Integrity:** Klien mobile tidak memiliki otoritas atas waktu ujian, penilaian, atau status kelulusan. Waktu pengerjaan, autosave, kedaluwarsa, dan pembekuan soal dikendalikan 100% oleh server.

---

## 2. Diagram Topologi Sistem

```mermaid
graph TD
    subgraph Client Layer
        ParentApp["📱 Eduvia Parent App<br/>(React Native / Expo)"]
        StudentApp["📱 Eduvia Student App<br/>(React Native / Expo)"]
        SharedPkg["📦 packages/shared<br/>(UI Tokens, API Client, Types)"]
        ParentApp -.-> SharedPkg
        StudentApp -.-> SharedPkg
    end

    subgraph API Gateway & Security Layer
        HTTPS["🔒 TLS 1.3 / HTTPS"]
        Sanctum["🛡️ Laravel Sanctum<br/>Bearer Token Auth & Abilities"]
        RateLimiter["⏱️ Rate Limiter & IP Throttling"]
        TenantScope["🏢 Tenant Isolation Guard<br/>(school_id enforcement)"]
    end

    subgraph Backend Core (Laravel 12)
        AuthCtrl["Auth & Profile Controllers"]
        ParentServ["ParentPortalService"]
        StudentCtrl["Student Learning & CBT Engine"]
        FinanceServ["SchoolFinanceService"]
        CommCtrl["Teacher-Parent Messaging"]
    end

    subgraph Storage & Infrastructure
        MySQL[("🗄️ MySQL 8.0 Primary DB")]
        SecureFiles["📁 Secure Storage<br/>(Private PDF Reports, Receipts, Materials)"]
    end

    ParentApp -->|REST API Request| HTTPS
    StudentApp -->|REST API Request| HTTPS
    HTTPS --> Sanctum
    Sanctum --> RateLimiter
    RateLimiter --> TenantScope
    TenantScope --> AuthCtrl
    TenantScope --> ParentServ
    TenantScope --> StudentCtrl
    TenantScope --> FinanceServ
    TenantScope --> CommCtrl

    AuthCtrl --> MySQL
    ParentServ --> MySQL
    StudentCtrl --> MySQL
    FinanceServ --> MySQL
    CommCtrl --> MySQL
    StudentCtrl -.-> SecureFiles
    FinanceServ -.-> SecureFiles
```

---

## 3. Keputusan Teknologi Mobile (Tech Stack)

### Evaluasi Framework Klien
| Kriteria | React Native + Expo (SDK 52) | Flutter | Native (Kotlin/Swift) |
| :--- | :--- | :--- | :--- |
| **Kesesuaian Tim Web & JS Ecosystem** | **Sangat Tinggi** (TypeScript, tooling Node/Vite serupa web) | Sedang (Perlu adaptasi bahasa Dart) | Rendah (Dua basis kode berbeda) |
| **Monorepo & Code Sharing** | **Sangat Baik** (Shared TypeScript interfaces, hooks, styling tokens) | Baik (Melalui pub package) | Sulit (Dua ekosistem berbeda) |
| **Dukungan Secure Storage** | `expo-secure-store` (Hardware Keychain / Keystore) | `flutter_secure_storage` | Keystore / Keychain native |
| **Aksesibilitas & UI Performance** | Reaktif, 60-120fps Hermes Engine | Reaktif, Skia/Impeller | Native |
| **OTA Updates & Distribusi** | Expo EAS Updates & internal testing | Sulit tanpa third-party | Terbatas |

**Keputusan:**  
Mengadopsi **React Native dengan Expo (SDK 52, TypeScript)** dalam struktur monorepo terpadu:
- `apps/parent`: Aplikasi khusus orang tua/wali murid.
- `apps/student`: Aplikasi khusus siswa (UI adaptif untuk SD, SMP, SMA, SMK).
- `packages/shared`: Shared design system (Eduvia Brand Tokens), API Client (Axios + SecureStore interceptors), Types/Interfaces, dan komponen UI murni.

---

## 4. Mekanisme Keamanan, Autentikasi, dan Multi-Tenancy

### A. Autentikasi API (Laravel Sanctum)
- Mobile klien menggunakan autentikasi berbasis Bearer Token yang diterbitkan melalui `POST /api/v1/auth/login`.
- Token diikat dengan masa berlaku, token abilities berdasarkan peran (`role:parent` atau `role:student`), dan informasi perangkat.
- Pada saat logout (`POST /api/v1/auth/logout`), token yang aktif langsung dicabut (*revoked*) dari tabel `personal_access_tokens`.
- Penyimpanan di sisi klien:
  - Token autentikasi disimpan menggunakan `expo-secure-store` (terenkripsi hardware).
  - Dilarang menyimpan token atau kredensial pengguna di unencrypted `AsyncStorage`.
  - Pada saat logout atau 401 Unauthorized, secure store dibersihkan total.

### B. Otorisasi Ketat & Tenant Scoping
- Setiap request API diverifikasi terhadap tenant `school_id` pengguna yang sedang login.
- Pengguna dengan role `parent` hanya dapat mengakses data anak yang telah terverifikasi di tabel `parent_student_relations` dengan `status = 'active'`.
- Setiap endpoint yang menerima parameter `{childId}` memvalidasi kepemilikan melalui method `ParentPortalService::getActiveChildren($parent)` untuk mencegah serangan Insecure Direct Object References (IDOR).
- Siswa hanya dapat melihat materi, jadwal, tugas, dan ujian dari rombongan belajar (kelas) aktif tempat siswa terdaftar (`ClassroomStudent` dengan status `active`).

### C. Keamanan File dan Dokumen
- Dokumen penting seperti E-Rapor digital, berkas materi tertutup, bukti pembayaran, dan kuitansi tidak boleh diekspos melalui URL statis publik tanpa proteksi.
- Klien mobile mengakses dokumen melalui endpoint otentikasi streaming atau Temporary Signed URLs yang kedaluwarsa dalam 15 menit.

---

## 5. Model CBT Mobile Terintegrasi (Exam Engine Integrity)

Ujian online (CBT) di aplikasi Eduvia Student mengadopsi protokol integritas tinggi:
1. **Server Authoritative Time:** Countdown ujian dihitung dari selisih waktu server `expires_at` pada model `ExamAttempt`. Klien tidak dapat memanipulasi waktu ujian lokal.
2. **Frozen Snapshot Question Delivery:** Soal ujian disajikan dari `questions_snapshot` yang telah dibekukan saat attempt dimulai, tanpa menyertakan kunci jawaban (*answer keys*) atau rubrik penilaian guru.
3. **Idempotent Answer Autosaving:** Pengiriman jawaban per soal menggunakan endpoint `POST /api/v1/student/exams/{id}/attempts/{attemptId}/save-answer` yang menjalankan `ExamAnswer::updateOrCreate`. Kegagalan jaringan atau pengiriman ulang tidak akan merusak data jawaban sebelumnya.
4. **State Recovery:** Apabila aplikasi mobile tertutup atau koneksi internet terputus, siswa dapat melanjutkan attempt yang sedang aktif (`STATUS_IN_PROGRESS`) selama `now() < expires_at`.
5. **Server Auto-Finalization:** Jika waktu kedaluwarsa tercapai saat jawaban dikirim, server otomatis mengubah status attempt menjadi `STATUS_SUBMITTED` / `STATUS_EXPIRED` dan menolak perubahan jawaban lebih lanjut.

---

## 6. Penanganan State & Offline Resilience

1. **Server State Management:** Menggunakan `@tanstack/react-query` untuk caching data server, background refetching, deduping request, dan error handling deklaratif.
2. **Offline Policy:**
   - Fitur baca (jadwal, riwayat kehadiran, materi yang telah diunduh) dapat dibuka secara offline dari cache lokal dengan indikator jelas: *"Menampilkan data tersimpan per [Waktu]"*.
   - Aksi mutasi penting (**pengerjaan CBT, pengumpulan tugas, konfirmasi pembayaran, pengiriman pesan**) **DILARANG** dilakukan secara offline tanpa konfirmasi langsung dari server.
3. **Error Boundaries & Feedback:**
   - Setiap layar memiliki 4 status wajib: Loading (Shimmer Skeleton), Error (Retry Button & user-friendly message), Empty State (edukatif dan actionable), serta Success State.
