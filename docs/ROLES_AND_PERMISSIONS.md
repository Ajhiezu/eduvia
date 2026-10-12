# EDUVIA ROLES AND PERMISSIONS SPECIFICATION
**Version:** 1.0.0 (Phase 0 Foundation)  
**Security Model:** Role-Based Access Control (RBAC) with Granular Capabilities  

---

## 1. Hirarki Peran Pengguna (User Roles)

Eduvia membedakan hak akses berdasarkan 5 peran pengguna utama yang didefinisikan dalam enum PHP 8.3 `App\Enums\UserRole`:

| Kode Enum | Nama Peran | Deskripsi dan Cakupan Hak Akses |
| :--- | :--- | :--- |
| `super_admin` | **Super Administrator** | Administrator utama platform Eduvia. Memiliki akses global tanpa batas ke seluruh data sistem, konfigurasi server, serta manajemen antar-sekolah. |
| `school_admin` | **Admin Sekolah** | Administrator operasional tingkat sekolah. Mengelola guru, siswa, kelas, mata pelajaran, dan konfigurasi kalender akademik di sekolahnya. |
| `teacher` | **Guru / Pendidik** | Tenaga pengajar. Berhak mengelola materi pembelajaran, memberikan tugas, menyusun bank soal, melaksanakan ujian CBT, dan mengisi nilai kelas yang diampunya. |
| `student` | **Siswa / Pelajar** | Peserta didik. Mengakses materi, mengumpulkan tugas, mengikuti ujian, melihat presensi, serta memantau capaian nilai pribadi. |
| `parent` | **Orang Tua / Wali** | Wali murid. Hak akses hanya baca (*read-only*) untuk memantau presensi, rapor nilai, pengumuman sekolah, dan perkembangan belajar anak mereka. |

---

## 2. Matriks Kapabilitas Izin (Permission Capabilities Matrix)

Didefinisikan dalam enum `App\Enums\Permission`:

| Area Kapabilitas | Kode Permission | Super Admin | Admin Sekolah | Guru | Siswa | Orang Tua |
| :--- | :--- | :---: | :---: | :---: | :---: | :---: |
| **Manajemen Sekolah** | `school:manage` | [x] | [ ] | [ ] | [ ] | [ ] |
| | `school:view` | [x] | [x] | [ ] | [ ] | [ ] |
| **Manajemen Pengguna** | `users:manage` | [x] | [x] | [ ] | [ ] | [ ] |
| | `users:view` | [x] | [x] | [ ] | [ ] | [ ] |
| **Struktur Akademik** | `academic:manage` | [x] | [x] | [ ] | [ ] | [ ] |
| | `academic:view` | [x] | [x] | [ ] | [ ] | [ ] |
| **Rombongan Belajar** | `classes:manage` | [x] | [x] | [ ] | [ ] | [ ] |
| | `classes:view` | [x] | [x] | [x] | [x] | [x] |
| **Materi Pembelajaran**| `learning:manage` | [x] | [ ] | [x] | [ ] | [ ] |
| | `learning:view` | [x] | [x] | [x] | [x] | [ ] |
| **Tugas & Submission** | `assignment:manage` | [x] | [ ] | [x] | [ ] | [ ] |
| | `assignment:submit` | [ ] | [ ] | [ ] | [x] | [ ] |
| | `assignment:grade` | [x] | [ ] | [x] | [ ] | [ ] |
| **Ujian CBT** | `exam:manage` | [x] | [ ] | [x] | [ ] | [ ] |
| | `exam:take` | [ ] | [ ] | [ ] | [x] | [ ] |
| **Buku Nilai / Rapor** | `report:manage` | [x] | [x] | [x] | [ ] | [ ] |
| | `report:view-own` | [ ] | [ ] | [ ] | [x] | [x] |

---

## 3. Prinsip Otorisasi Backend

> [!WARNING]
> **Menyembunyikan tombol di antarmuka BUKAN mekanisme keamanan.**  
> Seluruh pemeriksaan hak akses wajib dieksekusi di level backend melalui Middleware, Form Requests, atau Laravel Policies sebelum kueri data dijalankan.

### A. Route Middleware Enforcement
Setiap rute yang memerlukan hak khusus dilindungi oleh middleware `EnsureUserHasRole`:

```php
// Contoh rute khusus administrator:
Route::middleware(['auth', 'role:super_admin,school_admin'])->group(function () {
    // Rute administratif
});

// Contoh rute khusus guru:
Route::middleware(['auth', 'role:teacher'])->group(function () {
    // Rute pembuatan materi & tugas
});
```

Jika pengguna yang tidak memiliki peran mencoba mengakses endpoint tersebut:
- Permintaan API: Menghasilkan respon JSON `HTTP 403 Forbidden` (`{"success": false, "message": "Akses ditolak..."}`).
- Permintaan Web: Mengeluarkan halaman `403 Forbidden` resmi.

### B. Helper Methods pada Model User
Model `App\Models\User` menyediakan metode pembantu yang teruji:
- `$user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN)`
- `$user->hasPermission(Permission::LEARNING_MANAGE)`
- `$user->isSuperAdmin()`, `$user->isTeacher()`, `$user->isStudent()`
- `$user->isActive()`
