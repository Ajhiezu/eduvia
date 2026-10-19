# EDUVIA SRE INCIDENT RESPONSE RUNBOOK
## Buku Panduan Operasional Penanganan Insiden, Ambang Peringatan, & Observabilitas Sistem
**Versi Dokumen:** 1.0 (Fase 5F Baseline)  
**Klasifikasi:** Dokumen Teknis Kesiapan Operasional (SRE / DevOps / IT Yayasan)  
**Tanggal Penerbitan:** 19 Oktober 2026  
**Standar Observabilitas:** Correlation ID (`X-Request-ID`), Structured Logging, Health Checks, Alert Thresholds

---

## 1. PRINSIP OBSERVABILITAS & JEJAK KORELASI (CORRELATION ID)

Setiap request yang masuk ke Eduvia (Web Portal maupun REST API Mobile) wajib memiliki **Correlation ID (`X-Request-ID`)**:
* **Generasi Otomatis:** Jika klien tidak menyertakan header `X-Request-ID`, middleware `SecurityHeadersMiddleware` secara otomatis menghasilkan UUID v4 acak.
* **Context Sharing:** Correlation ID disuntikkan ke konteks global logger via `Log::shareContext(['request_id' => $requestId])`, memastikan setiap baris log yang tercatat selama siklus eksekusi memuat ID yang sama.
* **Audit Trail Integration:** Model `AuditLog` secara otomatis mencatat `request_id` untuk menghubungkan aksi pengguna dengan entri server log.
* **Client Response Header:** Header `X-Request-ID` dikembalikan pada setiap HTTP response sehingga pengguna atau tim support mobile app dapat melampirkan ID tersebut saat melaporkan kendala.

### Struktur JSON Log Terstruktur (Standard Monolog JSON Format):
```json
{
  "timestamp": "2026-10-19T08:28:40.123456+07:00",
  "level": "ERROR",
  "message": "Payment gateway connection timeout",
  "context": {
    "request_id": "7b8e192c-567a-4efb-9411-82d1c6919241",
    "school_id": 1,
    "user_id": 42,
    "endpoint": "/api/v1/parent/invoices/105/pay",
    "order_id": "PAY-20261019-ABCDE"
  }
}
```

> [!IMPORTANT]
> **ATURAN PII DALAM LOG:**
> DILARANG KERAS mencatat data NIK (16 digit), NISN (10 digit), nomor kartu kredit/debit, CVV, password plain text, atau detail nominal tabungan pribadi ke dalam file log. Seluruh data identitas sensitif wajib disamarkan atau dihilangkan sebelum dicatat.

---

## 2. HEALTH CHECK & MONITORING ENDPOINTS

Eduvia menyediakan endpoint pemantauan kesehatan sistem publik & DevOps:
* **Web:** `GET /health`
* **REST API:** `GET /api/v1/health`

### Spesifikasi Respons JSON Endpoint `/api/v1/health`:
```json
{
  "success": true,
  "message": "Seluruh layanan sistem Eduvia beroperasi normal.",
  "data": {
    "status": "healthy",
    "app": {
      "name": "Eduvia",
      "env": "production",
      "debug": false,
      "version": "1.0.0-phase1",
      "framework": "Laravel 12.x",
      "php": "8.3.31"
    },
    "services": {
      "database": {
        "connection": "mysql",
        "status": "healthy",
        "latency_ms": 1.25
      },
      "cache": {
        "driver": "database",
        "status": "healthy"
      },
      "storage": {
        "disk": "local",
        "status": "healthy"
      },
      "queue": {
        "connection": "database",
        "status": "healthy",
        "pending_jobs": 0
      },
      "ai_engine": {
        "status": "operational",
        "active_school_quotas": 5
      },
      "multi_tenancy": {
        "status": "operational",
        "active_schools_count": 5
      }
    },
    "system": {
      "timezone": "Asia/Jakarta",
      "server_time": "2026-10-19T15:28:40+07:00",
      "uptime": "operational"
    }
  },
  "errors": null,
  "timestamp": "2026-10-19T15:28:40+07:00"
}
```

*Jika salah satu layanan kritis (DB / Storage) mati, endpoint mengembalikan HTTP status **503 Service Unavailable** dengan field `status: "degraded"`.*

---

## 3. DEFINISI AMBANG BATAS PERINGATAN (ALERT THRESHOLDS)

| Metrik Pemantauan | Ambang Peringatan (Warning) | Ambang Kritis (Critical Pager) | Kanal Notifikasi | Tindakan Otomatis / Prosedur Respons |
| :--- | :--- | :--- | :--- | :--- |
| **HTTP Error Rate (5xx)** | > 0.5% request dalam 5 menit | **> 1.0% request dalam 5 menit** | Telegram Bot SRE & PagerDuty | 1. Periksa `storage/logs/laravel.log` filter level `ERROR`.<br>2. Cari lonjakan error DB connection atau PHP fatal.<br>3. Restart PHP-FPM jika pool jenuh. |
| **Latensi Endpoint (p95)** | > 300 ms dalam 5 menit | **> 500 ms dalam 5 menit** | Telegram Bot SRE | 1. Periksa slow query log MySQL (`long_query_time=1.0`).<br>2. Verifikasi beban CBT concurrent autosave.<br>3. Periksa CPU & memory utilization server. |
| **Antrean Job (Queue Backlog)** | > 200 jobs tertunda | **> 500 jobs pending ATAU job macet > 10 menit** | Telegram Bot SRE | 1. Cek worker supervisor: `supervisorctl status`.<br>2. Restart worker: `php artisan queue:restart`.<br>3. Periksa `failed_jobs` table: `php artisan queue:failed`. |
| **Sisa Kuota AI Sekolah** | < 25% kuota bulanan tersisa | **< 10% kuota bulanan tersisa** | Email Otomatis ke Bendahara Yayasan & Kepala Sekolah | 1. Kirim alert dashboard admin sekolah.<br>2. Siapkan approval penambahan kuota token AI.<br>3. Nonaktifkan sementara fitur opsional (Lesson Plan AI) jika kuota habis agar tidak overbudget. |
| **Kapasitas Disk Storage** | > 75% kapasitas terpakai | **> 85% kapasitas terpakai** | Notifikasi Tim Infrastruktur | 1. Jalankan pembersihan log lama: `find /var/log -type f -mtime +14 -delete`.<br>2. Bersihkan dump sementara: `php artisan view:clear; php artisan cache:clear`.<br>3. Perluas volume block storage cloud. |
| **Database Connection Pool** | > 70% max connections terpakai | **> 85% max connections terpakai** | Alert DBA / SRE | Periksa transaksi menggantung (`SHOW FULL PROCESSLIST;`) dan kill query tidur (`Sleep` > 60s). |

---

## 4. MATRIKS SEVERITAS INSIDEN & WAKTU TANGGAP (INCIDENT SEVERITY)

* **SEV-1 (Bencana Kritis / Outage Total):**
  - Kriteria: Seluruh siswa tidak bisa ujian CBT, atau pembayaran yayasan lumpuh total, atau data rusak.
  - Response Time: **< 15 Menit**.
  - Escalation: On-Call SRE + Lead Architect + Direktur IT Yayasan.
* **SEV-2 (Degradasi Signifikan):**
  - Kriteria: Latensi respons tinggi (p95 > 1s), notifikasi broadcast terlambat > 30 menit, fitur AI timeout berkala.
  - Response Time: **< 30 Menit**.
  - Escalation: On-Call SRE + Backend Developer.
* **SEV-3 (Isu Minor / Parsial):**
  - Kriteria: Satu guru gagal mengunduh rekap nilai CSV, atau glitch visual badge akreditasi.
  - Response Time: **< 4 Jam**.
  - Escalation: Helpdesk IT Sekolah + Scheduled Ticket.

---

## 5. RUNBOOK LANGKAH-DEMI-LANGKAH PENANGANAN INSIDEN (TRIAGE RUNBOOK)

### Skenario Insiden A: Lonjakan Error 500 Tiba-Tiba
1. **Identifikasi Request ID dari log:**
   ```bash
   grep -i "error" /var/www/eduvia/storage/logs/laravel.log | tail -n 20
   ```
2. **Telusuri jejak stack trace berdasarkan `request_id`:**
   ```bash
   grep "7b8e192c-567a-4efb-9411-82d1c6919241" /var/www/eduvia/storage/logs/laravel.log
   ```
3. **Periksa status koneksi MariaDB/MySQL:**
   ```bash
   mysqladmin -u root -p ping
   mysql -u root -p -e "SHOW STATUS LIKE 'Threads_connected';"
   ```
4. **Jika PHP-FPM hang / memory exhausted:**
   ```bash
   systemctl restart php8.3-fpm
   ```

### Skenario Insiden B: Antrean Job Macet (Queue Stalled)
1. **Periksa status supervisor worker:**
   ```bash
   supervisorctl status
   ```
2. **Lihat daftar job yang gagal:**
   ```bash
   php artisan queue:failed
   ```
3. **Cari akar masalah job gagal (misal API eksternal down atau timeout):**
   ```bash
   php artisan queue:retry all
   ```
4. **Instruksikan worker untuk restart bersih:**
   ```bash
   php artisan queue:restart
   ```

### Skenario Insiden C: Laporan Pembayaran Masuk Namun Status Masih Tertunda
1. **Cari order ID pada transaksi gateway:**
   ```bash
   php artisan tinker --execute='
     $trx = \App\Models\PaymentGatewayTransaction::where("order_id", "PAY-XXXX")->first();
     dump($trx?->toArray());
   '
   ```
2. **Sinkronkan status transaksi langsung ke Midtrans via command line:**
   ```bash
   php artisan tinker --execute='
     app(\App\Services\Payment\PaymentService::class)->syncTransactionStatus("PAY-XXXX");
   '
   ```

---

## 6. KESIMPULAN FASE 5F

Eduvia telah memenuhi standar observabilitas enterprise:
1. Setiap interaksi tercatat dengan **Correlation ID (`X-Request-ID`)** menyeluruh.
2. Endpoint `/health` dan `/api/v1/health` menyediakan diagnosa komprehensif (DB, Cache, Storage, Queue, AI, Tenancy).
3. Ambang batas peringatan terdefinisi dengan matriks respons dan matriks eskalasi konkret.
