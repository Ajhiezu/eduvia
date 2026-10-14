# EDUVIA — UI/UX DESIGN SYSTEM & BRAND MEMORY
**Single Source of Truth (SSOT) for Visual Identity, Frontend Architecture, and Component Standards**  
**Version:** 1.0.0 (Official Brand Alignment)  
**Target:** Indonesian Educational Institutions (SD, SMP, SMA, SMK)  

---

## 1. VISUAL IDENTITY & BRAND PHILOSOPHY

* **Brand Name:** **Eduvia**
* **Official Tagline:** **Learn · Grow · Build Your Future**
* **Deskripsi Produk:** Sistem E-Learning dan Manajemen Sekolah Terintegrasi untuk Jenjang SD/MI, SMP/MTs, SMA/MA, dan SMK/MAK.
* **Karakter Desain:** Modern, Academic, Trustworthy, Clean, Professional, Friendly to Students, Serious for Administrators.
* **Sumber Kebenaran Primer:** Berkas panduan merek resmi `Eduvia Education Brand Style Guide.png`.

---

## 2. OFFICIAL BRAND COLOR PALETTE

| Peran Warna | Hex Code | Kelas Tailwind / Token | Penggunaan & Rasio |
| :--- | :--- | :--- | :--- |
| **PRIMARY RED** | `#E11D2E` | `bg-brand-600`, `text-brand-600`, `border-brand-600` | Warna identitas resmi utama. Digunakan untuk aksi penting (CTA utama), status navigasi aktif, indikator fokus, dan highlight selektif (10–15% visual weight). |
| **PRIMARY RED HOVER** | `#C81423` | `hover:bg-brand-700` | Hover state tombol utama dan elemen interaktif primer. |
| **PRIMARY RED DARK** | `#9E0E1B` | `bg-brand-900` | Deep red untuk teks kontras tinggi pada background putih atau footer gelap. |
| **PRIMARY RED SUBTLE** | `#FEF2F2` | `bg-brand-50` | Background active sidebar / link navigasi terpilih, hover kartu, chip aktif. |
| **PRIMARY RED BORDER** | `#FCA5A5` | `border-brand-300`, `ring-brand-200` | Garis tepi chip aktif, ring focus halus. |
| **DARK TEXT (Primary)** | `#1F2937` | `text-slate-800`, `text-slate-900` | Judul, heading utama, navigasi, dan teks berbobot penting. Rasio kontras tinggi (>7:1). |
| **LIGHT GRAY (Canvas)** | `#F3F4F6` | `bg-surface-bg`, `bg-slate-100` | Background utama kanvas aplikasi, section selang-seling, input disabled. |
| **WHITE (Main Surface)**| `#FFFFFF` | `bg-white`, `bg-surface-card` | **Dominan (70–80% visual weight)**. Background kartu, modal dialog, formulir input, navbar. |
| **BODY TEXT (Secondary)**| `#4B5563` | `text-slate-600` | Paragraf deskripsi, sinopsis modul, petunjuk formulir. |
| **MUTED TEXT** | `#6B7280` | `text-slate-500` | Label tanggal, teks bantuan (helper text), metadata sekunder. |
| **BORDER LINE** | `#E5E7EB` | `border-slate-200` | Garis pembatas kartu, pemisah tabel, border default form input. |

### Semantic Status Colors (Digunakan secara Terukur):
* **Success:** `#10B981` (bg: `#ECFDF5`, border: `#A7F3D0`, text: `#065F46`)
* **Warning / Achievement:** `#F59E0B` (bg: `#FFFBEB`, border: `#FDE68A`, text: `#92400E`)
* **Danger / Error:** `#DC2626` (bg: `#FEF2F2`, border: `#FECACA`, text: `#991B1B`)
* **Info:** `#2563EB` (bg: `#EFF6FF`, border: `#BFDBFE`, text: `#1E40AF`)

> [!WARNING]
> **ATURAN MUTLAK WARNA:**
> - **JANGAN PERNAH** mengubah warna merah utama menjadi warna lain (seperti biru atau ungu).
> - **JANGAN** membuat seluruh halaman atau background sidebar menjadi merah pekat. Warna merah adalah warna aksentuasi aksi utama (*Action Color*), bukan warna latar belakang canvas.
> - Hindari penggunaan gradien neon, bayangan hitam tebal, atau efek kaca (*glassmorphism*) yang berlebihan.

---

## 3. ASSET LOGO & ICONS RESMI

Seluruh aset resmi telah diverifikasi dan diekstraksi dari panduan merek resmi:

| Jenis Aset | Path Berkas Terverifikasi | Penggunaan |
| :--- | :--- | :--- |
| **Logo Horizontal (Transparent)** | `public/images/brand/eduvia-logo-horizontal-transparent.png` | Header desktop, navbar utama, faktur/laporan resmi. |
| **Logo Horizontal (Original)** | `public/images/brand/eduvia-logo-horizontal.png` | Tampilan berlatar belakang putih solid. |
| **Logo Vertical (Transparent)** | `public/images/brand/eduvia-logo-vertical-transparent.png` | Kartu login, splash screen, sidebar terpusat, dokumen cetak. |
| **Icon Mark (Red Isometric E)** | `public/images/brand/eduvia-icon-mark-transparent.png` | Ikon avatar kecil, favicon, sidebar collapsed, watermark. |
| **App Icon Light** | `public/images/brand/eduvia-app-icon-light.png` | Ikon aplikasi mobile berlatar terang. |
| **App Icon Dark** | `public/images/brand/eduvia-app-icon-dark.png` | Ikon aplikasi mobile berlatar merah pekat. |
| **Browser Favicon** | `public/favicon.ico` dan `public/favicon.png` | Favicon resmi browser tab dan bookmark. |
| **Style Guide Reference** | `public/images/brand/eduvia-brand-style-guide.png` | Berkas sumber referensi panduan merek utuh. |

### Aturan Logo:
- Dilarang menggambar ulang (*redraw*), mengaproksimasi, atau mengganti logo resmi dengan teks/emoji.
- Titik pada huruf "i" di kata **Eduvia** berwarna **Merah (#E11D2E)**, mencerminkan identitas resmi.
- Proporsi logo harus selalu dipertahankan (*no stretching, no skewed distortion*).

---

## 4. SISTEM TIPOGRAFI

* **Font Utama:** `Plus Jakarta Sans`, dengan fallback `Inter, system-ui, -apple-system, sans-serif`.
* **Hierarki Teks:**
  * **H1 / Page Title:** 28px – 36px (desktop), 24px – 28px (mobile). Font Extrabold (`font-extrabold`), `text-[#1F2937]`, tracking-tight.
  * **H2 / Section Title:** 20px – 24px. Font Bold (`font-bold`), `text-[#1F2937]`.
  * **H3 / Card Header:** 16px – 18px. Font Semi-bold / Bold (`font-semibold` / `font-bold`).
  * **Body / Paragraf:** 14px – 15px. Line-height 1.6, `text-[#4B5563]`.
  * **Label / Kontrol:** 13px – 14px. Font Semi-bold (`font-semibold`), `text-[#1F2937]`.
  * **Metadata / Badge:** 11px – 12px. Font Medium (`font-medium`).

---

## 5. STANDAR KOMPONEN REUSABLE

### A. Tombol (`<x-button>`)
* **Primary CTA:** Background `#E11D2E`, Text `#FFFFFF`, Hover `#C81423`, Focus ring `#E11D2E`.
* **Secondary / Outline:** Background `#FFFFFF`, Border `#E5E7EB`, Text `#1F2937`, Hover Background `#F9FAFB`.
* **Destructive / Danger:** Background `#DC2626`, Text `#FFFFFF`, Hover `#B91C1C`.
* **Ghost:** Background `transparent`, Text `#4B5563`, Hover `#F3F4F6`, Text Hover `#1F2937`.

### B. Formulir Input (`<x-input>`, `<x-select>`, `<x-textarea>`)
* Normal: Background `#FFFFFF`, Border `1px solid #E5E7EB`, Text `#1F2937`, Placeholder `#9CA3AF`.
* Focus: `border-[#E11D2E]`, `ring-2 ring-[#E11D2E]/15`.
* Error: `border-[#DC2626]`, `ring-2 ring-[#DC2626]/15`, pesan error `#DC2626` dengan ikon penjelas.
* Disabled: Background `#F3F4F6`, Text `#9CA3AF`, cursor not-allowed.

### C. Kartu & Permukaan (`<x-card>`)
* Background: `#FFFFFF`.
* Border: `1px solid #E5E7EB`.
* Shadow: Halus dan ringan (`shadow-xs` atau `shadow-sm`).
* Radius: `rounded-2xl` (16px).
* Hover: Transisi halus elevasi mikro (`hover:border-[#E11D2E]/30 hover:shadow-md`).

### D. Navigasi Sidebar (Panel Dashboard)
* Background: `#FFFFFF`.
* Border Kanan: `1px solid #E5E7EB`.
* Nav Link Normal: Teks `#4B5563`, hover background `#F3F4F6`, hover teks `#1F2937`.
* Nav Link Aktif:
  * Background: `#FEF2F2`.
  * Teks: `#E11D2E` font semi-bold/bold.
  * Indikator Ikon: `#E11D2E`.

---

## 6. MATRIKS RESPONSIVITAS

Seluruh halaman dan komponen wajib diuji dan berfungsi tanpa *unintentional horizontal scrolling* pada:
- **320 px:** Ponsel layar kecil (tabel mengaktifkan horizontal scroll internal yang terisolasi).
- **360 – 430 px:** Smartphone standar (iPhone 12–15, Samsung Galaxy). Navigasi otomatis beralih ke mobile drawer dengan backdrop overlay.
- **768 px:** Tablet / iPad (layout 2 kolom).
- **1024 – 1440 px:** Laptop & Monitor Desktop (layout 3–4 kolom, sidebar paten).

---

## 7. ZERO BACKEND CHANGES POLICY

Seluruh standardisasi UI/UX **100% berfokus pada Frontend (Blade Views, CSS tokens, JS interaktivitas, dan aset visual)**.  
Jangan mengubah route, business logic controller, tabel database, migrasi, model, atau API backend yang sudah stabil.
