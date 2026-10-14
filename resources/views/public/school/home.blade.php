@extends('layouts.school-public')

@section('content')

    <!-- ====================================================================== -->
    <!-- 1. HERO SECTION                                                        -->
    <!-- ====================================================================== -->
    <section class="relative bg-zinc-900 text-white overflow-hidden">
        <!-- Background Overlay / Image -->
        <div class="absolute inset-0 z-0 opacity-25">
            @if ($setting->heroImageUrl())
                <img src="{{ $setting->heroImageUrl() }}" alt="{{ $school->name }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-linear-to-br from-zinc-950 via-zinc-900 to-rose-950"></div>
            @endif
        </div>
        <div class="absolute inset-0 bg-linear-to-t from-zinc-950 via-zinc-900/80 to-transparent z-1"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28 lg:py-36">
            <div class="max-w-3xl">
                <!-- Badges Strip -->
                <div class="flex flex-wrap items-center gap-2 mb-6">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#E11D2E] text-white shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                        <span>Situs Resmi</span>
                    </span>
                    @if ($setting->accreditation_grade)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            Akreditasi {{ $setting->accreditation_grade }}
                        </span>
                    @endif
                    @if ($school->educational_levels && is_array($school->educational_levels))
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-white/10 text-zinc-300 border border-white/15">
                            Jenjang: {{ strtoupper(implode(' / ', $school->educational_levels)) }}
                        </span>
                    @endif
                </div>

                <!-- Headline & Subheadline -->
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-[1.15] mb-6">
                    {{ $setting->hero_headline ?: $school->name }}
                </h1>

                <p class="text-base sm:text-lg md:text-xl text-zinc-300 leading-relaxed mb-8 max-w-2xl">
                    {{ $setting->hero_subheadline ?: 'Membina generasi berakhlak mulia, berprestasi akademik tinggi, dan siap memimpin masa depan melalui ekosistem digital terpadu Eduvia.' }}
                </p>

                <!-- Action CTAs -->
                <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                    <a href="{{ $setting->hero_cta_url ?: route('school.public.profile', ['school' => $school->slug]) }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-bold text-sm bg-[#E11D2E] hover:bg-[#C71827] text-white shadow-md shadow-rose-950/30 transition-all hover:-translate-y-0.5">
                        <span>{{ $setting->hero_cta_text ?: 'Jelajahi Profil Sekolah' }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>

                    @if ($activeAdmissionPeriod)
                        <a href="{{ route('ppdb.register', ['period' => $activeAdmissionPeriod->code]) }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-bold text-sm bg-emerald-600 hover:bg-emerald-700 text-white shadow-md transition-all hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Daftar PPDB Sekarang</span>
                        </a>
                    @else
                        <a href="{{ route('ppdb.index') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl font-semibold text-sm bg-white/10 hover:bg-white/15 text-white border border-white/20 transition-colors">
                            <span>Informasi Pendaftaran</span>
                        </a>
                    @endif

                    @if ($setting->hero_video_url)
                        <a href="{{ $setting->hero_video_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-3.5 rounded-xl font-semibold text-sm text-zinc-300 hover:text-white transition-colors">
                            <span class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white">
                                <svg class="w-4 h-4 fill-current ml-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </span>
                            <span>Tonton Video Profil</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- ====================================================================== -->
    <!-- 2. SAMBUTAN KEPALA SEKOLAH & IDENTITAS                                 -->
    <!-- ====================================================================== -->
    @if (!empty($setting->welcome_message))
        <section class="py-16 sm:py-20 bg-white border-b border-zinc-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                    
                    <!-- Principal Portrait -->
                    <div class="lg:col-span-4 text-center lg:text-left">
                        <div class="relative inline-block max-w-[280px] sm:max-w-[320px] mx-auto">
                            <div class="relative rounded-2xl overflow-hidden shadow-xl bg-zinc-100 border-4 border-white aspect-4/5">
                                @if ($setting->principalPhotoUrl())
                                    <img src="{{ $setting->principalPhotoUrl() }}" alt="{{ $setting->principal_name ?? 'Kepala Sekolah' }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center p-6 bg-zinc-100 text-zinc-400 text-center">
                                        <svg class="w-20 h-20 mb-2 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Pimpinan Sekolah</span>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-4">
                                <h3 class="font-bold text-lg text-zinc-900 leading-tight">
                                    {{ $setting->principal_name ?: 'Kepala Sekolah' }}
                                </h3>
                                <p class="text-xs text-zinc-500 mt-0.5">
                                    {{ $setting->principal_title ?: 'Kepala Sekolah ' . $school->name }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Welcome Text -->
                    <div class="lg:col-span-8 space-y-4">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-[#E11D2E] border border-rose-100">
                            Sambutan Resmi
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 tracking-tight">
                            Membangun Karakter & Prestasi Menuju Generasi Emas
                        </h2>
                        <div class="text-zinc-600 text-sm sm:text-base leading-relaxed space-y-3 prose-zinc">
                            {!! nl2br(e($setting->welcome_message)) !!}
                        </div>
                        <div class="pt-2">
                            <a href="{{ route('school.public.profile', ['school' => $school->slug]) }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-[#E11D2E] hover:text-[#C71827]">
                                <span>Baca Visi, Misi & Sejarah Selengkapnya</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    @endif

    <!-- ====================================================================== -->
    <!-- 3. PPDB HIGHLIGHT STRIP (JIKA PERIODE AKTIF)                            -->
    <!-- ====================================================================== -->
    @if ($activeAdmissionPeriod)
        <section class="py-10 bg-linear-to-r from-emerald-800 to-teal-900 text-white shadow-inner">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="space-y-1.5 text-center md:text-left">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/30 text-emerald-200 border border-emerald-400/40">
                            Penerimaan Siswa Baru Dibuka
                        </span>
                        <h3 class="text-xl sm:text-2xl font-extrabold text-white">
                            {{ $activeAdmissionPeriod->name }}
                        </h3>
                        <p class="text-xs sm:text-sm text-emerald-100">
                            Periode Pendaftaran: <strong>{{ $activeAdmissionPeriod->start_date->format('d M Y') }}</strong> s/d <strong>{{ $activeAdmissionPeriod->end_date->format('d M Y') }}</strong>
                            @if ($activeAdmissionPeriod->tracks->isNotEmpty())
                                &bull; Jalur: {{ $activeAdmissionPeriod->tracks->pluck('name')->implode(', ') }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('ppdb.register', ['period' => $activeAdmissionPeriod->code]) }}" class="px-5 py-3 rounded-xl font-bold text-xs sm:text-sm bg-white text-emerald-900 hover:bg-emerald-50 shadow-md transition-all hover:scale-105">
                            Formulir Pendaftaran
                        </a>
                        <a href="{{ route('ppdb.check-status') }}" class="px-4 py-3 rounded-xl font-semibold text-xs sm:text-sm bg-emerald-700/80 hover:bg-emerald-700 text-white border border-emerald-500/50 transition-colors">
                            Cek Status Seleksi
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- ====================================================================== -->
    <!-- 4. PROGRAM UNGGULAN & FASILITAS                                        -->
    <!-- ====================================================================== -->
    @if ($programs->isNotEmpty() || $facilities->isNotEmpty())
        <section class="py-16 sm:py-20 bg-zinc-50 border-b border-zinc-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#E11D2E]">Ekosistem Belajar</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 mt-1">Program Unggulan & Sarana Fasilitas</h2>
                    <p class="text-xs sm:text-sm text-zinc-500 mt-2">Dukungan komprehensif bagi tumbuh kembang potensi intelektual dan karakter siswa.</p>
                </div>

                <!-- Featured Programs -->
                @if ($programs->isNotEmpty())
                    <div class="mb-12">
                        <h3 class="text-lg font-bold text-zinc-900 mb-6 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#E11D2E]"></span>
                            <span>Program Unggulan Sekolah</span>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @foreach ($programs as $prog)
                                <div class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-2xs hover:shadow-md transition-shadow">
                                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-[#E11D2E] flex items-center justify-center font-bold mb-4">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                    </div>
                                    <h4 class="font-bold text-base text-zinc-900 mb-2">{{ $prog->name }}</h4>
                                    <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed">{{ $prog->description }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Facilities Grid -->
                @if ($facilities->isNotEmpty())
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-zinc-900 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span>Sarana & Fasilitas Kampus</span>
                            </h3>
                            <a href="{{ route('school.public.facilities', ['school' => $school->slug]) }}" class="text-xs font-bold text-[#E11D2E] hover:text-[#C71827]">
                                Lihat Semua Fasilitas &rarr;
                            </a>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            @foreach ($facilities as $fac)
                                <div class="bg-white rounded-2xl overflow-hidden border border-zinc-200 shadow-2xs hover:shadow-md transition-shadow">
                                    <div class="h-40 bg-zinc-100 overflow-hidden relative">
                                        @if ($fac->coverImageUrl())
                                            <img src="{{ $fac->coverImageUrl() }}" alt="{{ $fac->name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-zinc-100 text-zinc-400">
                                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-4">
                                        <h4 class="font-bold text-sm text-zinc-900 mb-1 truncate">{{ $fac->name }}</h4>
                                        <p class="text-xs text-zinc-500 line-clamp-2">{{ $fac->description }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </section>
    @endif

    <!-- ====================================================================== -->
    <!-- 5. PRESTASI SISWA                                                      -->
    <!-- ====================================================================== -->
    @if ($achievements->isNotEmpty())
        <section class="py-16 sm:py-20 bg-white border-b border-zinc-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#E11D2E]">Kebanggaan Sekolah</span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 mt-1">Catatan Prestasi Siswa</h2>
                        <p class="text-xs sm:text-sm text-zinc-500 mt-1">Kiprah dan dedikasi peserta didik pada ajang kompetisi berjenjang.</p>
                    </div>
                    <a href="{{ route('school.public.achievements', ['school' => $school->slug]) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#E11D2E] hover:text-[#C71827]">
                        <span>Lihat Arsip Prestasi</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($achievements as $ach)
                        <div class="bg-zinc-50/70 rounded-2xl p-5 border border-zinc-200 hover:border-rose-200 hover:bg-white hover:shadow-md transition-all">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-100 text-amber-800 border border-amber-200">
                                    {{ $ach->competition_level ?? 'Kompetisi' }}
                                </span>
                                @if ($ach->published_at)
                                    <span class="text-[11px] text-zinc-400">{{ $ach->published_at->format('M Y') }}</span>
                                @endif
                            </div>
                            <h4 class="font-bold text-sm text-zinc-900 leading-snug mb-2 line-clamp-2" title="{{ $ach->title }}">
                                {{ $ach->title }}
                            </h4>
                            @if ($ach->awardee_name)
                                <p class="text-xs font-semibold text-[#E11D2E] mb-1">
                                    {{ $ach->awardee_name }}
                                </p>
                            @endif
                            <p class="text-xs text-zinc-500 line-clamp-3 leading-relaxed">
                                {{ $ach->excerpt ?: Str::limit(strip_tags($ach->content), 120) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- ====================================================================== -->
    <!-- 6. WARTA BERITA & AGENDA ACARA                                         -->
    <!-- ====================================================================== -->
    <section class="py-16 sm:py-20 bg-zinc-50 border-b border-zinc-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                
                <!-- Left: News & Articles (8 cols) -->
                <div class="lg:col-span-8 space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-[#E11D2E]">Warta Kampus</span>
                            <h2 class="text-2xl font-extrabold text-zinc-900 mt-0.5">Berita & Informasi Terkini</h2>
                        </div>
                        <a href="{{ route('school.public.articles', ['school' => $school->slug]) }}" class="text-xs font-bold text-[#E11D2E] hover:text-[#C71827]">
                            Lihat Semua &rarr;
                        </a>
                    </div>

                    @if ($latestNews->isEmpty())
                        <div class="p-8 text-center bg-white rounded-2xl border border-zinc-200 text-zinc-400 text-sm">
                            Belum ada berita yang dipublikasikan.
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($latestNews as $news)
                                <article class="bg-white rounded-2xl overflow-hidden border border-zinc-200 shadow-2xs hover:shadow-md transition-all flex flex-col">
                                    <div class="h-44 bg-zinc-100 overflow-hidden relative">
                                        @if ($news->coverImageUrl())
                                            <img src="{{ $news->coverImageUrl() }}" alt="{{ $news->title }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-zinc-100 text-zinc-300">
                                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                            </div>
                                        @endif
                                        @if ($news->category)
                                            <span class="absolute top-2.5 left-2.5 px-2 py-0.5 text-[10px] font-bold rounded bg-zinc-900/80 text-white backdrop-blur-xs">
                                                {{ $news->category }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="p-4 flex-1 flex flex-col justify-between">
                                        <div>
                                            <span class="text-[11px] text-zinc-400 block mb-1">
                                                {{ $news->published_at ? $news->published_at->format('d M Y') : 'Terbaru' }}
                                            </span>
                                            <h3 class="font-bold text-sm text-zinc-900 leading-snug line-clamp-2 mb-2 hover:text-[#E11D2E] transition-colors">
                                                <a href="{{ route('school.public.article-detail', ['school' => $school->slug, 'slug' => $news->slug]) }}">
                                                    {{ $news->title }}
                                                </a>
                                            </h3>
                                            <p class="text-xs text-zinc-500 line-clamp-2 leading-relaxed">
                                                {{ $news->excerpt ?: Str::limit(strip_tags($news->content), 90) }}
                                            </p>
                                        </div>
                                        <div class="pt-3 mt-3 border-t border-zinc-100">
                                            <a href="{{ route('school.public.article-detail', ['school' => $school->slug, 'slug' => $news->slug]) }}" class="text-xs font-bold text-[#E11D2E] hover:underline inline-flex items-center gap-1">
                                                <span>Baca Artikel</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Right: Upcoming Events (4 cols) -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Kalender</span>
                            <h2 class="text-2xl font-extrabold text-zinc-900 mt-0.5">Agenda Acara</h2>
                        </div>
                        <a href="{{ route('school.public.events', ['school' => $school->slug]) }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">
                            Semua &rarr;
                        </a>
                    </div>

                    @if ($upcomingEvents->isEmpty())
                        <div class="p-8 text-center bg-white rounded-2xl border border-zinc-200 text-zinc-400 text-sm">
                            Belum ada agenda mendatang.
                        </div>
                    @else
                        <div class="space-y-3.5">
                            @foreach ($upcomingEvents as $evt)
                                <div class="bg-white rounded-2xl p-4 border border-zinc-200 flex items-start gap-3.5 hover:shadow-xs transition-shadow">
                                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex flex-col items-center justify-center shrink-0 border border-emerald-100">
                                        <span class="text-sm font-extrabold leading-none">{{ $evt->event_date?->format('d') ?? '--' }}</span>
                                        <span class="text-[10px] font-bold uppercase mt-0.5 leading-none">{{ $evt->event_date?->format('M') ?? 'Bln' }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="font-bold text-xs sm:text-sm text-zinc-900 leading-snug line-clamp-2">
                                            {{ $evt->title }}
                                        </h4>
                                        @if ($evt->event_location)
                                            <p class="text-[11px] text-zinc-400 flex items-center gap-1 mt-1 truncate">
                                                <svg class="w-3 h-3 text-zinc-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <span>{{ $evt->event_location }}</span>
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </section>

    <!-- ====================================================================== -->
    <!-- 7. EKSTRAKURIKULER                                                     -->
    <!-- ====================================================================== -->
    @if ($extracurriculars->isNotEmpty())
        <section class="py-16 sm:py-20 bg-white border-b border-zinc-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#E11D2E]">Minat & Bakat</span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 mt-1">Kegiatan Ekstrakurikuler</h2>
                        <p class="text-xs sm:text-sm text-zinc-500 mt-1">Wadah eksplorasi kepemimpinan, olahraga, seni, dan teknologi bagi peserta didik.</p>
                    </div>
                    <a href="{{ route('school.public.extracurriculars', ['school' => $school->slug]) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#E11D2E] hover:text-[#C71827]">
                        <span>Lihat Semua Ekstrakurikuler</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($extracurriculars as $ekstra)
                        <div class="bg-zinc-50 rounded-2xl p-5 border border-zinc-200 hover:border-zinc-300 transition-colors">
                            <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-white text-zinc-600 border border-zinc-200 mb-2 inline-block">
                                {{ $ekstra->category }}
                            </span>
                            <h4 class="font-bold text-base text-zinc-900 mb-1.5">{{ $ekstra->name }}</h4>
                            <p class="text-xs text-zinc-500 line-clamp-2 leading-relaxed mb-3">{{ $ekstra->description }}</p>
                            
                            <div class="space-y-1 text-[11px] text-zinc-500 pt-2 border-t border-zinc-200/60">
                                @if ($ekstra->coach_name)
                                    <div><strong>Pembina:</strong> {{ $ekstra->coach_name }}</div>
                                @endif
                                @if ($ekstra->schedule_info)
                                    <div><strong>Jadwal:</strong> {{ $ekstra->schedule_info }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- ====================================================================== -->
    <!-- 8. GALERI FOTO HIGHLIGHT                                               -->
    <!-- ====================================================================== -->
    @if ($gallery->isNotEmpty())
        <section class="py-16 sm:py-20 bg-zinc-50 border-b border-zinc-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#E11D2E]">Dokumentasi</span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 mt-1">Galeri Kegiatan Sekolah</h2>
                        <p class="text-xs sm:text-sm text-zinc-500 mt-1">Momen kebersamaan, pembelajaran, dan prestasi di lingkungan sekolah.</p>
                    </div>
                    <a href="{{ route('school.public.gallery', ['school' => $school->slug]) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#E11D2E] hover:text-[#C71827]">
                        <span>Buka Album Galeri</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                    @foreach ($gallery as $item)
                        <div class="aspect-square rounded-2xl overflow-hidden bg-zinc-100 border border-zinc-200 group relative">
                            @if ($item->mediaUrl())
                                <img src="{{ $item->mediaUrl() }}" alt="{{ $item->alt_text ?: $item->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-zinc-800 text-zinc-500">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-linear-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity p-2.5 flex flex-col justify-end text-white text-[10px]">
                                <span class="font-bold leading-tight truncate">{{ $item->title }}</span>
                                <span class="text-zinc-300 truncate">{{ $item->album_name }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection
