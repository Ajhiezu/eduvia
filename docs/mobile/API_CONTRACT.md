# EDUVIA MOBILE ECOSYSTEM — REST API CONTRACT
**Document ID:** `EDV-MOB-API-001`  
**Version:** 1.0.0 (Tahap 0 Baseline)  
**Base URL:** `https://eduvia.school/api/v1` (Production) / `http://127.0.0.1:8000/api/v1` (Local Dev)  
**Standard Envelopes:** Implemented using `App\Traits\ApiResponse`  

---

## 1. Konvensi Global & Format Respons

Setiap respons dari API Eduvia dibungkus dalam JSON Envelope standar berikut:

### Format Respons Berhasil (Success Envelope)
```json
{
  "success": true,
  "message": "Permintaan berhasil diproses.",
  "data": { ... },
  "errors": null,
  "timestamp": "2026-10-16T08:50:00+07:00"
}
```

### Format Respons Berpaginasi (Paginated Envelope)
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
    "total": 68,
    "has_more_pages": true
  },
  "timestamp": "2026-10-16T08:50:00+07:00"
}
```

### Format Respons Kesalahan (Error Envelope)
```json
{
  "success": false,
  "message": "Validasi gagal.",
  "data": null,
  "errors": {
    "email": ["Email atau kata sandi tidak valid."]
  },
  "timestamp": "2026-10-16T08:50:00+07:00"
}
```

### Standard HTTP Status Codes
* `200 OK`: Permintaan berhasil dan data dikembalikan.
* `201 Created`: Sumber daya baru berhasil dibuat.
* `400 Bad Request`: Parameter permintaan tidak valid.
* `401 Unauthorized`: Kredensial tidak valid atau token kedaluwarsa/tidak disertakan.
* `403 Forbidden`: Pengguna tidak memiliki hak akses (role tidak sesuai atau pelanggaran IDOR).
* `404 Not Found`: Objek yang diminta tidak ditemukan pada sekolah pengguna.
* `422 Unprocessable Entity`: Validasi input formulir gagal.
* `429 Too Many Requests`: Rate limit terlampaui.
* `500 Internal Server Error`: Kesalahan server internal (dilogkan secara aman).

---

## 2. Modul Autentikasi (`/auth`)

### 2.1 Login Akun Mobile
- **Method & Path:** `POST /api/v1/auth/login`
- **Rate Limit:** 5 percobaan per menit per IP
- **Headers:** `Accept: application/json`
- **Request Body:**
```json
{
  "email": "orangtua@sekolah.sch.id",
  "password": "SecretPassword123!",
  "device_name": "iPhone 15 Pro Max"
}
```
- **Response `200 OK`:**
```json
{
  "success": true,
  "message": "Autentikasi berhasil.",
  "data": {
    "token": "1|qW8e7...sanctum_token_string...",
    "user": {
      "id": 12,
      "name": "Budi Pratama",
      "email": "orangtua@sekolah.sch.id",
      "phone": "081234567890",
      "role": "parent",
      "school_id": 1,
      "avatar": "https://eduvia.school/storage/avatars/user_12.png"
    },
    "school": {
      "id": 1,
      "name": "SMA Negeri 1 Eduvia",
      "logo": "https://eduvia.school/images/brand/eduvia-icon-mark-transparent.png"
    }
  },
  "errors": null,
  "timestamp": "2026-10-16T08:50:00+07:00"
}
```

### 2.2 Profil Pengguna Aktif
- **Method & Path:** `GET /api/v1/auth/me`
- **Headers:** `Authorization: Bearer <TOKEN>`
- **Response `200 OK`:**
```json
{
  "success": true,
  "message": "Profil berhasil dimuat.",
  "data": {
    "id": 12,
    "name": "Budi Pratama",
    "email": "orangtua@sekolah.sch.id",
    "phone": "081234567890",
    "role": "parent",
    "school_id": 1,
    "avatar": "https://eduvia.school/storage/avatars/user_12.png"
  },
  "errors": null,
  "timestamp": "2026-10-16T08:50:00+07:00"
}
```

### 2.3 Logout Akun
- **Method & Path:** `POST /api/v1/auth/logout`
- **Headers:** `Authorization: Bearer <TOKEN>`
- **Response `200 OK`:**
```json
{
  "success": true,
  "message": "Sesi berhasil diakhiri.",
  "data": null,
  "errors": null,
  "timestamp": "2026-10-16T08:50:00+07:00"
}
```

---

## 3. Modul Eduvia Parent Portal (`/parent`)

> **Catatan Keamanan IDOR:** Seluruh endpoint dengan parameter `{childId}` memvalidasi apakah anak terdaftar di `ParentPortalService::getActiveChildren($user)`. Jika tidak cocok, server mengembalikan status `403 Forbidden`.

### 3.1 Daftar Anak Terverifikasi
- **Method & Path:** `GET /api/v1/parent/children`
- **Headers:** `Authorization: Bearer <TOKEN>`
- **Response `200 OK`:**
```json
{
  "success": true,
  "message": "Daftar anak berhasil diambil.",
  "data": [
    {
      "id": 4,
      "name": "Ahmad Fauzi",
      "nisn": "0081234567",
      "nis": "220101",
      "relationship_type": "father",
      "classroom": {
        "id": 3,
        "name": "X-IPA 1",
        "grade_level": "Kelas 10",
        "homeroom_teacher": "Dra. Siti Rahmah, M.Pd"
      },
      "academic_year": "2026/2027 Ganjil"
    }
  ],
  "errors": null,
  "timestamp": "2026-10-16T08:50:00+07:00"
}
```

### 3.2 Klaim Kode Penautan Anak (Link Code)
- **Method & Path:** `POST /api/v1/parent/children/claim-code`
- **Request Body:**
```json
{
  "code": "LINK-4829-AFZ1",
  "relationship_type": "father"
}
```
- **Response `200 OK`:**
```json
{
  "success": true,
  "message": "Penautan anak berhasil.",
  "data": {
    "student_id": 4,
    "student_name": "Ahmad Fauzi",
    "relationship_type": "father",
    "status": "active"
  },
  "errors": null,
  "timestamp": "2026-10-16T08:50:00+07:00"
}
```

### 3.3 Dasbor Ringkasan Anak
- **Method & Path:** `GET /api/v1/parent/children/{childId}/dashboard`
- **Response `200 OK`:**
```json
{
  "success": true,
  "message": "Dasbor anak berhasil dimuat.",
  "data": {
    "child": {
      "id": 4,
      "name": "Ahmad Fauzi",
      "classroom_name": "X-IPA 1",
      "homeroom_teacher": "Dra. Siti Rahmah, M.Pd"
    },
    "attendance_summary": {
      "total": 45,
      "present": 42,
      "late": 2,
      "sick": 1,
      "permit": 0,
      "absent": 0,
      "percentage": 97.8
    },
    "upcoming_assignments": [
      {
        "id": 14,
        "title": "Tugas Bab 3: Hukum Newton",
        "subject": "Fisika",
        "due_date": "2026-10-18T23:59:00+07:00",
        "is_submitted": true
      }
    ],
    "recent_announcements": [
      {
        "id": 8,
        "title": "Pelaksanaan Penilaian Tengah Semester (PTS)",
        "published_at": "2026-10-15T09:00:00+07:00",
        "is_pinned": true
      }
    ],
    "finance_summary": {
      "total_outstanding": 350000,
      "pending_verification": 0
    }
  },
  "errors": null,
  "timestamp": "2026-10-16T08:50:00+07:00"
}
```

### 3.4 Riwayat Kehadiran Anak
- **Method & Path:** `GET /api/v1/parent/children/{childId}/attendance`
- **Query Params:** `?month=10&year=2026`
- **Response `200 OK`:** Mengembalikan ringkasan statistik dan daftar catatan presensi harian per mata pelajaran / sesi.

### 3.5 Jadwal Pelajaran Anak
- **Method & Path:** `GET /api/v1/parent/children/{childId}/schedules`
- **Response `200 OK`:** Daftar mata pelajaran per hari (Senin - Jumat) beserta guru pengampu, jam, dan ruang.

### 3.6 Nilai & Rapor Resmi Terbit
- **Method & Path:** `GET /api/v1/parent/children/{childId}/report-cards`
- **Response `200 OK`:**
```json
{
  "success": true,
  "message": "Daftar rapor resmi berhasil dimuat.",
  "data": [
    {
      "id": 1,
      "academic_year": "2026/2027",
      "semester": "Ganjil",
      "classroom": "X-IPA 1",
      "published_at": "2026-12-20T10:00:00+07:00",
      "status": "published",
      "download_url": "/api/v1/parent/children/4/report-cards/1/pdf"
    }
  ],
  "errors": null,
  "timestamp": "2026-10-16T08:50:00+07:00"
}
```

### 3.7 Keuangan & Tagihan Anak
- **Method & Path:** `GET /api/v1/parent/children/{childId}/finance/invoices`
- **Method & Path:** `POST /api/v1/parent/children/{childId}/finance/invoices/{id}/pay`
- **Request (Multipart Form Data):** `amount`, `payment_date`, `payment_method`, `reference_number`, `proof_file`.

### 3.8 Komunikasi Orang Tua - Guru
- **Method & Path:** `GET /api/v1/parent/conversations`
- **Method & Path:** `POST /api/v1/parent/conversations` (Inisiasi percakapan baru dengan wali kelas/guru mapel anak)
- **Method & Path:** `GET /api/v1/parent/conversations/{id}`
- **Method & Path:** `POST /api/v1/parent/conversations/{id}/messages`

---

## 4. Modul Eduvia Student Portal (`/student`)

### 4.1 Dasbor Belajar Siswa
- **Method & Path:** `GET /api/v1/student/dashboard`
- **Response `200 OK`:**
```json
{
  "success": true,
  "message": "Dasbor belajar berhasil dimuat.",
  "data": {
    "student": {
      "id": 4,
      "name": "Ahmad Fauzi",
      "classroom": "X-IPA 1",
      "homeroom_teacher": "Dra. Siti Rahmah, M.Pd"
    },
    "today_schedules": [
      {
        "id": 101,
        "subject": "Matematika Wajib",
        "teacher": "Drs. Bambang Sudarsono",
        "time": "07:30 - 09:00",
        "room": "R. 101"
      }
    ],
    "active_exams_count": 1,
    "pending_assignments_count": 2,
    "new_materials_count": 3
  },
  "errors": null,
  "timestamp": "2026-10-16T08:50:00+07:00"
}
```

### 4.2 Materi Pembelajaran Siswa
- **Method & Path:** `GET /api/v1/student/materials` (Paginated)
- **Method & Path:** `GET /api/v1/student/materials/{id}`
- **Method & Path:** `POST /api/v1/student/materials/{id}/progress` (`status: completed`)

### 4.3 Tugas & Pengumpulan
- **Method & Path:** `GET /api/v1/student/assignments`
- **Method & Path:** `GET /api/v1/student/assignments/{id}`
- **Method & Path:** `POST /api/v1/student/assignments/{id}/submit` (Multipart: `text_answer`, `attachment_file`)

### 4.4 CBT / Ujian Online Siswa
- **Daftar Ujian:** `GET /api/v1/student/exams?tab=available` (`available`, `upcoming`, `completed`)
- **Detail / Instruksi:** `GET /api/v1/student/exams/{id}`
- **Mulai / Resume Ujian:** `POST /api/v1/student/exams/{id}/start`
  - Mengembalikan `attempt_id`, `questions_snapshot` (tanpa kunci jawaban), `started_at`, `expires_at`, `remaining_seconds`.
- **Autosave Jawaban Soal:** `POST /api/v1/student/exams/{id}/attempts/{attemptId}/save-answer`
  - Payload:
```json
{
  "question_id": 52,
  "answer": "B",
  "is_marked_for_review": false
}
```
  - Response `200 OK`: `remaining_seconds`, `saved_at`, `is_marked_for_review`.
- **Kumpulkan Ujian (Final Submit):** `POST /api/v1/student/exams/{id}/attempts/{attemptId}/submit`
- **Hasil & Pembahasan Ujian:** `GET /api/v1/student/exams/{id}/attempts/{attemptId}/result`
