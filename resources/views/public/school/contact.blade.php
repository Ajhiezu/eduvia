@extends('layouts.school-public')

@section('content')

    <!-- Blueprint Crosshair SVG Helper Macro -->
    @php
        $crosshairSvg = '
            <div class="inline-grid grid-cols-3 gap-3 opacity-30 select-none pointer-events-none">
                <span class="w-2.5 h-2.5 flex items-center justify-center text-zinc-400 font-mono text-[10px] leading-none">+</span>
                <span class="w-2.5 h-2.5 flex items-center justify-center text-zinc-400 font-mono text-[10px] leading-none">+</span>
                <span class="w-2.5 h-2.5 flex items-center justify-center text-zinc-400 font-mono text-[10px] leading-none">+</span>
                <span class="w-2.5 h-2.5 flex items-center justify-center text-zinc-400 font-mono text-[10px] leading-none">+</span>
                <span class="w-2.5 h-2.5 flex items-center justify-center text-zinc-400 font-mono text-[10px] leading-none">+</span>
                <span class="w-2.5 h-2.5 flex items-center justify-center text-zinc-400 font-mono text-[10px] leading-none">+</span>
                <span class="w-2.5 h-2.5 flex items-center justify-center text-zinc-400 font-mono text-[10px] leading-none">+</span>
                <span class="w-2.5 h-2.5 flex items-center justify-center text-zinc-400 font-mono text-[10px] leading-none">+</span>
                <span class="w-2.5 h-2.5 flex items-center justify-center text-zinc-400 font-mono text-[10px] leading-none">+</span>
            </div>
        ';
    @endphp

    <div class="py-12 sm:py-16 lg:py-20 bg-[#F8F9FA] border-b border-zinc-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Breadcrumb Navigation -->
            <nav class="flex items-center gap-2 text-xs text-zinc-400 mb-8 font-sans">
                <a href="{{ $school->slug === 'nusantara-utama' ? route('home') : route('school.public.home', ['school' => $school->slug]) }}" class="hover:text-zinc-900 transition-colors">Beranda</a>
                <span>&rsaquo;</span>
                <span class="text-zinc-900 font-semibold">Kontak & Buku Tamu</span>
            </nav>

            <!-- Page Editorial Header -->
            <div class="mb-10 sm:mb-14 max-w-4xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#161F2E]/5 border border-[#161F2E]/10 text-xs font-semibold text-[#161F2E] tracking-wider uppercase mb-3 font-sans">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#161F2E]"></span>
                    Contact &amp; Inquiries
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-serif font-bold text-zinc-900 tracking-tight leading-tight">
                    Hubungi {{ $school->name }}
                </h1>
                <p class="mt-3 text-sm sm:text-base text-zinc-600 leading-relaxed font-sans max-w-3xl">
                    Punya pertanyaan mengenai program akademik, kurikulum terpadu (SD, SMP, SMA), pendaftaran siswa baru (PPDB), atau kemitraan institusi? Silakan kirimkan pesan Anda melalui formulir di bawah ini. Tim administrasi kami akan segera merespons.
                </p>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">

                <!-- Left Column: School Contact & Location (5 cols) -->
                <div class="lg:col-span-5 space-y-6">

                    <!-- Dark Navy Institutional Secretariat Card -->
                    <div class="bg-[#161F2E] text-white rounded-3xl p-7 sm:p-9 shadow-xl relative overflow-hidden space-y-6" style="background-color: #161F2E; color: #ffffff;">
                        <div class="absolute top-6 right-6 opacity-20">
                            {!! $crosshairSvg !!}
                        </div>

                        <div>
                            <span class="text-[10px] font-bold tracking-widest uppercase text-zinc-400 block mb-2 font-sans">
                                SECRETARIAT OFFICE
                            </span>
                            <h2 class="text-2xl font-serif font-bold text-white leading-snug">
                                Informasi Sekretariat Yayasan &amp; Sekolah
                            </h2>
                        </div>

                        <div class="space-y-5 text-xs sm:text-sm font-sans pt-2">
                            <!-- Address -->
                            <div class="flex items-start gap-3.5">
                                <div class="w-9 h-9 rounded-xl bg-white/10 text-white flex items-center justify-center shrink-0 mt-0.5 border border-white/10">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 block">Alamat Kampus</span>
                                    <p class="text-zinc-200 mt-0.5 leading-relaxed">
                                        {{ $school->address ?? 'Alamat sekolah resmi terdaftar' }}<br>
                                        {{ $school->city ?? '' }}, {{ $school->province ?? '' }} {{ $school->postal_code ?? '' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Phone -->
                            @if ($school->phone)
                                <div class="flex items-start gap-3.5">
                                    <div class="w-9 h-9 rounded-xl bg-white/10 text-white flex items-center justify-center shrink-0 mt-0.5 border border-white/10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 block">Telepon & Kontak</span>
                                        <a href="tel:{{ $school->phone }}" class="text-white font-medium hover:underline mt-0.5 block">
                                            {{ $school->phone }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <!-- Email -->
                            @if ($school->email)
                                <div class="flex items-start gap-3.5">
                                    <div class="w-9 h-9 rounded-xl bg-white/10 text-white flex items-center justify-center shrink-0 mt-0.5 border border-white/10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 block">Surat Elektronik (Email)</span>
                                        <a href="mailto:{{ $school->email }}" class="text-white font-medium hover:underline mt-0.5 block break-all">
                                            {{ $school->email }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <!-- Service Hours -->
                            <div class="flex items-start gap-3.5">
                                <div class="w-9 h-9 rounded-xl bg-white/10 text-white flex items-center justify-center shrink-0 mt-0.5 border border-white/10">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 block">Jam Pelayanan Kantor</span>
                                    <p class="text-zinc-200 mt-0.5">
                                        Senin – Jumat: 07:30 – 15:30 WIB<br>
                                        <span class="text-zinc-400 text-xs">Sabtu, Minggu & Hari Libur Nasional: Tutup</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-5 border-t border-zinc-700/60 flex items-center justify-between text-xs text-zinc-400 font-sans">
                            <span>Layanan Administrasi Terpadu</span>
                            <span class="text-white font-semibold">{{ $school->short_name ?: $school->name }}</span>
                        </div>
                    </div>

                    <!-- Google Maps Embed if available -->
                    @if (!empty($setting->maps_embed_url))
                        <div class="bg-white rounded-3xl p-5 border border-zinc-200/90 shadow-sm overflow-hidden">
                            <span class="text-xs font-bold text-zinc-700 block mb-3 uppercase tracking-wider font-sans">Peta Lokasi Kampus</span>
                            <div class="aspect-video w-full rounded-2xl overflow-hidden bg-zinc-100 border border-zinc-200">
                                <iframe
                                    src="{{ $setting->maps_embed_url }}"
                                    width="100%"
                                    height="100%"
                                    style="border:0;"
                                    allowfullscreen=""
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                ></iframe>
                            </div>
                        </div>
                    @endif

                    <!-- Privacy & Data Commitment Banner -->
                    <div class="bg-white rounded-3xl p-6 border border-zinc-200/90 shadow-sm space-y-2">
                        <div class="flex items-center gap-2 text-zinc-900 font-bold text-xs uppercase tracking-wider font-sans">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Jaminan Privasi & Keamanan Data</span>
                        </div>
                        <p class="text-xs text-zinc-500 leading-relaxed font-sans">
                            Seluruh data kontak dan isi pesan Anda dilindungi oleh platform Eduvia dan hanya digunakan oleh staf resmi {{ $school->name }} untuk menjawab permohonan informasi Anda tanpa dipindahtangankan ke pihak luar.
                        </p>
                    </div>

                </div>

                <!-- Right Column: Interactive Contact Form (7 cols) -->
                <div class="lg:col-span-7">
                    <div class="bg-white rounded-3xl p-7 sm:p-10 border border-zinc-200/90 shadow-sm">

                        @if (session('contact_success'))
                            <!-- Success Feedback Card -->
                            <div class="py-10 px-4 text-center space-y-5">
                                <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto border border-emerald-200 shadow-sm">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div class="space-y-1.5">
                                    <h3 class="text-2xl sm:text-3xl font-serif font-normal text-zinc-900">Pesan Berhasil Dikirim!</h3>
                                    <p class="text-xs sm:text-sm text-zinc-500 max-w-md mx-auto leading-relaxed font-sans">
                                        Terima kasih telah menghubungi {{ $school->name }}. Pesan Anda telah tersimpan dan tim administrasi sekolah akan segera meninjau pesan Anda.
                                    </p>
                                </div>

                                @if (session('reference_number'))
                                    <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-5 max-w-sm mx-auto">
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 block font-sans">Nomor Referensi Pesan Anda</span>
                                        <span class="font-mono text-xl sm:text-2xl font-bold text-zinc-900 tracking-wider block mt-1">
                                            {{ session('reference_number') }}
                                        </span>
                                        <span class="text-[11px] text-zinc-400 block mt-1 font-sans">Simpan kode ini jika ingin mengonfirmasi status pesan.</span>
                                    </div>
                                @endif

                                <div class="pt-4">
                                    <a 
                                        href="{{ route('school.public.contact', ['school' => $school->slug]) }}" 
                                        class="inline-flex items-center justify-center px-7 py-3.5 rounded-full text-xs font-semibold bg-[#161F2E] hover:bg-black text-white shadow-sm transition-all duration-200"
                                    >
                                        <span>Kirim Pesan Lainnya &rarr;</span>
                                    </a>
                                </div>
                            </div>

                        @else
                            <!-- The Form -->
                            <div class="mb-8 pb-6 border-b border-zinc-100">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 block mb-1 font-sans">INQUIRY FORM</span>
                                <h2 class="text-2xl sm:text-3xl font-serif font-normal text-zinc-900">Formulir Pesan & Pertanyaan</h2>
                                <p class="text-xs text-zinc-500 mt-1.5 font-sans">
                                    Kolom dengan tanda bintang (<span class="text-rose-500 font-bold">*</span>) wajib diisi dengan informasi yang valid.
                                </p>
                            </div>

                            @if ($errors->any())
                                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-sans">
                                    <strong class="font-bold block mb-1">Terdapat kesalahan pada isian formulir:</strong>
                                    <ul class="list-disc list-inside space-y-0.5">
                                        @foreach ($errors->all() as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form
                                method="POST"
                                action="{{ route('school.public.contact.submit', ['school' => $school->slug]) }}"
                                class="space-y-5"
                                x-data="{ submitting: false }"
                                @submit="submitting = true"
                            >
                                @csrf

                                <!-- Anti-spam honeypot (hidden from real users) -->
                                <div style="display:none !important;" aria-hidden="true">
                                    <label for="hp_website">Website</label>
                                    <input type="text" id="hp_website" name="hp_website" value="" tabindex="-1" autocomplete="off">
                                </div>

                                <!-- Name & Email (2 cols) -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 font-sans">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-zinc-700 mb-1.5">
                                            Nama Lengkap <span class="text-rose-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="name"
                                            name="name"
                                            value="{{ old('name') }}"
                                            required
                                            maxlength="150"
                                            placeholder="Masukkan nama lengkap Anda"
                                            class="w-full text-base rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 min-h-[44px] h-[44px] text-zinc-900 placeholder:text-zinc-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition-all"
                                        >
                                        @error('name')
                                            <p class="text-[13px] font-medium text-rose-600 mt-1.5">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="email" class="block text-sm font-medium text-zinc-700 mb-1.5">
                                            Alamat Email <span class="text-rose-500">*</span>
                                        </label>
                                        <input
                                            type="email"
                                            id="email"
                                            name="email"
                                            value="{{ old('email') }}"
                                            required
                                            maxlength="150"
                                            placeholder="nama@email.com"
                                            class="w-full text-base rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 min-h-[44px] h-[44px] text-zinc-900 placeholder:text-zinc-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition-all"
                                        >
                                        @error('email')
                                            <p class="text-[13px] font-medium text-rose-600 mt-1.5">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Phone & Category (2 cols) -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 font-sans">
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-zinc-700 mb-1.5">
                                            Nomor Telepon / WhatsApp <span class="text-zinc-400 font-normal">(Opsional)</span>
                                        </label>
                                        <input
                                            type="tel"
                                            id="phone"
                                            name="phone"
                                            value="{{ old('phone') }}"
                                            maxlength="30"
                                            placeholder="Contoh: 08123456789"
                                            class="w-full text-base rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 min-h-[44px] h-[44px] text-zinc-900 placeholder:text-zinc-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition-all"
                                        >
                                        @error('phone')
                                            <p class="text-[13px] font-medium text-rose-600 mt-1.5">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="category" class="block text-sm font-medium text-zinc-700 mb-1.5">
                                            Kategori Pesan <span class="text-rose-500">*</span>
                                        </label>
                                        <select
                                            id="category"
                                            name="category"
                                            required
                                            class="w-full text-base rounded-lg border border-zinc-200 bg-white pl-3.5 pr-11 py-2.5 min-h-[44px] h-[44px] text-zinc-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition-all"
                                        >
                                            <option value="" disabled {{ old('category') ? '' : 'selected' }}>Pilih kategori pesan...</option>
                                            @foreach (\App\Models\SchoolMessage::CATEGORIES as $key => $label)
                                                <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category')
                                            <p class="text-[13px] font-medium text-rose-600 mt-1.5">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Subject -->
                                <div class="font-sans">
                                    <label for="subject" class="block text-sm font-medium text-zinc-700 mb-1.5">
                                        Subjek Pesan <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="subject"
                                        name="subject"
                                        value="{{ old('subject') }}"
                                        required
                                        maxlength="200"
                                        placeholder="Tuliskan pokok atau topik pesan Anda"
                                        class="w-full text-base rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 min-h-[44px] h-[44px] text-zinc-900 placeholder:text-zinc-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition-all"
                                    >
                                    @error('subject')
                                        <p class="text-[13px] font-medium text-rose-600 mt-1.5">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Message Content -->
                                <div class="font-sans">
                                    <label for="message" class="block text-sm font-medium text-zinc-700 mb-1.5">
                                        Isi Pesan <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea
                                        id="message"
                                        name="message"
                                        rows="5"
                                        required
                                        maxlength="3000"
                                        placeholder="Tuliskan pertanyaan, tanggapan, atau kebutuhan informasi Anda secara jelas..."
                                        class="w-full text-base rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 min-h-[120px] text-zinc-900 placeholder:text-zinc-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition-all leading-relaxed"
                                    >{{ old('message') }}</textarea>
                                    <div class="flex items-center justify-between text-[13px] text-zinc-500 mt-1.5">
                                        <span>Teks biasa &bull; Maksimal 3.000 karakter</span>
                                    </div>
                                    @error('message')
                                        <p class="text-[13px] font-medium text-rose-600 mt-1.5">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Privacy Notice Agreement -->
                                <div class="pt-2 font-sans">
                                    <label class="flex items-start gap-3 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            name="privacy_consent"
                                            value="1"
                                            required
                                            {{ old('privacy_consent') ? 'checked' : '' }}
                                            class="mt-1 w-4 h-4 rounded border-zinc-300 text-[#161F2E] focus:ring-[#161F2E]"
                                        >
                                        <span class="text-xs text-zinc-600 leading-relaxed">
                                            Saya menyetujui bahwa data nama, email, dan pesan yang saya kirimkan dapat disimpan dan diproses oleh pihak <strong>{{ $school->name }}</strong> untuk keperluan komunikasi dan penanganan korespondensi resmi.
                                        </span>
                                    </label>
                                    @error('privacy_consent')
                                        <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Submit Action -->
                                <div class="pt-6 border-t border-zinc-100 flex flex-col sm:flex-row items-center justify-between gap-4 font-sans">
                                    <span class="text-xs text-zinc-400 hidden sm:inline">
                                        Pesan Anda akan langsung masuk ke kotak masuk sekretariat sekolah.
                                    </span>
                                    <button
                                        type="submit"
                                        :disabled="submitting"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-full text-xs sm:text-sm font-semibold bg-[#161F2E] hover:bg-black text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed"
                                        style="background-color: #161F2E; color: #ffffff;"
                                    >
                                        <template x-if="!submitting">
                                            <span class="flex items-center gap-2">
                                                <span>Kirim Pesan Sekarang</span>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                            </span>
                                        </template>
                                        <template x-if="submitting">
                                            <span class="flex items-center gap-2">
                                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                                <span>Sedang Mengirim...</span>
                                            </span>
                                        </template>
                                    </button>
                                </div>

                            </form>
                        @endif

                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection
