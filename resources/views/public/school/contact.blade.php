@extends('layouts.school-public')

@section('content')
<div class="py-10 lg:py-16 bg-[#F8FAFC]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-xs text-zinc-500 mb-6">
            <a href="{{ $school->slug === 'nusantara-utama' ? route('home') : route('school.public.home', ['school' => $school->slug]) }}" class="hover:text-zinc-900 transition-colors">Beranda</a>
            <span>&rsaquo;</span>
            <span class="text-zinc-900 font-semibold">Kontak & Buku Tamu</span>
        </nav>

        <!-- Page Header -->
        <div class="mb-10 text-center max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-[#E11D2E] border border-rose-100 mb-3">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Layanan Informasi & Buku Tamu</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-zinc-900 tracking-tight">
                Hubungi {{ $school->name }}
            </h1>
            <p class="mt-3 text-sm sm:text-base text-zinc-600 leading-relaxed">
                Punya pertanyaan mengenai kegiatan akademik, ekstrakurikuler, pendaftaran siswa baru, atau ingin menyampaikan kritik dan saran? Silakan kirimkan pesan Anda melalui formulir di bawah ini.
            </p>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left Column: School Contact & Location (5 cols) -->
            <div class="lg:col-span-5 space-y-6">

                <!-- Institutional Card -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-2xs">
                    <h2 class="text-base font-bold text-zinc-900 mb-4 pb-3 border-b border-zinc-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#E11D2E]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Informasi Sekretariat Sekolah</span>
                    </h2>

                    <div class="space-y-4 text-xs sm:text-sm">
                        <!-- Address -->
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-rose-50 text-[#E11D2E] flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <span class="font-bold text-zinc-800 block text-xs uppercase tracking-wider">Alamat Kampus</span>
                                <p class="text-zinc-600 mt-0.5 leading-relaxed">
                                    {{ $school->address ?? 'Alamat sekolah resmi' }}<br>
                                    {{ $school->city ?? '' }}, {{ $school->province ?? '' }} {{ $school->postal_code ?? '' }}
                                </p>
                            </div>
                        </div>

                        <!-- Phone -->
                        @if ($school->phone)
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-rose-50 text-[#E11D2E] flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                                <div>
                                    <span class="font-bold text-zinc-800 block text-xs uppercase tracking-wider">Telepon & Kontak</span>
                                    <a href="tel:{{ $school->phone }}" class="text-[#E11D2E] font-medium hover:underline mt-0.5 block">
                                        {{ $school->phone }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- Email -->
                        @if ($school->email)
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-rose-50 text-[#E11D2E] flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <span class="font-bold text-zinc-800 block text-xs uppercase tracking-wider">Surat Elektronik (Email)</span>
                                    <a href="mailto:{{ $school->email }}" class="text-[#E11D2E] font-medium hover:underline mt-0.5 block break-all">
                                        {{ $school->email }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- Service Hours -->
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-rose-50 text-[#E11D2E] flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <span class="font-bold text-zinc-800 block text-xs uppercase tracking-wider">Jam Pelayanan Kantor</span>
                                <p class="text-zinc-600 mt-0.5">
                                    Senin – Jumat: 07:30 – 15:30 WIB<br>
                                    <span class="text-zinc-400 text-xs">Sabtu, Minggu & Libur Nasional: Tutup</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Google Maps Embed if available -->
                @if (!empty($setting->maps_embed_url))
                    <div class="bg-white rounded-2xl p-4 border border-zinc-200/80 shadow-2xs overflow-hidden">
                        <span class="text-xs font-bold text-zinc-700 block mb-2 uppercase tracking-wider">Peta Lokasi Sekolah</span>
                        <div class="aspect-video w-full rounded-xl overflow-hidden bg-zinc-100 border border-zinc-200">
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
                <div class="bg-zinc-900 text-zinc-300 rounded-2xl p-5 border border-zinc-800 space-y-2">
                    <div class="flex items-center gap-2 text-white font-bold text-xs uppercase tracking-wider">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Jaminan Privasi & Keamanan Data</span>
                    </div>
                    <p class="text-xs text-zinc-400 leading-relaxed">
                        Data kontak dan isi pesan Anda dilindungi oleh platform Eduvia dan hanya digunakan oleh administrator resmi {{ $school->name }} untuk menjawab kebutuhan informasi Anda. Kami tidak akan menyebarluaskan data Anda kepada pihak lain.
                    </p>
                </div>

            </div>

            <!-- Right Column: Interactive Contact Form (7 cols) -->
            <div class="lg:col-span-7">
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-zinc-200/80 shadow-2xs">

                    @if (session('contact_success'))
                        <!-- Success Feedback Card -->
                        <div class="py-8 px-4 text-center space-y-4">
                            <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto border-2 border-emerald-200">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-xl sm:text-2xl font-black text-zinc-900">Pesan Berhasil Dikirim!</h3>
                                <p class="text-xs sm:text-sm text-zinc-500 max-w-md mx-auto">
                                    Terima kasih telah menghubungi {{ $school->name }}. Pesan Anda telah tersimpan dan tim administrasi sekolah akan segera meninjau pesan Anda.
                                </p>
                            </div>

                            @if (session('reference_number'))
                                <div class="bg-zinc-50 border border-zinc-200 rounded-xl p-4 max-w-sm mx-auto">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 block">Nomor Referensi Pesan Anda</span>
                                    <span class="font-mono text-lg sm:text-xl font-black text-[#E11D2E] tracking-wider block mt-0.5">
                                        {{ session('reference_number') }}
                                    </span>
                                    <span class="text-[11px] text-zinc-400 block mt-1">Simpan kode ini jika ingin mengonfirmasi status pesan.</span>
                                </div>
                            @endif

                            <div class="pt-4">
                                <a href="{{ route('school.public.contact', ['school' => $school->slug]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C71827] text-white shadow-xs transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Kirim Pesan Lainnya</span>
                                </a>
                            </div>
                        </div>

                    @else
                        <!-- The Form -->
                        <div class="mb-6">
                            <h2 class="text-lg sm:text-xl font-bold text-zinc-900">Formulir Pesan & Pertanyaan</h2>
                            <p class="text-xs text-zinc-500 mt-1">
                                Field dengan tanda bintang (<span class="text-[#E11D2E] font-bold">*</span>) wajib diisi.
                            </p>
                        </div>

                        @if ($errors->any())
                            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-[#E11D2E] text-xs">
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
                            class="space-y-4"
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
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="name" class="block text-xs font-bold text-zinc-700 mb-1">
                                        Nama Lengkap <span class="text-[#E11D2E]">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        value="{{ old('name') }}"
                                        required
                                        maxlength="150"
                                        placeholder="Masukkan nama lengkap Anda"
                                        class="w-full text-xs sm:text-sm rounded-xl border border-zinc-200 px-3.5 py-2.5 text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-[#E11D2E]/20 focus:border-[#E11D2E] transition-all"
                                    >
                                    @error('name')
                                        <p class="text-[11px] text-[#E11D2E] mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email" class="block text-xs font-bold text-zinc-700 mb-1">
                                        Alamat Email <span class="text-[#E11D2E]">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                        maxlength="150"
                                        placeholder="contoh@email.com"
                                        class="w-full text-xs sm:text-sm rounded-xl border border-zinc-200 px-3.5 py-2.5 text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-[#E11D2E]/20 focus:border-[#E11D2E] transition-all"
                                    >
                                    @error('email')
                                        <p class="text-[11px] text-[#E11D2E] mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Phone & Category (2 cols) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="phone" class="block text-xs font-bold text-zinc-700 mb-1">
                                        Nomor Telepon / WhatsApp <span class="text-zinc-400 font-normal">(Opsional)</span>
                                    </label>
                                    <input
                                        type="tel"
                                        id="phone"
                                        name="phone"
                                        value="{{ old('phone') }}"
                                        maxlength="30"
                                        placeholder="Contoh: 08123456789"
                                        class="w-full text-xs sm:text-sm rounded-xl border border-zinc-200 px-3.5 py-2.5 text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-[#E11D2E]/20 focus:border-[#E11D2E] transition-all"
                                    >
                                    @error('phone')
                                        <p class="text-[11px] text-[#E11D2E] mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="category" class="block text-xs font-bold text-zinc-700 mb-1">
                                        Kategori Pesan <span class="text-[#E11D2E]">*</span>
                                    </label>
                                    <select
                                        id="category"
                                        name="category"
                                        required
                                        class="w-full text-xs sm:text-sm rounded-xl border border-zinc-200 px-3.5 py-2.5 text-zinc-900 bg-white focus:outline-none focus:ring-2 focus:ring-[#E11D2E]/20 focus:border-[#E11D2E] transition-all"
                                    >
                                        <option value="" disabled {{ old('category') ? '' : 'selected' }}>Pilih kategori pesan...</option>
                                        @foreach (\App\Models\SchoolMessage::CATEGORIES as $key => $label)
                                            <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <p class="text-[11px] text-[#E11D2E] mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Subject -->
                            <div>
                                <label for="subject" class="block text-xs font-bold text-zinc-700 mb-1">
                                    Subjek Pesan <span class="text-[#E11D2E]">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="subject"
                                    name="subject"
                                    value="{{ old('subject') }}"
                                    required
                                    maxlength="200"
                                    placeholder="Tuliskan pokok atau topik pesan Anda"
                                    class="w-full text-xs sm:text-sm rounded-xl border border-zinc-200 px-3.5 py-2.5 text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-[#E11D2E]/20 focus:border-[#E11D2E] transition-all"
                                >
                                @error('subject')
                                    <p class="text-[11px] text-[#E11D2E] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Message Content -->
                            <div>
                                <label for="message" class="block text-xs font-bold text-zinc-700 mb-1">
                                    Isi Pesan <span class="text-[#E11D2E]">*</span>
                                </label>
                                <textarea
                                    id="message"
                                    name="message"
                                    rows="5"
                                    required
                                    maxlength="3000"
                                    placeholder="Tuliskan pertanyaan, tanggapan, atau kebutuhan informasi Anda secara jelas..."
                                    class="w-full text-xs sm:text-sm rounded-xl border border-zinc-200 px-3.5 py-2.5 text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-[#E11D2E]/20 focus:border-[#E11D2E] transition-all leading-relaxed"
                                >{{ old('message') }}</textarea>
                                <div class="flex items-center justify-between text-[11px] text-zinc-400 mt-1">
                                    <span>Teks biasa &bull; Maksimal 3.000 karakter</span>
                                </div>
                                @error('message')
                                    <p class="text-[11px] text-[#E11D2E] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Privacy Notice Agreement -->
                            <div class="pt-2">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="privacy_consent"
                                        value="1"
                                        required
                                        {{ old('privacy_consent') ? 'checked' : '' }}
                                        class="mt-1 w-4 h-4 rounded border-zinc-300 text-[#E11D2E] focus:ring-[#E11D2E]"
                                    >
                                    <span class="text-xs text-zinc-600 leading-relaxed">
                                        Saya menyetujui bahwa data nama, email, dan pesan yang saya kirimkan dapat disimpan dan diproses oleh pihak <strong>{{ $school->name }}</strong> untuk keperluan komunikasi dan penanganan korespondensi.
                                    </span>
                                </label>
                                @error('privacy_consent')
                                    <p class="text-[11px] text-[#E11D2E] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Submit Action -->
                            <div class="pt-4 border-t border-zinc-100 flex items-center justify-between">
                                <span class="text-xs text-zinc-400 hidden sm:inline">
                                    Pesan Anda akan segera diteruskan ke staf administrasi.
                                </span>
                                <button
                                    type="submit"
                                    :disabled="submitting"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-xs sm:text-sm font-bold bg-[#E11D2E] hover:bg-[#C71827] text-white shadow-xs transition-all disabled:opacity-50 disabled:cursor-not-allowed"
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
