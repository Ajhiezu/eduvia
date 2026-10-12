# EDUVIA PLATFORM ARCHITECTURE SPECIFICATION
**Version:** 1.0.0 (Phase 0 Foundation)  
**Target:** Indonesian K-12 Educational Institutions (SD, SMP, SMA, SMK)  
**Architectural Paradigm:** Clean Modular Monolith with Layered Domain Separation  

---

## 1. Executive Architectural Overview

Eduvia dirancang sebagai platform e-learning terpadu yang melayani institusi pendidikan di Indonesia dari jenjang Sekolah Dasar (SD/MI), Sekolah Menengah Pertama (SMP/MTs), Sekolah Menengah Atas (SMA/MA), hingga Sekolah Menengah Kejuruan (SMK/MAK).

Platform ini mengadopsi pola **Modular Monolith** berbasis Laravel 12 & PHP 8.3 dengan pemisahan tegas antarlapisan (*Separation of Concerns*). Pola arsitektur ini dipilih untuk menghindari jebakan *overengineering* (seperti microservices prematur atau decoupled SPA yang membebani latensi koneksi internet sekolah di Indonesia), sambil tetap menyediakan isolasi domain yang rapi sehingga setiap modul dapat dikembangkan dan diuji secara independen.

---

## 2. Layered Architecture & Dependency Rules

Eduvia membagi aplikasi menjadi 4 lapisan utama dengan aturan ketergantungan searah (*Unidirectional Dependency Flow*):

```
+-------------------------------------------------------------+
| 1. PRESENTATION LAYER (Blade Components, Controllers, UI)    |
|    - Blade View Components (<x-button>, <x-modal>, etc.)     |
|    - HTTP Controllers (Auth, HealthCheck, Feature)          |
|    - Form Requests & Input Sanitization                     |
+-------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------+
| 2. APPLICATION & SECURITY LAYER (Middleware, Policies)       |
|    - SecurityHeadersMiddleware (Defense-in-depth)           |
|    - EnsureUserHasRole (RBAC route enforcement)             |
|    - EnsureSchoolTenant (Multi-school boundary hook)        |
|    - Rate Limiting (Throttle on login & sensitive API)      |
+-------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------+
| 3. DOMAIN & SERVICE LAYER (Business Logic & Contracts)       |
|    - Domain Enums (UserRole, SchoolLevel, Permission)       |
|    - Standard API Response Handler (ApiResponse Trait)      |
|    - Business Rules & Workflow Orchestration (Future Phases)|
+-------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------+
| 4. INFRASTRUCTURE & DATA ACCESS LAYER (Persistence)          |
|    - Eloquent Models (User, etc.)                           |
|    - Migrations, Database Indexes & Foreign Key Constraints |
|    - Cache Drivers, Session Stores & Log Sinks               |
+-------------------------------------------------------------+
```

### Dependency Rules:
1. **Aturan Searah:** Lapisan atas boleh memanggil lapisan di bawahnya, namun lapisan bawah tidak boleh memiliki ketergantungan langsung ke lapisan di atasnya.
2. **Presentation -> Domain/Infrastructure:** Controller bertanggung jawab menerima HTTP Request, mendelegasikan validasi ke `FormRequest`, memanggil logika domain, dan mengembalikan HTTP Response (atau Blade View).
3. **Penyembunyian Implementasi Data:** Business logic tidak boleh menulis kueri SQL mentah di dalam controller atau view.

---

## 3. Directory Structure Layout

```
C:\Antigravity\Eduvia\
|-- app/
|   |-- Enums/                       # Domain Value Objects & Enums (PHP 8.3)
|   |   |-- UserRole.php             # Peran Pengguna (Super Admin, Guru, Siswa, dll)
|   |   |-- SchoolLevel.php          # Jenjang Pendidikan (SD, SMP, SMA, SMK)
|   |   |-- Permission.php           # Kemampuan Izin Granular
|   |-- Http/
|   |   |-- Controllers/
|   |   |   |-- Auth/
|   |   |   |   |-- AuthController.php
|   |   |   |-- HealthCheckController.php
|   |   |-- Middleware/
|   |   |   |-- EnsureUserHasRole.php
|   |   |   |-- EnsureSchoolTenant.php
|   |   |   |-- SecurityHeadersMiddleware.php
|   |   |-- Requests/
|   |   |   |-- Auth/
|   |   |       |-- LoginRequest.php
|   |-- Models/                      # Eloquent Data Entities
|   |   |-- User.php
|   |-- Traits/                      # Reusable Cross-Cutting Traits
|       |-- ApiResponse.php          # Pola Respon JSON Standar
|-- bootstrap/
|   |-- app.php                      # Routing, Middleware & Exception Configuration
|-- config/                          # Application Configuration Files
|-- database/
|   |-- factories/
|   |   |-- UserFactory.php          # Factory Pengujian dengan States
|   |-- migrations/                  # Skema Database Migrations
|   |-- seeders/
|       |-- DatabaseSeeder.php       # Data Seeding Awal Pengembangan
|-- docs/                            # Dokumentasi Resmi Arsitektur & Pedoman
|   |-- ARCHITECTURE.md
|   |-- DATABASE_DESIGN.md
|   |-- SECURITY.md
|   |-- ROLES_AND_PERMISSIONS.md
|   |-- UI_DESIGN_SYSTEM.md
|   |-- TESTING.md
|   |-- DEVELOPMENT_ROADMAP.md
|   |-- DECISIONS.md
|-- resources/
|   |-- css/
|   |   |-- app.css                  # Tailwind CSS v4 Theme & Design Tokens
|   |-- js/
|   |   |-- app.js                   # Alpine.js & Global Event Utilities
|   |-- views/
|       |-- auth/                    # Tampilan Autentikasi
|       |   |-- login.blade.php
|       |-- components/              # Reusable Design System Blade Components
|       |   |-- layouts/             # Layout Component Proxies
|       |   |-- alert.blade.php
|       |   |-- application-logo.blade.php
|       |   |-- badge.blade.php
|       |   |-- button.blade.php
|       |   |-- card.blade.php
|       |   |-- empty-state.blade.php
|       |   |-- input.blade.php
|       |   |-- modal.blade.php
|       |   |-- pagination.blade.php
|       |   |-- select.blade.php
|       |   |-- skeleton.blade.php
|       |   |-- table.blade.php
|       |   |-- textarea.blade.php
|       |   |-- toast.blade.php
|       |-- layouts/                 # Master Layouts (guest, app)
|       |-- dashboard.blade.php      # Shell Dashboard Verifikasi
|       |-- design-system.blade.php  # Living Interactive Design System Showcase
|       |-- welcome.blade.php        # Halaman Beranda Publik
|-- routes/
|   |-- api.php                      # REST API Endpoints (v1)
|   |-- web.php                      # Web Route Definitions
|-- tests/
|   |-- Feature/                     # Integrasi & Functional Tests
|   |-- Unit/                        # Unit Domain Tests
|-- .env.example                     # Environment Template Bersih Tanpa Rahasia
|-- README.md                        # Panduan Operasional Proyek
```

---

## 4. API Standardization Pattern

Seluruh endpoint API menerapkan kontrak respon standar melalui trait `App\Traits\ApiResponse`:

### Respon Sukses (HTTP 200/201):
```json
{
  "success": true,
  "message": "Permintaan berhasil diproses.",
  "data": { ... },
  "errors": null,
  "timestamp": "2026-10-12T15:00:00+07:00"
}
```

### Respon Error (HTTP 400/401/403/422/500):
```json
{
  "success": false,
  "message": "Validasi input gagal.",
  "data": null,
  "errors": {
    "email": ["Format email tidak valid."]
  },
  "timestamp": "2026-10-12T15:00:00+07:00"
}
```

### Respon Paginated:
```json
{
  "success": true,
  "message": "Data berhasil diambil.",
  "data": [ ... ],
  "errors": null,
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75,
    "has_more_pages": true
  },
  "timestamp": "2026-10-12T15:00:00+07:00"
}
```

---

## 5. Error Handling & Exception Policy

1. **Production:** `APP_DEBUG=false` wajib diterapkan. Stack trace disembunyikan total dari pengguna akhir untuk mencegah kebocoran informasi arsitektur (*information disclosure*).
2. **Content-Type Negotiation:** Exception handler secara otomatis mendeteksi apakah request berasal dari API (`api/*` atau `Accept: application/json`) dan merender JSON ApiResponse standar, sedangkan request browser merender halaman kesalahan yang ramah pengguna.
3. **Logging Sanitization:** Konfigurasi log memfilter field sensitif seperti `password`, `token`, `secret`, dan `authorization` header sebelum ditulis ke berkas log.
