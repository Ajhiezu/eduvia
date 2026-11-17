# Eduvia — Platform E-Learning Sekolah Indonesia
> **Platform Akademik dan Manajemen Sekolah - Validasi Operasional**
> Mendukung jenjang pendidikan **SD / MI**, **SMP / MTs**, **SMA / MA**, hingga **SMK / MAK**.

Eduvia adalah aplikasi e-learning modern yang dirancang untuk institusi pendidikan di Indonesia dengan mengutamakan performa tinggi, arsitektur modular yang bersih, keamanan berlapis (*defense-in-depth*), serta antarmuka berstandar akademik yang responsif di seluruh perangkat.

---

## 1. Stack Teknologi

- **Backend:** PHP 8.5.12 & Laravel 13 (Modular Monolith)
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

Kredensial di atas hanya untuk lingkungan pengembangan. Dataset multi-jenjang mengikuti `docs/DATA_DUMMY_ALL_LEVELS_MEMORY.md` dan memakai password `password123`.

---

## 5. Menjalankan Pengujian Otomatis & Linting

```bash
# Menjalankan seluruh test suite (jumlah pengujian mengikuti hasil eksekusi terkini)
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
1. [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) — Arsitektur Modular Monolith, aturan dependensi, dan kontrak API.
2. [docs/DATABASE_DESIGN.md](docs/DATABASE_DESIGN.md) — Konvensi basis data, disaster recovery, dan cetak biru data masa depan.
3. [docs/SECURITY.md](docs/SECURITY.md) — Kebijakan keamanan, defense-in-depth headers, throttle, dan sanitasi data.
4. [docs/ROLES_AND_PERMISSIONS.md](docs/ROLES_AND_PERMISSIONS.md) — Model RBAC, kapabilitas izin, dan middleware otorisasi backend.
5. [docs/UI_DESIGN_SYSTEM.md](docs/UI_DESIGN_SYSTEM.md) — Design tokens, pustaka komponen Blade, dan aturan aksesibilitas.
6. [docs/TESTING.md](docs/TESTING.md) — Pedoman menjalankan pengujian unit dan fitur.
7. [docs/DEVELOPMENT_ROADMAP.md](docs/DEVELOPMENT_ROADMAP.md) — Roadmap pengembangan dari Fase 0 hingga Fase 5.
8. [docs/DECISIONS.md](docs/DECISIONS.md) — Rekaman keputusan arsitektural (ADR) dan justifikasinya.

## Validasi integrasi dan keamanan akun

- Buka menu **Keamanan Akun** untuk mengaktifkan autentikator, mengonfirmasi kode, dan menyimpan kode pemulihan sekali pakai. Login web dan API akun yang sudah aktif 2FA memerlukan `two_factor_code` bersama email dan password. Aktivasi/nonaktivasi mencabut token mobile; perangkat perlu login ulang.
- Midtrans: isi `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, dan `MIDTRANS_IS_PRODUCTION`. Produksi menolak kredensial kosong/simulasi dan mode sandbox. Gangguan jaringan tidak menghasilkan instruksi pembayaran sintetis. Perubahan metode/tagihan ditolak selama instruksi lama masih aktif untuk mencegah dua instruksi gateway yang dapat dibayar.
- Firebase HTTP v1: isi `FCM_PROJECT_ID`, `FCM_CLIENT_EMAIL`, dan `FCM_PRIVATE_KEY` dari service account yang berwenang mengirim pesan. Private key dapat memakai newline atau escape `\n`. Token OAuth disimpan sementara di cache; jangan membagikan atau commit kredensial.
- WhatsApp: isi `WHATSAPP_ENDPOINT` (endpoint Messages HTTPS penyedia yang kompatibel dengan Meta Cloud API) dan `WHATSAPP_TOKEN`. Pengiriman memerlukan opt-in dan template `eduvia_general_notice` berbahasa Indonesia yang disetujui penyedia.
- `NOTIFICATION_SIMULATE=true` hanya berlaku di luar produksi. Log membedakan `simulated`, `configuration_error`, `failed`, dan `accepted`. Status `accepted` berarti penyedia menerima pesan, bukan bukti perangkat sudah menerimanya. Mailer `log`/`array` juga hanya simulasi.
- Pemeriksaan pilot gelombang 2 memerlukan persetujuan `child_data_processing` aktif dari wali yang terhubung untuk setiap siswa aktif. Gelombang 6 memeriksa alokasi kuota AI sekolah yang dipilih.

## Uji beban CBT dan batas bukti kesiapan

`tests/Feature/Performance/LoadSimulationBenchmarkTest.php` mengukur request aplikasi secara berurutan; hasilnya tidak membuktikan 600 peserta serentak. Jalankan alat HTTP serentak pada staging terisolasi:

```bash
node scripts/cbt-load-test.mjs --help
```

Gunakan token siswa pengujian dengan ability `role:student`, masing-masing attempt berbeda dan ujian aktif. File peserta berisi token rahasia; simpan di lokasi privat di luar repository. Alat mengirim jawaban nyata ke target staging yang Anda tentukan, lalu melaporkan p50/p95/p99, error rate, dan hasil ambang kelulusan. Atur concurrency menjadi 600 hanya setelah kapasitas staging dan data peserta tersedia.

Source aplikasi mobile tidak tersedia di workspace ini. Dokumen mobile adalah acuan/rencana; kesiapan APK, Flutter/Expo, distribusi, serta perilaku offline perlu diverifikasi terhadap repository dan build klien yang sebenarnya. Laporan pilot dan performa historis tidak menggantikan hasil staging/produksi yang dapat ditelusuri ke versi kode, konfigurasi, serta waktu pengujian.
