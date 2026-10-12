# EDUVIA PLATFORM SECURITY SPECIFICATION & POLICIES
**Version:** 1.0.0 (Phase 0 Foundation)  
**Standard Compliance:** OWASP Top 10 Web Application Security Guidelines  

---

## 1. Ikhtisar Kebijakan Keamanan

Keamanan pada platform Eduvia diperlakukan sebagai **prasyarat fondasi (Defense-in-Depth)**, bukan sekadar pelengkap yang ditambahkan di akhir pengerjaan. Seluruh arus data dari browser pengguna ke server database dilindungi oleh beberapa lapis pengamanan independen.

---

## 2. Implementasi Keamanan Fase 0

### A. Proteksi Injeksi (SQL Injection & XSS)
1. **Parameterized Queries:** Seluruh operasi data menggunakan Eloquent ORM dan PDO Prepared Statements. Dilarang keras menggunakan konkatenasi string mentah pada kueri basis data.
2. **Context-Aware Output Escaping:** Seluruh view Blade menggunakan kurung kurawal ganda `{{ $var }}` yang otomatis memanggil `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`. Penggunaan `{!! $var !!}` dilarang kecuali untuk konten terverifikasi dan telah melewati sanitasi HTML Purifier ketat.

### B. Proteksi Pemalsuan Permintaan Lintas Situs (CSRF)
1. Seluruh formulir HTTP POST/PUT/DELETE wajib menyertakan token `@csrf`.
2. Token sesi diperbarui secara otomatis setiap kali pengguna melakukan *login* atau *logout* (`$request->session()->regenerateToken()`) untuk mencegah serangan *Session Fixation*.

### C. Defense-in-Depth HTTP Security Headers
Diimplementasikan secara global melalui `App\Http\Middleware\SecurityHeadersMiddleware`:
- `X-Frame-Options: SAMEORIGIN` — Melindungi aplikasi dari serangan *Clickjacking*.
- `X-Content-Type-Options: nosniff` — Mencegah browser mengeksekusi file non-script sebagai executable script melalui MIME confusion.
- `X-XSS-Protection: 1; mode=block` — Mengaktifkan filter XSS reflektif pada peramban klien lawas.
- `Referrer-Policy: strict-origin-when-cross-origin` — Mencegah kebocoran URL sensitif ke domain eksternal.
- `Permissions-Policy: camera=(), microphone=(), geolocation=()` — Menonaktifkan akses perangkat keras yang tidak relevan.
- `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload` — Diaktifkan pada environment produksi untuk memaksa komunikasi HTTPS.

### D. Hashing Kredensial & Pengelolaan Rahasia
1. **Password Hashing:** Menggunakan algoritma Bcrypt dengan cost/rounds 12 (`BCRYPT_ROUNDS=12`) untuk memastikan ketahanan terhadap serangan *brute-force* atau *rainbow table*.
2. **Secret Separation:** Seluruh kredensial (kunci enkripsi aplikasi, koneksi database, secret token) disimpan secara eksklusif di dalam file `.env` yang dikecualikan dari Git melalui `.gitignore`. File `.env.example` hanya berisi template kosong tanpa nilai rahasia aktual.

### E. Pembatasan Laju Permintaan (Rate Limiting)
1. **Login Throttle:** Rute `/login` dibatasi maksimal **5 percobaan per menit per alamat IP / email** (`throttle:5,1` dan `RateLimiter::hit(...)`).
2. Setelah batas terlampaui, request diblokir dengan kode HTTP 429 (Too Many Requests) dan pengguna harus menunggu periode pendinginan sebelum dapat mencoba kembali.

---

## 3. Kebijakan Pengunggahan File (File Upload Policy - Rencana Fase 2)

Ketika modul materi atau tugas diimplementasikan pada fase berikutnya, aturan keamanan berikut wajib dipatuhi:
1. **Validasi Tipe Konten:** Validasi ekstensi file serta MIME-type sebenarnya (`mimes:pdf,docx,jpg,png,zip`).
2. **Batas Ukuran:** Batas maksimal unggahan berkas siswa didefinisikan secara ketat (misal: maksimal 10 MB per dokumen).
3. **Penyimpanan Non-Executable:** Seluruh file unggahan disimpan di luar `public/` web root langsung atau diakses melalui *signed storage URL* agar file berbahaya (seperti skrip PHP) tidak dapat dieksekusi langsung oleh web server.
4. **Randomized File Names:** Nama berkas asli di-hash menjadi UUID acak untuk menghindari *Path Traversal attack*.

---

## 4. Kebijakan Logging dan Pencegahan Kebocoran Informasi

1. **Information Disclosure Prevention:** Pada lingkungan produksi (`APP_ENV=production`), `APP_DEBUG=false` harus selalu aktif. Stack trace, query SQL internal, dan path berkas server tidak boleh ditampilkan kepada pengguna.
2. **Sensitive Data Scrubbing:** Parameter sensitif (`password`, `password_confirmation`, `credit_card`, `token`, `secret`) otomatis disaring dari log Laravel.
