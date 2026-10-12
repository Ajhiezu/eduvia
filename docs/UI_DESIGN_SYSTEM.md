# EDUVIA UI DESIGN SYSTEM & FRONTEND SPECIFICATION
**Version:** 1.0.0 (Phase 0 Foundation)  
**CSS Engine:** Tailwind CSS v4 via `@tailwindcss/vite`  
**Micro-Interactions:** Alpine.js 3.x  
**Aesthetics Philosophy:** Modern Academic Trust, Clean Contrast, High Readability  

---

## 1. Filosofi Desain Eduvia

Desain antarmuka Eduvia dibangun untuk mencerminkan institusi pendidikan modern di Indonesia:
- **Academic Trust & Credibility:** Menggunakan palet biru royal/indigo yang memancarkan integritas, stabilitas, dan fokus belajar.
- **Keterbacaan Optimal (High Legibility):** Tipografi sans-serif modern dengan kontras warna yang memenuhi standar WCAG 2.1 AA.
- **Ringan & Bebas Bloat:** Interaktivitas berbasis Alpine.js tanpa runtime JavaScript ratusan kilobyte sehingga halaman memuat dengan cepat di jaringan sekolah mana pun.

---

## 2. Design Tokens System

Dikonfigurasikan di `resources/css/app.css` menggunakan token `@theme`:

### A. Palet Warna (Color Tokens)
- **Primary Brand (Eduvia Blue):**
  - `--color-brand-50`: `#eff6ff` (Subtle tint, active navigation)
  - `--color-brand-100`: `#dbeafe` (Border chip)
  - `--color-brand-600`: `#2563eb` (Interactive primary CTA, focus rings)
  - `--color-brand-700`: `#1d4ed8` (Button hover state)
  - `--color-brand-800`: `#1e40af` (Academic banner)
  - `--color-brand-900`: `#1e3a8a` (Deep institutional header)
- **Achievement & Accent (Warm Amber/Gold):**
  - `--color-accent-400`: `#fbbf24`
  - `--color-accent-500`: `#f59e0b` (Medal badge, highlighted action)
  - `--color-accent-600`: `#d97706`
- **Semantic Feedback Status:**
  - Success: `#10B981` (Emerald) — status aktif, tugas terkirim, kelulusan.
  - Warning: `#F59E0B` (Amber) — tenggat mendekati, perlu konfirmasi.
  - Danger: `#E11D48` (Rose) — pembatalan, akun non-aktif, batas waktu lewat.
- **Neutral Surface & Canvas:**
  - Canvas Body: `#F8FAFC` (Slate 50)
  - Card & Container: `#FFFFFF`
  - Divider & Border: `#E2E8F0` (Slate 200)
  - Primary Typography: `#0F172A` (Slate 900)
  - Secondary Typography: `#64748B` (Slate 500)

### B. Tipografi
- **Primary Font Family:** `"Plus Jakarta Sans", "Inter", system-ui, sans-serif`.
- **Hierarki:**
  - H1 / Hero: 32px – 48px, font-extrabold, tracking-tight.
  - H2 / Section Title: 24px – 30px, font-bold.
  - H3 / Card Title: 16px – 20px, font-bold.
  - Body Text: 14px – 16px, line-height 1.6.
  - Metadata / Caption: 11px – 12px, font-medium.

---

## 3. Komponen Reusable Blade & API Props

| Komponen | Lokasi Berkas | Ringkasan Props & Fitur Utama |
| :--- | :--- | :--- |
| `<x-button>` | `components/button.blade.php` | `variant` (primary, secondary, outline, ghost, danger, success, accent), `size` (sm, md, lg), `type`, `href`, `disabled`, `loading`. |
| `<x-input>` | `components/input.blade.php` | `label`, `name`, `type`, `placeholder`, `required`, `disabled`, `hint`, `error`, slot `prefixIcon` & `suffixIcon`. |
| `<x-select>` | `components/select.blade.php` | `label`, `name`, `required`, `disabled`, `hint`, `error`, chevron SVG terintegrasi. |
| `<x-textarea>` | `components/textarea.blade.php` | `label`, `name`, `rows`, `placeholder`, `required`, `disabled`, `hint`, `error`. |
| `<x-card>` | `components/card.blade.php` | `title`, `subtitle`, `hover`, `padding`, slot `action`, `header`, `footer`. |
| `<x-badge>` | `components/badge.blade.php` | `variant` (primary, accent, success, warning, danger, neutral), `size` (sm, md), `dot` (boolean indicator). |
| `<x-alert>` | `components/alert.blade.php` | `variant` (info, success, warning, danger), `title`, `dismissible` (Alpine animation). |
| `<x-modal>` | `components/modal.blade.php` | `name`, `title`, `maxWidth` (sm, md, lg, xl, 2xl), slot `footer`, listener event `open-modal` & `close-modal`, ESC key closing. |
| `<x-table>` | `components/table.blade.php` | `striped`, `hoverable`, slot `header`, slot `footer`, slot `empty`. |
| `<x-pagination>` | `components/pagination.blade.php` | `currentPage`, `totalPages`, `totalItems`, `perPage`. |
| `<x-skeleton>` | `components/skeleton.blade.php` | `type` (line, circle, card, table), `lines`. |
| `<x-empty-state>` | `components/empty-state.blade.php` | `title`, `description`, slot `icon`, slot `action`. |
| `<x-toast>` | `components/toast.blade.php` | Alpine toast container global, mendengarkan event JS `notify` dan session flash `success`/`error`/`status`. |

---

## 4. Aksesibilitas dan Interaksi Responsif

1. **Focus Rings:** Seluruh elemen interaktif memiliki `focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2` yang tegas saat dinavigasi menggunakan keyboard Tab.
2. **Accessible Labels:** Setiap komponen input menyertakan atribut `id`, `for`, dan penanda `required` berupa bintang merah jika wajib diisi.
3. **Pemeriksaan Responsivitas Viewport:**
   - 320 px (Mobile kecil): Komponen tabel otomatis mengaktifkan container horizontal scroll tanpa merusak struktur halaman luar.
   - 360 – 430 px (Layar Smartphone umum): Sidebar otomatis beralih menjadi Drawer melayang dengan tombol hamburger dan backdrop blur.
   - 768 px (Tablet): Layout beralih ke grid 2 kolom.
   - 1024 – 1440 px (Laptop & Desktop HD): Sidebar paten, grid 3-4 kolom aktif, whitespace proporsional.
