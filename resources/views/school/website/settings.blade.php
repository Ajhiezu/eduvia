@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-zinc-200">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('website.dashboard') }}" class="text-xs font-semibold text-zinc-500 hover:text-zinc-800">&larr; Kembali ke Ringkasan CMS</a>
            </div>
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">
                Pengaturan Website Publik & Profil Sekolah
            </h1>
            <p class="text-xs sm:text-sm text-zinc-500 mt-0.5">
                Konfigurasi URL publik, banner hero, visi & misi, sambutan kepala sekolah, akreditasi, dan SEO.
            </p>
        </div>
        <a href="{{ route('school.public.preview', ['school' => $school->slug]) }}" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-zinc-800 hover:bg-zinc-900 text-white transition-colors">
            <span>Pratinjau Draf</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <strong>Terdapat kesalahan pada formulir:</strong>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('website.settings.update') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Card 1: Domain, Slug & Ketersediaan Publik -->
        <div class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-2xs space-y-4">
            <h2 class="text-base font-bold text-zinc-900 flex items-center gap-2 border-b border-zinc-100 pb-3">
                <span class="w-2.5 h-2.5 rounded-full bg-[#E11D2E]"></span>
                <span>Alamat Publik & Akses Website</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">
                        Slug Alamat Website <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center">
                        <span class="px-3 py-2 bg-zinc-100 border border-r-0 border-zinc-200 text-xs text-zinc-500 rounded-l-xl select-none">
                            /s/
                        </span>
                        <input type="text" name="slug" value="{{ old('slug', $school->slug) }}" required pattern="^[a-z0-9]+(?:-[a-z0-9]+)*$" class="w-full px-3 py-2 text-xs rounded-r-xl border border-zinc-200 focus:outline-none focus:border-[#E11D2E] focus:ring-1 focus:ring-[#E11D2E] font-mono">
                    </div>
                    <span class="text-[11px] text-zinc-400 mt-1 block">Hanya huruf kecil, angka, dan tanda hubung (-). Contoh: <code>nusantara-utama</code></span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">
                        Status Keterlihatan Publik <span class="text-rose-500">*</span>
                    </label>
                    <select name="public_website_enabled" class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200 focus:outline-none focus:border-[#E11D2E] focus:ring-1 focus:ring-[#E11D2E]">
                        <option value="1" {{ old('public_website_enabled', $school->public_website_enabled) ? 'selected' : '' }}>
                            Aktif (Dapat diakses oleh publik tanpa login)
                        </option>
                        <option value="0" {{ !old('public_website_enabled', $school->public_website_enabled) ? 'selected' : '' }}>
                            Nonaktif (Hanya administrator yang dapat melihat pratinjau)
                        </option>
                    </select>
                    <span class="text-[11px] text-zinc-400 mt-1 block">Jika nonaktif, pengunjung anonim tidak dapat melihat data internal sekolah.</span>
                </div>
            </div>

            <!-- Theme Color Setting -->
            <div class="pt-4 border-t border-zinc-100">
                <label class="block text-xs font-bold text-zinc-700 mb-1">
                    Tema Warna Publik Sekolah (Brand Accent)
                </label>
                <div class="flex items-center gap-3">
                    <input type="color" id="theme_color_picker" value="{{ old('theme_color', $setting->theme_color ?: '#E11D2E') }}" onchange="document.getElementById('theme_color_input').value = this.value" class="w-10 h-10 p-0.5 rounded-xl border border-zinc-200 cursor-pointer">
                    <input type="text" id="theme_color_input" name="theme_color" value="{{ old('theme_color', $setting->theme_color ?: '#E11D2E') }}" pattern="^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$" placeholder="#E11D2E" oninput="if (/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/.test(this.value)) document.getElementById('theme_color_picker').value = this.value" class="w-36 px-3 py-2 text-xs rounded-xl border border-zinc-200 font-mono uppercase">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <button type="button" onclick="document.getElementById('theme_color_input').value = '#E11D2E'; document.getElementById('theme_color_picker').value = '#E11D2E';" class="w-6 h-6 rounded-full bg-[#E11D2E] border-2 border-white shadow-2xs hover:scale-110 transition-transform" title="Eduvia Merah"></button>
                        <button type="button" onclick="document.getElementById('theme_color_input').value = '#1D4ED8'; document.getElementById('theme_color_picker').value = '#1D4ED8';" class="w-6 h-6 rounded-full bg-[#1D4ED8] border-2 border-white shadow-2xs hover:scale-110 transition-transform" title="Royal Blue"></button>
                        <button type="button" onclick="document.getElementById('theme_color_input').value = '#059669'; document.getElementById('theme_color_picker').value = '#059669';" class="w-6 h-6 rounded-full bg-[#059669] border-2 border-white shadow-2xs hover:scale-110 transition-transform" title="Emerald"></button>
                        <button type="button" onclick="document.getElementById('theme_color_input').value = '#7C3AED'; document.getElementById('theme_color_picker').value = '#7C3AED';" class="w-6 h-6 rounded-full bg-[#7C3AED] border-2 border-white shadow-2xs hover:scale-110 transition-transform" title="Violet"></button>
                        <button type="button" onclick="document.getElementById('theme_color_input').value = '#0F766E'; document.getElementById('theme_color_picker').value = '#0F766E';" class="w-6 h-6 rounded-full bg-[#0F766E] border-2 border-white shadow-2xs hover:scale-110 transition-transform" title="Teal"></button>
                    </div>
                </div>
                <span class="text-[11px] text-zinc-400 mt-1 block">Warna aksen utama yang tampil pada tombol, badge, dan highlight navigasi halaman publik sekolah Anda.</span>
            </div>
        </div>

        <!-- Card 2: Banner Hero & Slogan Utama -->
        <div class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-2xs space-y-4">
            <h2 class="text-base font-bold text-zinc-900 flex items-center gap-2 border-b border-zinc-100 pb-3">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                <span>Banner Hero & Slogan Utama</span>
            </h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Judul / Headline Utama</label>
                    <input type="text" name="hero_headline" value="{{ old('hero_headline', $setting->hero_headline) }}" placeholder="Mewujudkan Generasi Unggul, Berkarakter..." class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200 focus:outline-none focus:border-[#E11D2E]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Subjudul / Deskripsi Pendukung</label>
                    <textarea name="hero_subheadline" rows="2" placeholder="Penjelasan singkat visi dan lingkungan belajar sekolah..." class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200 focus:outline-none focus:border-[#E11D2E]">{{ old('hero_subheadline', $setting->hero_subheadline) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">Teks Tombol CTA</label>
                        <input type="text" name="hero_cta_text" value="{{ old('hero_cta_text', $setting->hero_cta_text) }}" placeholder="Jelajahi Profil Sekolah" class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">Tautan / Target Tombol CTA</label>
                        <input type="text" name="hero_cta_url" value="{{ old('hero_cta_url', $setting->hero_cta_url) }}" placeholder="#profil atau /s/nama-sekolah/profile" class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">Tautan Video Profil (Opsional)</label>
                        <input type="url" name="hero_video_url" value="{{ old('hero_video_url', $setting->hero_video_url) }}" placeholder="https://youtube.com/watch?v=..." class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">Unggah Gambar Latar Hero (Maks 3MB)</label>
                        <input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp" class="w-full text-xs text-zinc-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200">
                        @if ($setting->heroImageUrl())
                            <div class="mt-2 flex items-center justify-between p-2 rounded-xl bg-zinc-50 border border-zinc-200">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $setting->heroImageUrl() }}" alt="Current Hero" class="w-20 h-10 object-cover rounded-lg border border-zinc-200">
                                    <span class="text-[11px] text-zinc-500 font-medium">Gambar aktif</span>
                                </div>
                                <label class="inline-flex items-center gap-1.5 text-xs text-rose-600 font-semibold cursor-pointer">
                                    <input type="checkbox" name="remove_hero_image" value="1" class="rounded border-zinc-300 text-[#E11D2E] focus:ring-[#E11D2E]">
                                    <span>Hapus gambar ini</span>
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Sambutan Kepala Sekolah -->
        <div class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-2xs space-y-4">
            <h2 class="text-base font-bold text-zinc-900 flex items-center gap-2 border-b border-zinc-100 pb-3">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <span>Sambutan Pimpinan / Kepala Sekolah</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Nama Lengkap & Gelar</label>
                    <input type="text" name="principal_name" value="{{ old('principal_name', $setting->principal_name) }}" placeholder="Drs. H. Mulyadi Kartodirdjo, M.Pd." class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Jabatan Resmi</label>
                    <input type="text" name="principal_title" value="{{ old('principal_title', $setting->principal_title) }}" placeholder="Kepala Sekolah SMP/SMA Nusantara Utama" class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Teks Sambutan</label>
                    <textarea name="welcome_message" rows="5" placeholder="Pesan sambutan resmi untuk menyambut siswa baru dan wali murid..." class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200 leading-relaxed">{{ old('welcome_message', $setting->welcome_message) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Foto Formal Kepala Sekolah</label>
                    <input type="file" name="principal_photo" accept="image/jpeg,image/png,image/webp" class="w-full text-xs text-zinc-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200">
                    @if ($setting->principalPhotoUrl())
                        <div class="mt-2 flex items-center justify-between p-2 rounded-xl bg-zinc-50 border border-zinc-200">
                            <div class="flex items-center gap-2">
                                <img src="{{ $setting->principalPhotoUrl() }}" alt="Current Photo" class="w-10 h-12 object-cover rounded-lg border border-zinc-200">
                                <span class="text-[11px] text-zinc-500 font-medium">Foto aktif</span>
                            </div>
                            <label class="inline-flex items-center gap-1.5 text-xs text-rose-600 font-semibold cursor-pointer">
                                <input type="checkbox" name="remove_principal_photo" value="1" class="rounded border-zinc-300 text-[#E11D2E] focus:ring-[#E11D2E]">
                                <span>Hapus foto ini</span>
                            </label>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card 4: Visi, Misi & Sejarah -->
        <div class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-2xs space-y-4">
            <h2 class="text-base font-bold text-zinc-900 flex items-center gap-2 border-b border-zinc-100 pb-3">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span>Visi, Misi & Sejarah Pendirian</span>
            </h2>

            <div>
                <label class="block text-xs font-bold text-zinc-700 mb-1">Visi Sekolah</label>
                <textarea name="vision" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200 leading-relaxed">{{ old('vision', $setting->vision) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-zinc-700 mb-1">
                    Misi Sekolah (Tulis 1 poin misi per baris)
                </label>
                @php
                    $missionText = is_array($setting->mission) ? implode("\n", $setting->mission) : ($setting->mission ?? '');
                @endphp
                <textarea name="mission" rows="4" class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200 leading-relaxed font-sans" placeholder="Misi 1&#10;Misi 2&#10;Misi 3">{{ old('mission', $missionText) }}</textarea>
                <span class="text-[11px] text-zinc-400 mt-1 block">Setiap baris baru akan otomatis dijadikan satu nomor butir misi.</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-zinc-700 mb-1">Sejarah Singkat Lembaga</label>
                <textarea name="history" rows="4" class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200 leading-relaxed">{{ old('history', $setting->history) }}</textarea>
            </div>
        </div>

        <!-- Card 5: Akreditasi & Kontak Peta -->
        <div class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-2xs space-y-4">
            <h2 class="text-base font-bold text-zinc-900 flex items-center gap-2 border-b border-zinc-100 pb-3">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                <span>Akreditasi, Peta & Media Sosial</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Predikat Akreditasi</label>
                    <input type="text" name="accreditation_grade" value="{{ old('accreditation_grade', $setting->accreditation_grade) }}" placeholder="Contoh: A (Unggul)" class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Nomor SK Akreditasi (Opsional)</label>
                    <input type="text" name="accreditation_no" value="{{ old('accreditation_no', $setting->accreditation_no) }}" placeholder="028/BAN-SM/SK/2023" class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-zinc-700 mb-1">Tautan Embed Google Maps (iframe src)</label>
                <input type="text" name="maps_embed_url" value="{{ old('maps_embed_url', $setting->maps_embed_url) }}" placeholder="https://www.google.com/maps/embed?pb=..." class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200 font-mono">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Facebook URL</label>
                    <input type="url" name="facebook_url" value="{{ old('facebook_url', $setting->social_links['facebook'] ?? '') }}" placeholder="https://facebook.com/..." class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Instagram URL</label>
                    <input type="url" name="instagram_url" value="{{ old('instagram_url', $setting->social_links['instagram'] ?? '') }}" placeholder="https://instagram.com/..." class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">YouTube URL</label>
                    <input type="url" name="youtube_url" value="{{ old('youtube_url', $setting->social_links['youtube'] ?? '') }}" placeholder="https://youtube.com/@..." class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200">
                </div>
            </div>
        </div>

        <!-- Card 6: SEO Metadata -->
        <div class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-2xs space-y-4">
            <h2 class="text-base font-bold text-zinc-900 flex items-center gap-2 border-b border-zinc-100 pb-3">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                <span>Optimasi Mesin Pencari (SEO & Meta Tags)</span>
            </h2>

            <div>
                <label class="block text-xs font-bold text-zinc-700 mb-1">Meta Description (Maks 300 Karakter)</label>
                <textarea name="meta_description" rows="2" maxlength="300" class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200">{{ old('meta_description', $setting->meta_description) }}</textarea>
                <span class="text-[11px] text-zinc-400 mt-1 block">Deskripsi yang muncul pada hasil pencarian Google & pratinjau tautan media sosial.</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-zinc-700 mb-1">Meta Kata Kunci (Keywords)</label>
                <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $setting->meta_keywords) }}" placeholder="SMP Terbaik Jakarta, PPDB Online..." class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200">
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200">
            <a href="{{ route('website.dashboard') }}" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-zinc-600 hover:bg-zinc-100 transition-colors">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C71827] text-white shadow-xs transition-colors">
                Simpan Perubahan Pengaturan
            </button>
        </div>

    </form>
</div>
@endsection
