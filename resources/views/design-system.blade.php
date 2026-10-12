<x-layouts.guest>
    <x-slot:title>
        Eduvia Design System & Component Foundation
    </x-slot:title>

    <div class="py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-12">

        <!-- Page Header -->
        <div class="border-b border-slate-200 pb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-brand-600 mb-2">
                    <span class="w-2 h-2 rounded-full bg-brand-600"></span>
                    <span>Frontend Foundation (Fase 0)</span>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                    Eduvia UI Design System & Component Library
                </h1>
                <p class="text-sm text-slate-600 mt-1 max-w-2xl leading-relaxed">
                    Katalog komponen antarmuka terpadu untuk platform e-learning sekolah Indonesia. Berorientasi pada keterbacaan, aksesibilitas kontras, dan interaktivitas mulus di semua ukuran layar.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <x-button onclick="window.toast('Contoh toast notifikasi sukses berhasil dipicu!', 'success')" size="sm" variant="outline">
                    Picu Toast Sukses
                </x-button>
                <x-button onclick="window.toast('Contoh notifikasi peringatan sistem!', 'warning')" size="sm" variant="outline">
                    Picu Toast Warning
                </x-button>
            </div>
        </div>

        <!-- 1. Color Tokens & Theme System -->
        <section class="space-y-4">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <span class="w-1.5 h-4 rounded-full bg-brand-600"></span>
                <span>1. Design Tokens — Color Palette & Hierarchy</span>
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 text-xs">
                <div class="p-3 rounded-xl bg-brand-600 text-white shadow-xs">
                    <div class="font-bold">Brand Primary</div>
                    <div class="text-[10px] opacity-80 mt-1 font-mono">#2563EB (600)</div>
                </div>
                <div class="p-3 rounded-xl bg-brand-800 text-white shadow-xs">
                    <div class="font-bold">Brand Deep Navy</div>
                    <div class="text-[10px] opacity-80 mt-1 font-mono">#1E40AF (800)</div>
                </div>
                <div class="p-3 rounded-xl bg-accent-500 text-slate-950 shadow-xs">
                    <div class="font-bold">Gold Accent</div>
                    <div class="text-[10px] opacity-80 mt-1 font-mono">#F59E0B (500)</div>
                </div>
                <div class="p-3 rounded-xl bg-emerald-600 text-white shadow-xs">
                    <div class="font-bold">Success Green</div>
                    <div class="text-[10px] opacity-80 mt-1 font-mono">#10B981 (600)</div>
                </div>
                <div class="p-3 rounded-xl bg-rose-600 text-white shadow-xs">
                    <div class="font-bold">Danger Rose</div>
                    <div class="text-[10px] opacity-80 mt-1 font-mono">#E11D48 (600)</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-900 text-white shadow-xs">
                    <div class="font-bold">Neutral Text</div>
                    <div class="text-[10px] opacity-80 mt-1 font-mono">#0F172A (Slate)</div>
                </div>
            </div>
        </section>

        <!-- 2. Buttons -->
        <section class="space-y-4">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <span class="w-1.5 h-4 rounded-full bg-brand-600"></span>
                <span>2. Reusable Buttons (`x-button`)</span>
            </h2>

            <x-card padding>
                <div class="space-y-4">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2.5">Variasi Warna (Variants)</h4>
                        <div class="flex flex-wrap gap-2.5 items-center">
                            <x-button variant="primary">Primary Button</x-button>
                            <x-button variant="secondary">Secondary Button</x-button>
                            <x-button variant="outline">Outline Button</x-button>
                            <x-button variant="accent">Accent Gold</x-button>
                            <x-button variant="success">Success Button</x-button>
                            <x-button variant="danger">Danger Button</x-button>
                            <x-button variant="ghost">Ghost Button</x-button>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2.5">Ukuran (Sizes) & Keadaan (States)</h4>
                        <div class="flex flex-wrap gap-2.5 items-center">
                            <x-button size="sm">Small (sm)</x-button>
                            <x-button size="md">Medium (md)</x-button>
                            <x-button size="lg">Large (lg)</x-button>
                            <x-button disabled>Disabled State</x-button>
                            <x-button loading>Loading State</x-button>
                        </div>
                    </div>
                </div>
            </x-card>
        </section>

        <!-- 3. Form Controls & Validation -->
        <section class="space-y-4">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <span class="w-1.5 h-4 rounded-full bg-brand-600"></span>
                <span>3. Form Controls (`x-input`, `x-select`, `x-textarea`)</span>
            </h2>

            <x-card padding>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Standard Input with Icon -->
                    <x-input
                        label="Nama Lengkap Siswa"
                        name="demo_nama"
                        placeholder="Contoh: Muhammad Rizki"
                        required
                        hint="Gunakan nama resmi yang tercantum di ijazah/akta."
                    >
                        <x-slot:prefixIcon>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7 7z"/>
                            </svg>
                        </x-slot:prefixIcon>
                    </x-input>

                    <!-- Input with Error State -->
                    <x-input
                        label="Nomor Induk Siswa Nasional (NISN)"
                        name="demo_nisn"
                        value="12345"
                        error="NISN harus terdiri dari tepat 10 digit angka resmi Kemendikbud."
                        required
                    />

                    <!-- Select Control -->
                    <x-select
                        label="Jenjang Pendidikan Sekolah"
                        name="demo_jenjang"
                        required
                        hint="Pilih tingkatan institusi yang sesuai."
                    >
                        <option value="">Pilih Jenjang...</option>
                        <option value="sd">Sekolah Dasar / MI</option>
                        <option value="smp">Sekolah Menengah Pertama / MTs</option>
                        <option value="sma">Sekolah Menengah Atas / MA</option>
                        <option value="smk">Sekolah Menengah Kejuruan / MAK</option>
                    </x-select>

                    <!-- Disabled Input -->
                    <x-input
                        label="Kode Registrasi Sistem (Read Only)"
                        name="demo_code"
                        value="EDUVIA-REG-2026-F0"
                        disabled
                        hint="Kode dibuat otomatis oleh arsitektur platform."
                    />

                    <!-- Textarea Control -->
                    <div class="md:col-span-2">
                        <x-textarea
                            label="Catatan Pengantar Akademik"
                            name="demo_notes"
                            rows="2"
                            placeholder="Tuliskan deskripsi atau catatan tambahan..."
                            hint="Maksimum 500 karakter."
                        />
                    </div>
                </div>
            </x-card>
        </section>

        <!-- 4. Badges & Status Chips -->
        <section class="space-y-4">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <span class="w-1.5 h-4 rounded-full bg-brand-600"></span>
                <span>4. Status Badges (`x-badge`)</span>
            </h2>

            <x-card padding>
                <div class="space-y-4">
                    <div class="flex flex-wrap gap-2.5 items-center">
                        <x-badge variant="primary" dot>Super Admin</x-badge>
                        <x-badge variant="primary">Admin Sekolah</x-badge>
                        <x-badge variant="success" dot>Guru / Pengajar (Aktif)</x-badge>
                        <x-badge variant="warning" dot>Siswa (Dalam Ujian)</x-badge>
                        <x-badge variant="accent">Prestasi Emas</x-badge>
                        <x-badge variant="danger" dot>Akun Non-Aktif</x-badge>
                        <x-badge variant="neutral">Orang Tua / Wali</x-badge>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex flex-wrap gap-2.5 items-center">
                        <x-badge variant="primary" size="sm">Small Badge (sm)</x-badge>
                        <x-badge variant="primary" size="md">Medium Badge (md)</x-badge>
                    </div>
                </div>
            </x-card>
        </section>

        <!-- 5. Alerts & Feedback -->
        <section class="space-y-4">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <span class="w-1.5 h-4 rounded-full bg-brand-600"></span>
                <span>5. Feedback Alerts (`x-alert`)</span>
            </h2>

            <div class="space-y-3">
                <x-alert variant="info" title="Pembaruan Fondasi Fase 0" dismissible>
                    Konfigurasi autentikasi, role middleware, dan design tokens telah aktif dan diverifikasi siap digunakan.
                </x-alert>

                <x-alert variant="success" title="Koneksi Basis Data Normal" dismissible>
                    Database MySQL telah terhubung dengan status sehat dan transaksi terisolasi secara aman.
                </x-alert>

                <x-alert variant="warning" title="Peringatan Uji Coba" dismissible>
                    Pastikan tidak mengimplementasikan modul bisnis Fase 1 sebelum pengujian Fase 0 sepenuhnya lulus.
                </x-alert>

                <x-alert variant="danger" title="Akses Ditolak (403 Forbidden)" dismissible>
                    Peran Anda tidak memiliki izin otorisasi untuk mengakses sumber daya ini.
                </x-alert>
            </div>
        </section>

        <!-- 6. Interactive Modal Dialog -->
        <section class="space-y-4">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <span class="w-1.5 h-4 rounded-full bg-brand-600"></span>
                <span>6. Dialog Modal Interaktif (`x-modal`)</span>
            </h2>

            <x-card padding>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Modal berbasis Alpine.js dengan transisi halus, backdrop blur, pencegahan scroll, dan penutupan otomatis menggunakan tombol <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded text-[10px] font-mono">ESC</kbd>.
                </p>

                <div class="flex flex-wrap gap-3">
                    <x-button
                        type="button"
                        variant="primary"
                        x-data
                        @click="$dispatch('open-modal', 'demo-modal')"
                    >
                        Buka Contoh Modal Dialog
                    </x-button>

                    <x-button
                        type="button"
                        variant="danger"
                        x-data
                        @click="$dispatch('open-modal', 'confirm-modal')"
                    >
                        Buka Modal Konfirmasi
                    </x-button>
                </div>
            </x-card>

            <!-- Demo Modal Instance 1 -->
            <x-modal name="demo-modal" title="Konfirmasi Fondasi Aplikasi Eduvia" maxWidth="md">
                <p class="text-slate-600 mb-3 text-xs leading-relaxed">
                    Komponen modal dialog ini mendukung aksesibilitas keyboard (fokus tertahan & tombol ESC untuk keluar) serta tombol aksi di bagian bawah.
                </p>
                <x-input
                    label="Nama Uji Coba Komponen"
                    name="modal_test_name"
                    value="Verifikasi Desain Komponen"
                    disabled
                />
                <x-slot:footer>
                    <x-button
                        type="button"
                        variant="outline"
                        size="sm"
                        x-data
                        @click="$dispatch('close-modal', 'demo-modal')"
                    >
                        Batal
                    </x-button>
                    <x-button
                        type="button"
                        variant="primary"
                        size="sm"
                        x-data
                        @click="$dispatch('close-modal', 'demo-modal'); window.toast('Aksi modal berhasil dieksekusi!', 'success')"
                    >
                        Simpan Perubahan
                    </x-button>
                </x-slot:footer>
            </x-modal>

            <!-- Demo Modal Instance 2: Confirm -->
            <x-modal name="confirm-modal" title="Konfirmasi Tindakan Sensitif" maxWidth="sm">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Apakah Anda yakin ingin melanjutkan tindakan ini? Pola dialog ini digunakan untuk verifikasi sebelum perubahan kritis dieksekusi.
                        </p>
                    </div>
                </div>
                <x-slot:footer>
                    <x-button
                        type="button"
                        variant="outline"
                        size="sm"
                        x-data
                        @click="$dispatch('close-modal', 'confirm-modal')"
                    >
                        Batalkan
                    </x-button>
                    <x-button
                        type="button"
                        variant="danger"
                        size="sm"
                        x-data
                        @click="$dispatch('close-modal', 'confirm-modal'); window.toast('Tindakan konfirmasi berhasil.', 'danger')"
                    >
                        Lanjutkan
                    </x-button>
                </x-slot:footer>
            </x-modal>
        </section>

        <!-- 7. Data Tables & Pagination -->
        <section class="space-y-4">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <span class="w-1.5 h-4 rounded-full bg-brand-600"></span>
                <span>7. Data Table & Pagination (`x-table`, `x-pagination`)</span>
            </h2>

            <x-table>
                <x-slot:header>
                    <th scope="col" class="px-5 py-3">Nama Akun Uji</th>
                    <th scope="col" class="px-5 py-3">Email Terdaftar</th>
                    <th scope="col" class="px-5 py-3">Peran (Role)</th>
                    <th scope="col" class="px-5 py-3">Status</th>
                    <th scope="col" class="px-5 py-3 text-right">Aksi</th>
                </x-slot:header>

                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-5 py-3.5 font-bold text-slate-900">Super Administrator</td>
                    <td class="px-5 py-3.5 font-mono text-xs text-slate-500">superadmin@eduvia.test</td>
                    <td class="px-5 py-3.5"><x-badge variant="danger" size="sm">Super Admin</x-badge></td>
                    <td class="px-5 py-3.5"><x-badge variant="success" size="sm" dot>Aktif</x-badge></td>
                    <td class="px-5 py-3.5 text-right"><x-button size="sm" variant="ghost">Detail</x-button></td>
                </tr>

                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-5 py-3.5 font-bold text-slate-900">Admin Sekolah Nusantara</td>
                    <td class="px-5 py-3.5 font-mono text-xs text-slate-500">admin.sekolah@eduvia.test</td>
                    <td class="px-5 py-3.5"><x-badge variant="primary" size="sm">Admin Sekolah</x-badge></td>
                    <td class="px-5 py-3.5"><x-badge variant="success" size="sm" dot>Aktif</x-badge></td>
                    <td class="px-5 py-3.5 text-right"><x-button size="sm" variant="ghost">Detail</x-button></td>
                </tr>

                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-5 py-3.5 font-bold text-slate-900">Budi Santoso, S.Pd.</td>
                    <td class="px-5 py-3.5 font-mono text-xs text-slate-500">guru@eduvia.test</td>
                    <td class="px-5 py-3.5"><x-badge variant="success" size="sm">Guru / Pengajar</x-badge></td>
                    <td class="px-5 py-3.5"><x-badge variant="success" size="sm" dot>Aktif</x-badge></td>
                    <td class="px-5 py-3.5 text-right"><x-button size="sm" variant="ghost">Detail</x-button></td>
                </tr>

                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-5 py-3.5 font-bold text-slate-900">Ahmad Fauzi</td>
                    <td class="px-5 py-3.5 font-mono text-xs text-slate-500">siswa@eduvia.test</td>
                    <td class="px-5 py-3.5"><x-badge variant="warning" size="sm">Siswa</x-badge></td>
                    <td class="px-5 py-3.5"><x-badge variant="success" size="sm" dot>Aktif</x-badge></td>
                    <td class="px-5 py-3.5 text-right"><x-button size="sm" variant="ghost">Detail</x-button></td>
                </tr>

                <x-slot:footer>
                    <x-pagination :currentPage="1" :totalPages="3" :totalItems="15" :perPage="5" />
                </x-slot:footer>
            </x-table>
        </section>

        <!-- 8. Empty States & Loading Skeletons -->
        <section class="space-y-4">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <span class="w-1.5 h-4 rounded-full bg-brand-600"></span>
                <span>8. Empty State & Skeleton Loading (`x-empty-state`, `x-skeleton`)</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Empty State Component -->
                <x-empty-state
                    title="Katalog Pembelajaran Masih Kosong"
                    description="Modul mata pelajaran dan jadwal akan disediakan pada Fase 1 sesuai roadmap pengembangan."
                >
                    <x-slot:action>
                        <x-button size="sm" variant="outline" onclick="window.toast('Informasi roadmap telah dilihat.', 'info')">
                            Pelajari Roadmap Fase 1 &rarr;
                        </x-button>
                    </x-slot:action>
                </x-empty-state>

                <!-- Skeleton Loading Component -->
                <x-card title="Contoh Skeleton Loading State" subtitle="Digunakan saat data asinkronus sedang dimuat">
                    <x-skeleton type="card" />
                </x-card>
            </div>
        </section>

        <!-- 9. Responsive Testing Viewport Guide -->
        <section class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <span class="w-1.5 h-4 rounded-full bg-brand-600"></span>
                <span>9. Matriks Pengujian Responsif (Responsive Matrix)</span>
            </h2>

            <p class="text-xs text-slate-600 leading-relaxed">
                Fondasi antarmuka Eduvia telah dirancang dan diuji untuk menjamin tidak ada <em>unintentional horizontal overflow</em> pada seluruh skala breakpoint standar:
            </p>

            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2.5 text-center text-xs">
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="font-bold text-slate-800">320 px</div>
                    <div class="text-[10px] text-slate-500">Mobile Kecil</div>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="font-bold text-slate-800">360 px</div>
                    <div class="text-[10px] text-slate-500">Android Standar</div>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="font-bold text-slate-800">390 px</div>
                    <div class="text-[10px] text-slate-500">iPhone 12-15</div>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="font-bold text-slate-800">430 px</div>
                    <div class="text-[10px] text-slate-500">iPhone Pro Max</div>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="font-bold text-slate-800">768 px</div>
                    <div class="text-[10px] text-slate-500">Tablet / iPad</div>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="font-bold text-slate-800">1024 px</div>
                    <div class="text-[10px] text-slate-500">Laptop / Desktop</div>
                </div>
                <div class="p-2.5 rounded-xl bg-brand-50 border border-brand-200 text-brand-900 font-bold">
                    <div class="font-bold">1440 px</div>
                    <div class="text-[10px] text-brand-700">Widescreen HD</div>
                </div>
            </div>
        </section>

    </div>
</x-layouts.guest>
