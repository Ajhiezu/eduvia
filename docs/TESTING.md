# EDUVIA TESTING STRATEGY & QA PROCEDURES
**Version:** 1.0.0 (Phase 0 Foundation)  
**Test Framework:** PHPUnit 12 / Laravel Testing Suite  
**Code Formatter:** Laravel Pint  

---

## 1. Ikhtisar Strategi Pengujian Otomatis

Fondasi aplikasi Eduvia wajib melewati pengujian otomatis menyeluruh sebelum rilis atau peningkatan modul dapat diterima. Pengujian dibagi menjadi dua kategori utama:
1. **Unit Tests (`tests/Unit`):** Menguji logika domain murni (Enum mapping, ApiResponse format, helper functions) secara terisolasi tanpa efek samping.
2. **Feature Tests (`tests/Feature`):** Menguji alur HTTP terintegrasi (Autentikasi, Middleware keamanan, Otorisasi peran, Health check, Rendering Blade).

---

## 2. Struktur Berkas Pengujian Fase 0

```
tests/
|-- Feature/
|   |-- AuthenticationFoundationTest.php # Alur login, salah password, user nonaktif, logout, throttle
|   |-- DesignSystemShowcaseTest.php     # Rute /design-system dan halaman / berstatus 200
|   |-- ExampleTest.php
|   |-- HealthCheckTest.php              # Endpoint /health dan /api/v1/health berstatus 200 & schema valid
|   |-- RoleAuthorizationTest.php        # Proteksi rute berdasarkan peran (Super Admin, Admin Sekolah vs Siswa 403)
|   |-- SecurityHeadersTest.php          # Kehadiran header X-Frame-Options, X-Content-Type-Options, dll.
|-- Unit/
|   |-- ApiResponseTraitTest.php         # Kontrak format JSON ApiResponse (success, error, paginated)
|   |-- ExampleTest.php
|   |-- UserRoleTest.php                 # Enums UserRole, SchoolLevel, Permission mapping
|-- TestCase.php                         # Basis pengujian Laravel
```

---

## 3. Menjalankan Pengujian

### A. Menjalankan Seluruh Test Suite
```bash
# Menjalankan seluruh pengujian via Composer
composer run test

# Atau langsung via Artisan CLI
php artisan test
```

### B. Menjalankan Test Tertentu Saja
```bash
# Menjalankan satu file Feature Test
php artisan test tests/Feature/HealthCheckTest.php

# Menjalankan test dengan filter nama method
php artisan test --filter=test_login_request_is_throttled
```

### C. Menjalankan Linter / Formatter Kode
```bash
# Memformat dan memeriksa gaya kode menggunakan Laravel Pint
composer run lint
# atau
vendor/bin/pint --format agent
```

### D. Menguji Build Aset Frontend
```bash
npm run build
```

---

## 4. Cara Membaca Hasil Pengujian

Output pengujian standar menampilkan rekap:
```
PASS  Tests\Feature\AuthenticationFoundationTest
PASS  Tests\Feature\DesignSystemShowcaseTest
PASS  Tests\Feature\HealthCheckTest
PASS  Tests\Feature\RoleAuthorizationTest
PASS  Tests\Feature\SecurityHeadersTest
PASS  Tests\Unit\ApiResponseTraitTest
PASS  Tests\Unit\UserRoleTest

Tests:    25 passed (113 assertions)
Duration: 3.5s
```

Jika terdapat kegagalan:
1. Periksa baris error dan status kode HTTP (misal: 403 vs 401).
2. Pastikan database testing `eduvia_testing` telah termigrasi dengan `php artisan migrate --env=testing`.
3. Jalankan `php artisan config:clear` jika terjadi konflik konfigurasi cache.
