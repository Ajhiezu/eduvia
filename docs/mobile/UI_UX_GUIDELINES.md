# EDUVIA MOBILE ECOSYSTEM — UI/UX DESIGN GUIDELINES
**Document ID:** `EDV-MOB-UIUX-001`  
**Version:** 1.0.0 (Tahap 0 Baseline)  
**Brand Identity SSOT:** Conforming to `docs/UI_UX_DESIGN_MEMORY.md`  
**Target Systems:** Eduvia Parent App & Eduvia Student App (iOS & Android)  

---

## 1. Identitas Merek & Nilai Visual (Brand Identity)

Eduvia adalah platform edukasi modern yang terpercaya, ramah untuk siswa, dan profesional untuk orang tua serta pihak sekolah.

* **Nama Produk:** **Eduvia**
* **Tagline Resmi:** **Learn · Grow · Build Your Future**
* **Karakter Desain:** Modern, Academic, Trustworthy, Clean, Ergonomic, Mobile-First.

### Palet Warna Resmi (Brand Colors)
| Token Nama | Nilai Hex | Penggunaan Utama | Aturan Bobot Visual |
| :--- | :--- | :--- | :--- |
| **Primary Red** | `#E11D2E` | Tombol CTA primer, indikator tab aktif, highlight selektif | 10% – 15% visual weight |
| **Primary Red Hover/Press** | `#C81423` | Pressed state tombol dan elemen interaktif primer | Interaktif |
| **Primary Red Dark** | `#9E0E1B` | Teks merah berbobot tebal, aksen kontras | Selektif |
| **Primary Red Subtle** | `#FEF2F2` | Background chip aktif, surface aktif item menu, badge merah | Background halus |
| **Primary Red Border** | `#FCA5A5` | Ring fokus input, border badge aktif | Border halus |
| **Dark Text (Primary)** | `#1F2937` | Heading, judul halaman, nama siswa, teks navigasi utama | Kontras tinggi (>7:1) |
| **Body Text (Secondary)** | `#4B5563` | Paragraf, instruksi tugas, sinopsis materi | Standar WCAG AA |
| **Muted Text** | `#6B7280` | Placeholder input, cap waktu, metadata sekunder | Kontras memadai |
| **Border Line** | `#E5E7EB` | Garis pembatas kartu, separator item daftar | 1px reguler |
| **Light Gray (Canvas)** | `#F3F4F6` | Background kanvas aplikasi (layar dasar) | Netral sejuk |
| **White (Main Surface)** | `#FFFFFF` | Background kartu, panel modal, bottom sheet, header | 70% – 80% dominan |

### Semantic Status Colors
* **Success:** `#10B981` (Surface: `#ECFDF5`, Border: `#A7F3D0`, Text: `#065F46`) — Hadir, Lulus, Lunas, Terkirim.
* **Warning:** `#F59E0B` (Surface: `#FFFBEB`, Border: `#FDE68A`, Text: `#92400E`) — Terlambat, Jatuh Tempo, Ragu-ragu (CBT).
* **Danger:** `#DC2626` (Surface: `#FEF2F2`, Border: `#FECACA`, Text: `#991B1B`) — Alpa, Kedaluwarsa, Belum Bayar, Error.
* **Info:** `#2563EB` (Surface: `#EFF6FF`, Border: `#BFDBFE`, Text: `#1E40AF`) — Pengumuman, Info Akademik, Terjadwal.

> [!WARNING]
> **Larangan Desain:**
> 1. Dilarang mewarnai seluruh background layar atau top header dengan warna merah pekat pekat yang melelahkan mata. Warna merah `#E11D2E` adalah *action accent color*.
> 2. Dilarang menggunakan gradien neon atau drop shadow hitam pekat. Gunakan soft shadow halus (`elevation: 2` di Android, `shadowOpacity: 0.06` di iOS).
> 3. Dilarang mengecilkan tampilan web (*responsive web iframe*) sebagai ganti antarmuka mobile native.

---

## 2. Aset Resmi & Logo

Aset resmi diambil dari direktori web terverifikasi `public/images/brand/`:
* **App Icon Light:** `public/images/brand/eduvia-app-icon-light.png`
* **App Icon Dark:** `public/images/brand/eduvia-app-icon-dark.png`
* **Logo Horizontal:** `public/images/brand/eduvia-logo-horizontal-transparent.png`
* **Logo Vertical:** `public/images/brand/eduvia-logo-vertical-transparent.png`
* **Icon Mark (Isometric E):** `public/images/brand/eduvia-icon-mark-transparent.png`

**Ketentuan Logo:**
- Titik pada huruf "i" pada kata **Eduvia** selalu berwarna **Merah (#E11D2E)**.
- Pertahankan proporsi aspect ratio asli tanpa peregangan vertikal atau horizontal.

---

## 3. Sistem Tipografi Mobile

* **Font Keluarga:** `Plus Jakarta Sans`, dengan fallback `Inter, system-ui, -apple-system, sans-serif`.
* **Skala Tipografi:**
  * **Large Display Title:** 24px – 28px, Bold / Extrabold, tracking-tight. Digunakan pada header selamat datang dasbor.
  * **Screen Header / H1:** 20px – 22px, Bold, color `#1F2937`.
  * **Section Title / H2:** 16px – 18px, Bold, color `#1F2937`.
  * **Card Header / H3:** 14px – 16px, Semi-bold.
  * **Body / Paragraf:** 13px – 14px, Regular / Medium, line-height 20px, color `#4B5563`.
  * **Form Label & Control:** 12px – 13px, Semi-bold, color `#1F2937`.
  * **Caption / Small Metadata:** 11px – 12px, Medium, color `#6B7280`.
  * **Tag / Badge Text:** 10px – 11px, Semi-bold, uppercase tracking-wide.

---

## 4. Pola Navigasi & Tata Letak (Layouts)

### A. Eduvia Parent App — Struktur Navigasi
1. **Top Bar Dinamis (Header):**
   - Menampilkan avatar orang tua, tombol notifikasi berpenghitung (badge), serta **Active Child Dropdown Switcher** yang menampilkan nama anak terpilih, kelas, dan jenjang.
2. **Bottom Navigation Bar (5 Tab Utama):**
   - 🏠 **Beranda (Home):** Dasbor ringkasan presensi, agenda hari ini, tugas aktif, dan pengumuman sekolah.
   - 🎓 **Akademik:** Jadwal mingguan, riwayat kehadiran detail, rekap tugas anak, dan E-Rapor resmi terbit.
   - 💳 **Keuangan:** Daftar tagihan SPP/formulir, rincian pembayaran, unggah bukti transfer, kuitansi digital.
   - 💬 **Pesan:** Komunikasi resmi dengan wali kelas atau guru mata pelajaran anak.
   - 👤 **Akun:** Profil orang tua, penautan kode anak baru, pengaturan notifikasi, ubah kata sandi, bantuan, dan tombol logout.

### B. Eduvia Student App — Struktur Navigasi
1. **Top Bar Siswa:**
   - Sambutan ramah (*"Halo, [Nama Siswa]!"*), tanggal aktif, avatar siswa, kelas, dan tombol lonceng pengumuman.
2. **Bottom Navigation Bar (5 Tab Belajar):**
   - 📚 **Belajar (Home):** Jadwal aktif saat ini, hitung mundur kelas selanjutnya, materi baru, dan pengingat tugas mendesak.
   - 📅 **Jadwal:** Kalender harian & mingguan, ruang kelas, dan jam pelajaran.
   - 📝 **Tugas:** Daftar tugas aktif, detail tugas, upload dokumen jawaban, dan status penilaian guru.
   - ✍️ **Ujian (CBT):** Daftar ujian terjadwal, instruksi, dan ruang pengerjaan soal ujian mobile.
   - 👤 **Profil:** Biodata siswa, NISN, NIS, kelas, rekap nilai, dan pengaturan akun.

---

## 5. Standar Komponen Mobile Reusable

### A. Tombol (`Button`)
* **Primary Button:** Background `#E11D2E`, Text `#FFFFFF`, font-weight 600, border-radius 12px, tinggi minimal 48pt (memenuhi standar sentuhan Apple/Google). State pressed: `#C81423`.
* **Secondary / Outline Button:** Background `#FFFFFF`, Border 1.5px `#E5E7EB`, Text `#1F2937`, radius 12px.
* **Ghost / Text Button:** Background transparan, Text `#E11D2E` atau `#4B5563`, radius 8px.
* **Disabled State:** Background `#F3F4F6`, Text `#9CA3AF`, tidak merespons sentuhan.

### B. Formulir Input (`TextInput`, `Select`, `SearchInput`)
* **Default State:** Background `#FFFFFF`, Border 1px `#E5E7EB`, Text `#1F2937`, Placeholder `#9CA3AF`, radius 12px, tinggi 48pt.
* **Focus State:** Border `#E11D2E`, Ring halus transparan (`#E11D2E/15`).
* **Error State:** Border `#DC2626`, teks keterangan error di bawah input berwarna merah dengan ikon tanda seru.
* **Keyboard Handling:** Terintegrasi dengan `KeyboardAvoidingView` agar input tidak tertutup keyboard pada layar kecil.

### C. Kartu Data (`Card`)
* Background: `#FFFFFF`.
* Border: 1px solid `#E5E7EB`.
* Radius: 16px (modern rounded).
* Padding: 16px.
* Shadow: Ringan dan menyebar halus (`elevation: 2` Android, `shadowColor: #000`, `shadowOpacity: 0.04`, `shadowRadius: 8` iOS).

### D. Status Wajib pada Setiap Layar (State Machine)
1. **Loading State:** Menggunakan shimmer placeholder skeleton yang menyerupai bentuk kartu asli, bukan hanya spinner layar penuh.
2. **Empty State:** Ilustrasi vektor ramah atau ikon outline tematik, judul penjelas (misal: *"Belum Ada Tugas Aktif"*), deskripsi singkat, dan tombol aksi jika relevan (*"Periksa Kembali Nanti"*).
3. **Error State:** Pesan kegagalan koneksi atau server yang jelas tanpa kode teknis rumit, disertai tombol **Coba Lagi (Retry)**.
4. **Success State:** Feedback visual (animasi centang hijau atau banner toast non-intrusif) setelah aksi penting berhasil.

---

## 6. Aksesibilitas & Responsivitas Perangkat

1. **Area Sentuh Minimal (Touch Targets):** Minimal 44 x 44 pt (iOS Human Interface Guidelines) dan 48 x 48 dp (Material Design) untuk setiap tombol, chip, atau tautan yang dapat diklik.
2. **Dukungan Safe Area:** Menggunakan `SafeAreaView` / `react-native-safe-area-context` pada seluruh layar untuk menghindari benturan dengan notch, dynamic island, kamera punch-hole, dan home indicator.
3. **Skalabilitas Teks:** Teks tidak boleh terpotong saat pengguna mengaktifkan font berukuran besar di pengaturan sistem perangkat (*dynamic font scale handling*).
