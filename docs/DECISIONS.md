# EDUVIA ARCHITECTURAL DECISION RECORDS (ADR)
**Version:** 1.0.0 (Phase 0 Foundation)  
**Status:** Approved & Implemented  

---

## ADR-001: Pemilihan Stack Aplikasi (Modular Monolith Laravel + Blade + Tailwind + Alpine)

### Konteks:
Aplikasi e-learning ditargetkan untuk institusi sekolah di Indonesia dari jenjang SD hingga SMK, dengan variasi kecepatan koneksi internet dan kapasitas server lokal. Perlu diputuskan apakah menggunakan arsitektur Decoupled Single Page Application (Next.js/React + REST API) atau Server-Side Rendered (SSR) Modular Monolith.

### Keputusan:
Mengadopsi **Laravel 12 (PHP 8.3) dengan Blade Component System, Tailwind CSS v4, dan Alpine.js 3.x**.

### Alasan:
1. **Performa Jaringan di Lingkungan Sekolah:** Server-rendered HTML memiliki *First Contentful Paint (FCP)* yang jauh lebih cepat pada perangkat berdaya rendah (komputer lab sekolah atau ponsel murah siswa) dibandingkan SPA berat yang memerlukan unduhan bundel JavaScript berukuran megabyte.
2. **Keamanan Bawaan:** Proteksi CSRF otomatis, sesi aman berbasis cookies HttpOnly, dan otorisasi terpusat di server mengurangi celah keamanan sisi klien.
3. **Efisiensi Pengembangan & Pemeliharaan:** Satu basis kode tunggal mempermudah proses migrasi, backup, dan deployment oleh admin IT sekolah tanpa keharusan mengelola layanan frontend dan backend secara terpisah.

---

## ADR-002: Strategi Single-School vs Multi-School Readiness

### Konteks:
Sebagian besar sekolah menginginkan aplikasi mandiri untuk instansinya, namun di sisi lain terdapat kebutuhan yayasan pendidikan atau dinas yang menaungi banyak sekolah dalam satu platform terpusat. Perlu diputuskan strategi isolasi data tanpa menimbulkan *overengineering* pada Fase 0.

### Keputusan:
Menggunakan strategi **Tenant Readiness Hook (Shared Database with Tenant Discriminator)**:
1. Pada Fase 0, basis data dan autentikasi disiapkan secara modular dengan middleware isolasi tenant `EnsureSchoolTenant`.
2. Pada Fase 1, setiap tabel data akademik yang bersifat kepemilikan sekolah akan menyertakan kolom `school_id` yang diindeks.
3. Isolasi data diterapkan secara otomatis menggunakan **Global Scope** Eloquent (`SchoolTenantScope`), sehingga pengembang tidak perlu mengingat penambahan klausa `WHERE school_id = ?` di setiap kueri.
4. **Strategi Domain:**
   - *Opsi 1 (Satu Domain Bersama):* `app.eduvia.id` dengan pemilihan konteks sekolah melalui sesi pengguna.
   - *Opsi 2 (Subdomain per Sekolah):* `smpn1.eduvia.id`, `smkn2.eduvia.id` didukung melalui konfigurasi wildcard route tanpa perlu mengubah skema tabel.

---

## ADR-003: Model Autentikasi dan Role-Based Access Control (RBAC)

### Konteks:
Aplikasi membutuhkan 5 tingkatan peran: Super Admin, Admin Sekolah, Guru, Siswa, dan Orang Tua. Perlu diputuskan apakah memasang pustaka pihak ketiga seperti `spatie/laravel-permission` secara langsung atau menggunakan arsitektur Enum terstruktur.

### Keputusan:
Menerapkan **PHP 8.3 Backed Enums (`App\Enums\UserRole` dan `App\Enums\Permission`) dengan Middleware Otorisasi Mandiri (`EnsureUserHasRole`)**:

### Alasan:
1. **Type Safety & Zero Overhead:** Enum PHP 8.3 memberikan validasi tipe statis di level compiler tanpa melakukan puluhan query join tabel relasi dinamis di setiap request HTTP.
2. **Pemberian Izin Eksplisit:** Kapabilitas setiap peran dipetakan secara terpusat di `Permission::forRole()`.
3. **Extensibility:** Jika pada fase lanjutan sekolah membutuhkan kustomisasi izin dinamis per pengguna melalui antarmuka admin, model ini dapat dimigrasikan ke `spatie/laravel-permission` secara mulus tanpa mengubah kontrak pemanggilan `hasRole()` dan `hasPermission()`.

---

## ADR-004: Engine Basis Data (MySQL / MariaDB InnoDB)

### Konteks:
Aplikasi memerlukan konsistensi data tinggi untuk riwayat akademik, presensi, dan penilaian ujian.

### Keputusan:
Menggunakan **MySQL 8.0+ / MariaDB dengan Storage Engine InnoDB**, charset `utf8mb4`, dan collation `utf8mb4_unicode_ci`.

### Alasan:
1. **Integritas Transaksional (ACID):** Menjamin operasi multi-tabel (misal: pengumpulan ujian dan pencatatan nilai) tidak meninggalkan data korup saat terjadi interupsi.
2. **Dukungan Luas:** Tersedia di hampir seluruh penyedia hosting/VPS di Indonesia (Laragon lokal, cPanel, Cloud VPS, Docker).
