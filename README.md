# Eduvia — Platform E-Learning Sekolah Indonesia
> **Fase 0: Project Foundation & Architecture**  
> Mendukung jenjang pendidikan **SD / MI**, **SMP / MTs**, **SMA / MA**, hingga **SMK / MAK**.

Eduvia adalah aplikasi e-learning modern yang dirancang untuk institusi pendidikan di Indonesia dengan mengutamakan performa tinggi, arsitektur modular yang bersih, keamanan berlapis (*defense-in-depth*), serta antarmuka berstandar akademik yang responsif di seluruh perangkat.

---

## 1. Stack Teknologi

- **Backend:** PHP 8.5.12 & Laravel 12 (Modular Monolith)
- **Frontend & UI:** Blade Components + Tailwind CSS v4 (`@tailwindcss/vite`) + Alpine.js 3.x
- **Basis Data:** MySQL / MariaDB (InnoDB, `utf8mb4`)
- **Keamanan:** Security Headers Middleware, CSRF Token Protection, Login Rate Limiting (Throttle), Password Hashing Bcrypt (Rounds 12)
- **Kualitas Kode & Testing:** PHPUnit 12 Testing Suite + Laravel Pint Formatter

---

## 2. Prasyarat Sistem

Pastikan lingkungan server atau mesin pengembang memiliki:
- PHP >= 8.5.12 (dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `curl`)
- Composer >= 2.2
- Node.js >= 20.x & npm >= 10.x
- MySQL >= 8.0 atau MariaDB >= 10.4

---

## 3. Panduan Instalasi & Menjalankan Proyek

### Langkah 1: Kloning Repositori & Masuk ke Folder Proyek
```bash
git clone <url-repository>
cd Eduvia
```

### Langkah 2: Instalasi Ketergantungan Backend & Frontend
```bash
# Instalasi pustaka PHP
composer install

# Instalasi paket frontend
npm install
```

### Langkah 3: Konfigurasi Environment
Salin template konfigurasi dan sesuaikan kredensial basis data:
```bash
cp .env.example .env
php artisan key:generate
```
Pastikan variabel basis data pada berkas `.env` sesuai dengan MySQL Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eduvia_dev
DB_USERNAME=root
DB_PASSWORD=
```

### Langkah 4: Migrasi & Seeding Data Pengujian
```bash
# Jalankan migrasi dan isi akun uji coba awal
php artisan migrate:fresh --seed
```

### Langkah 5: Kompilasi Aset Frontend
```bash
# Untuk kompilasi produksi
npm run build

# Atau jalankan dev-server dengan hot-reload
npm run dev
```

### Langkah 6: Jalankan Server Aplikasi
```bash
php artisan serve
```
Aplikasi sekarang dapat diakses melalui peramban di: **`http://localhost:8000`**.

---

## 4. Akun Uji Coba Bawaan (Development Seed Accounts)

Semua akun pengujian di bawah ini menggunakan kata sandi: **`Password123!`**

| Peran | Alamat Email | Hak Akses |
| :--- | :--- | :--- |
| **Super Admin** | `superadmin@eduvia.test` | Akses penuh seluruh platform & sistem |
| **Admin Sekolah** | `admin.sekolah@eduvia.test` | Manajemen operasional tingkat sekolah |
| **Guru / Pendidik** | `guru@eduvia.test` | Pengelolaan materi & evaluasi kelas |
| **Siswa / Pelajar** | `siswa@eduvia.test` | Peserta pembelajaran & pengumpulan tugas |
| **Orang Tua / Wali** | `orangtua@eduvia.test` | Pemantauan kemajuan belajar siswa |

Tersedia tombol satu-klik pada halaman masuk (`/login`) untuk mengisi kredensial secara instan.

---

## 5. Menjalankan Pengujian Otomatis & Linting

```bash
# Menjalankan seluruh test suite (25 tests, 113 assertions)
composer run test
# atau
php artisan test

# Memformat dan memvalidasi gaya kode
composer run lint
# atau
vendor/bin/pint --format agent
```

---

## 6. Observability & Health Check

- **Health Check Web:** `GET /health`
- **Health Check REST API:** `GET /api/v1/health`  
  Mengembalikan status kesehatan sistem, latensi koneksi basis data, versi PHP/Laravel, dan waktu server dalam format JSON standar.
- **Living Design System:** `GET /design-system`  
  Katalog interaktif untuk memverifikasi 13+ komponen UI, modal, alerts, tombol, dan form input.

---

## 7. Dokumentasi Teknis Lengkap

Dokumentasi arsitektur dan pedoman implementasi tersedia di folder `docs/`:
1. [docs/ARCHITECTURE.md](file:///c:/Antigravity/Eduvia/docs/ARCHITECTURE.md) — Arsitektur Modular Monolith, aturan dependensi, dan kontrak API.
2. [docs/DATABASE_DESIGN.md](file:///c:/Antigravity/Eduvia/docs/DATABASE_DESIGN.md) — Konvensi basis data, disaster recovery, dan cetak biru data masa depan.
3. [docs/SECURITY.md](file:///c:/Antigravity/Eduvia/docs/SECURITY.md) — Kebijakan keamanan, defense-in-depth headers, throttle, dan sanitasi data.
4. [docs/ROLES_AND_PERMISSIONS.md](file:///c:/Antigravity/Eduvia/docs/ROLES_AND_PERMISSIONS.md) — Model RBAC, kapabilitas izin, dan middleware otorisasi backend.
5. [docs/UI_DESIGN_SYSTEM.md](file:///c:/Antigravity/Eduvia/docs/UI_DESIGN_SYSTEM.md) — Design tokens, pustaka komponen Blade, dan aturan aksesibilitas.
6. [docs/TESTING.md](file:///c:/Antigravity/Eduvia/docs/TESTING.md) — Pedoman menjalankan pengujian unit dan fitur.
7. [docs/DEVELOPMENT_ROADMAP.md](file:///c:/Antigravity/Eduvia/docs/DEVELOPMENT_ROADMAP.md) — Roadmap pengembangan dari Fase 0 hingga Fase 5.
8. [docs/DECISIONS.md](file:///c:/Antigravity/Eduvia/docs/DECISIONS.md) — Rekaman keputusan arsitektural (ADR) dan justifikasinya.
