@extends('layouts.school-public')

@section('content')
    <!-- Header Banner -->
    <div class="bg-zinc-900 text-white py-14 sm:py-20 border-b border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-[#E11D2E] text-white mb-3">
                Tentang Sekolah
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight">
                Profil & Identitas {{ $school->name }}
            </h1>
            <p class="text-zinc-400 text-sm sm:text-base mt-2 max-w-2xl">
                Mengenal lebih dekat sejarah, visi, misi, pimpinan, dan sarana prasarana sekolah.
            </p>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16 space-y-16">
        
        <!-- Section: Identitas & Akreditasi -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-zinc-200/80 shadow-xs">
            <h2 class="text-xl font-bold text-zinc-900 mb-6 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#E11D2E]"></span>
                <span>Identitas Resmi Lembaga</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 text-xs sm:text-sm">
                <div class="p-4 rounded-2xl bg-zinc-50 border border-zinc-100">
                    <span class="text-zinc-400 text-xs block mb-1">Nama Lembaga</span>
                    <strong class="text-zinc-900 font-bold">{{ $school->name }}</strong>
                </div>
                <div class="p-4 rounded-2xl bg-zinc-50 border border-zinc-100">
                    <span class="text-zinc-400 text-xs block mb-1">Nomor Pokok Sekolah Nasional (NPSN)</span>
                    <strong class="text-zinc-900 font-bold">{{ $school->npsn ?: 'Belum diisi' }}</strong>
                </div>
                <div class="p-4 rounded-2xl bg-zinc-50 border border-zinc-100">
                    <span class="text-zinc-400 text-xs block mb-1">Status Akreditasi</span>
                    <strong class="text-emerald-600 font-bold">{{ $setting->accreditation_grade ?: 'Terakreditasi' }}</strong>
                    @if ($setting->accreditation_no)
                        <span class="text-[11px] text-zinc-500 block mt-0.5">{{ $setting->accreditation_no }}</span>
                    @endif
                </div>
                <div class="p-4 rounded-2xl bg-zinc-50 border border-zinc-100">
                    <span class="text-zinc-400 text-xs block mb-1">Jenjang Pendidikan</span>
                    <strong class="text-zinc-900 font-bold uppercase">{{ is_array($school->educational_levels) ? implode(', ', $school->educational_levels) : 'Formal' }}</strong>
                </div>
            </div>
        </div>

        <!-- Section: Sambutan Kepala Sekolah -->
        @if ($setting->welcome_message)
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-zinc-200/80 shadow-xs">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    <div class="lg:col-span-4 text-center">
                        <div class="w-48 h-56 rounded-2xl bg-zinc-100 overflow-hidden mx-auto shadow-md border-2 border-white">
                            @if ($setting->principalPhotoUrl())
                                <img src="{{ $setting->principalPhotoUrl() }}" alt="{{ $setting->principal_name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-zinc-300">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                            @endif
                        </div>
                        <h3 class="font-bold text-zinc-900 mt-3 text-base">{{ $setting->principal_name ?: 'Kepala Sekolah' }}</h3>
                        <p class="text-xs text-zinc-500">{{ $setting->principal_title ?: 'Kepala Sekolah ' . $school->name }}</p>
                    </div>
                    <div class="lg:col-span-8 space-y-3">
                        <h2 class="text-2xl font-extrabold text-zinc-900 tracking-tight">Sambutan Kepala Sekolah</h2>
                        <div class="text-zinc-600 text-sm leading-relaxed space-y-2">
                            {!! nl2br(e($setting->welcome_message)) !!}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Section: Visi dan Misi -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-zinc-200/80 shadow-xs">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-[#E11D2E] flex items-center justify-center font-bold mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <h3 class="text-xl font-extrabold text-zinc-900 mb-3">Visi Sekolah</h3>
                <p class="text-sm sm:text-base text-zinc-600 leading-relaxed font-medium italic">
                    "{{ $setting->vision ?: 'Menjadi lembaga pendidikan terdepan yang mewujudkan generasi beriman, cerdas, terampil, dan berdaya saing global.' }}"
                </p>
            </div>

            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-zinc-200/80 shadow-xs">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <h3 class="text-xl font-extrabold text-zinc-900 mb-3">Misi Sekolah</h3>
                @if ($setting->mission && is_array($setting->mission) && count($setting->mission) > 0)
                    <ul class="space-y-2.5 text-sm text-zinc-600">
                        @foreach ($setting->mission as $m)
                            <li class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                                    {{ $loop->iteration }}
                                </span>
                                <span>{{ $m }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-zinc-500">Misi sekolah sedang dalam tahap penataan kurikulum.</p>
                @endif
            </div>
        </div>

        <!-- Section: Sejarah Singkat -->
        @if ($setting->history)
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-zinc-200/80 shadow-xs">
                <h2 class="text-xl font-bold text-zinc-900 mb-4 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-zinc-800"></span>
                    <span>Sejarah & Kilas Balik Pendirian</span>
                </h2>
                <div class="text-zinc-600 text-sm sm:text-base leading-relaxed space-y-3 prose-zinc">
                    {!! nl2br(e($setting->history)) !!}
                </div>
            </div>
        @endif

        <!-- Section: Peta Lokasi / Peta Google -->
        @if ($setting->maps_embed_url)
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-zinc-200/80 shadow-xs">
                <h2 class="text-xl font-bold text-zinc-900 mb-4 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    <span>Lokasi & Denah Sekolah</span>
                </h2>
                <div class="rounded-2xl overflow-hidden border border-zinc-200 h-80 sm:h-96 w-full">
                    <iframe src="{{ $setting->maps_embed_url }}" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        @endif

    </div>
@endsection
