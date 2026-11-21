@extends('layouts.school-public')

@section('content')
    @php
        $heroCtaUrl = trim($setting->hero_cta_url ?? '');

        if (in_array($heroCtaUrl, ['', '#', '#profil'], true)) {
            $heroCtaUrl = route('school.public.profile', ['school' => $school->slug]);
        }
    @endphp

    <div class="school-home">
        <section class="school-hero school-container" aria-labelledby="hero-title">
            <div class="hero-copy">
                <p class="school-eyebrow">Belajar. Bertumbuh. Berarti.</p>
                <h1 id="hero-title">{{ $setting->hero_headline ?: 'Tempat potensi tumbuh, masa depan dimulai.' }}</h1>
                <p class="hero-description">{{ $setting->hero_subheadline ?: 'Ruang untuk bertanya, keberanian untuk mencoba, dan pendampingan untuk menjadi diri terbaik. Bersama, kita siapkan langkah anak menuju masa depan.' }}</p>
                <div class="school-actions">
                    <x-button :href="$activeAdmissionPeriod ? route('ppdb.register', ['period' => $activeAdmissionPeriod->code]) : route('ppdb.index')" size="lg">{{ $activeAdmissionPeriod ? 'Daftar peserta didik baru' : 'Informasi penerimaan' }}</x-button>
                    <x-button :href="$heroCtaUrl" variant="outline" size="lg">{{ $setting->hero_cta_text ?: 'Kenali sekolah kami' }}</x-button>
                </div>
                <div class="hero-footnote">
                    <span class="hero-footnote-mark" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" focusable="false">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                    </span>
                    <p>Setiap anak punya potensi.<br><strong>Kami hadir untuk menumbuhkannya.</strong></p>
                </div>
            </div>
            <div class="hero-visual">
                <figure class="hero-photo">
                    <img src="{{ $setting->heroImageUrl() ?: asset('images/edmunhigh/hero-students-web.webp') }}" @if (! $setting->heroImageUrl()) srcset="{{ asset('images/edmunhigh/hero-students-mobile.webp') }} 800w, {{ asset('images/edmunhigh/hero-students-web.webp') }} 1200w" sizes="(max-width: 767px) calc(100vw - 40px), (max-width: 1312px) 50vw, 640px" @endif alt="{{ $setting->heroImageUrl() ? 'Suasana kebersamaan dan pembelajaran siswa' : 'Ilustrasi siswa belajar dan beraktivitas bersama' }}" width="1100" height="800" fetchpriority="high" decoding="async">
                    <figcaption><span class="photo-label">Ruang belajar, ruang bertumbuh</span><span>{{ $setting->heroImageUrl() ? ($school->short_name ?: $school->name) : 'Ilustrasi lingkungan pendidikan' }}</span></figcaption>
                </figure>
                <div class="hero-note"><img src="{{ asset('images/brand/eduvia-icon-mark-web.webp') }}" alt="" width="38" height="38"><div><strong>Pendidikan yang terhubung.</strong><span>Siswa, guru, dan orang tua.</span></div></div>
                <span class="hero-visual-line" aria-hidden="true"></span>
            </div>
        </section>

        <div class="school-facts"><div class="school-container facts-inner">
            <p>Pendidikan berkarakter.<br><strong>Pembelajaran untuk masa depan.</strong></p>
            <div class="facts-levels" aria-label="Jenjang pendidikan">
                @foreach ($school->educational_levels ?? [] as $level)
                    <span>{{ strtoupper($level) }}</span>
                @endforeach
            </div>
            @if ($setting->accreditation_grade)
                <p class="facts-accreditation"><span>Akreditasi</span><strong>{{ $setting->accreditation_grade }}</strong></p>
            @else
                <p class="facts-accreditation"><span>Ekosistem digital</span><strong>Didukung Eduvia</strong></p>
            @endif
        </div></div>

        <section id="about" class="school-section school-container about-section" aria-labelledby="about-title">
            <div class="about-visual">
                <img src="{{ asset('images/edmunhigh/small-group-web.webp') }}" alt="Siswa berdiskusi dan belajar bersama dalam kelompok" width="640" height="720" loading="lazy" decoding="async">
                <div class="about-caption"><span>Lebih dari ruang kelas.</span><strong>Tempat menemukan<br>versi terbaik diri.</strong></div>
            </div>
            <div class="about-copy">
                <p class="school-eyebrow">Mengenal sekolah kami</p>
                <h2 id="about-title">Pendidikan yang melihat<br>potensi setiap anak.</h2>
                <p>{{ $setting->welcome_message ? Str::limit(strip_tags($setting->welcome_message), 460) : 'Kami percaya perjalanan belajar yang baik dimulai dari rasa ingin tahu. Di ' . $school->name . ', siswa mendapatkan ruang untuk mengeksplorasi minat, mengembangkan pengetahuan, dan membangun karakter melalui pengalaman belajar bersama.' }}</p>
                <div class="about-principles">
                    <div><h3>Karakter sebagai fondasi</h3><p>Membiasakan tanggung jawab, empati, dan sikap saling menghargai dalam keseharian.</p></div>
                    <div><h3>Belajar dengan pengalaman</h3><p>Menghubungkan pengetahuan di kelas dengan eksplorasi, diskusi, dan kolaborasi.</p></div>
                </div>
                @if ($setting->principal_name)
                    <p class="principal-name">Bersama <strong>{{ $setting->principal_name }}</strong></p>
                @endif
                <a class="school-text-link" href="{{ route('school.public.profile', ['school' => $school->slug]) }}">Tentang visi dan misi kami</a>
            </div>
        </section>

        <section id="programs" class="program-section school-section" aria-labelledby="program-title">
            <div class="school-container">
                <div class="section-heading"><div><p class="school-eyebrow">Perjalanan pendidikan</p><h2 id="program-title">Langkah yang tepat.<br>Untuk setiap tahap tumbuh.</h2></div><p>Setiap jenjang membuka kesempatan baru. Temukan lingkungan belajar yang sesuai dengan tahap perkembangan putra-putri Anda.</p></div>
                <div class="level-grid">
                    @forelse ($school->educational_levels ?? [] as $level)
                        <a class="level-card" href="{{ route('school.public.profile', ['school' => $school->slug]) }}">
                            <span class="level-card-label">Jenjang pendidikan</span><h3>{{ strtoupper($level) }}</h3>
                            <p>{{ match (strtoupper($level)) { 'SD', 'MI', 'SD/MI' => 'Membangun rasa ingin tahu, kebiasaan baik, dan fondasi belajar yang kuat.', 'SMP', 'MTS', 'SMP/MTS' => 'Mengeksplorasi minat, melatih kemandirian, dan mengembangkan kepercayaan diri.', 'SMA', 'MA', 'SMA/MA' => 'Memperdalam pengetahuan dan mempersiapkan pilihan studi serta masa depan.', 'SMK', 'MAK', 'SMK/MAK' => 'Mengembangkan keterampilan terapan dan kesiapan untuk dunia profesional.', default => 'Mendampingi perkembangan siswa melalui pembelajaran dan pembinaan karakter.' } }}</p>
                            <span class="school-text-link">Kenali jenjang {{ strtoupper($level) }}</span>
                        </a>
                    @empty
                        <a class="level-card level-card-wide" href="{{ route('school.public.profile', ['school' => $school->slug]) }}"><h3>Kenali program pembelajaran kami</h3><p>Pelajari pendekatan pendidikan dan lingkungan belajar sekolah.</p><span class="school-text-link">Lihat profil sekolah</span></a>
                    @endforelse
                </div>
                @if ($programs->isNotEmpty())
                    <div class="featured-programs"><h3>Program unggulan</h3>
                        @foreach ($programs as $program)
                            <a href="{{ route('school.public.facilities', ['school' => $school->slug]) }}"><strong>{{ $program->name }}</strong><span>{{ Str::limit(strip_tags($program->description ?? ''), 140) }}</span></a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section id="campus-life" class="school-section school-container" aria-labelledby="campus-title">
            <div class="section-heading"><div><p class="school-eyebrow">Kehidupan sekolah</p><h2 id="campus-title">Pengalaman hari ini.<br>Bekal untuk esok hari.</h2></div><a class="school-text-link" href="{{ route('school.public.gallery', ['school' => $school->slug]) }}">Jelajahi galeri sekolah</a></div>
            <div class="campus-grid">
                <a class="campus-feature" href="{{ $setting->hero_video_url ?: route('school.public.gallery', ['school' => $school->slug]) }}" @if ($setting->hero_video_url) target="_blank" rel="noopener noreferrer" @endif>
                    <img src="{{ asset('images/edmunhigh/campus-life-web.webp') }}" alt="Aktivitas dan lingkungan belajar di sekolah" width="1000" height="650" loading="lazy" decoding="async">
                    <div><span>{{ $setting->hero_video_url ? 'Video sekolah · Buka di tab baru' : 'Cerita dari sekolah' }}</span><h3>Setiap hari, ada hal<br>baru untuk ditemukan.</h3><span class="campus-feature-action">{{ $setting->hero_video_url ? 'Tonton video sekolah' : 'Lihat kehidupan sekolah' }}</span></div>
                </a>
                <div id="community" class="campus-aside">
                    <div><p class="school-eyebrow">Tumbuh bersama</p><h3>Minat berbeda.<br>Kesempatan yang sama.</h3><p>Ruang bagi siswa untuk menemukan minat, menjalin pertemanan, dan belajar bekerja sama.</p></div>
                    <div class="activity-tags">
                        @foreach ($extracurriculars as $extracurricular)
                            <a href="{{ route('school.public.extracurriculars', ['school' => $school->slug]) }}">{{ $extracurricular->name }}</a>
                        @endforeach
                    </div>
                    <a class="school-text-link" href="{{ route('school.public.extracurriculars', ['school' => $school->slug]) }}">Temukan kegiatan siswa</a>
                    <a class="campus-facilities" href="{{ route('school.public.facilities', ['school' => $school->slug]) }}"><strong>Lingkungan yang mendukung</strong><span>Kenali sarana dan fasilitas belajar kami.</span></a>
                </div>
            </div>
        </section>

        @if ($achievements->isNotEmpty())
            <section class="achievement-section" aria-labelledby="achievement-title"><div class="school-container achievement-inner"><div><p class="school-eyebrow">Apresiasi karya & usaha</p><h2 id="achievement-title">Langkah kecil.<br>Pencapaian berarti.</h2><a class="school-text-link" href="{{ route('school.public.achievements', ['school' => $school->slug]) }}">Lihat seluruh prestasi</a></div><div class="achievement-list">
                @foreach ($achievements as $achievement)
                    <a href="{{ route('school.public.achievements', ['school' => $school->slug]) }}"><span>Prestasi siswa</span><h3>{{ $achievement->title }}</h3></a>
                @endforeach
            </div></div></section>
        @endif

        <section id="news" class="school-section school-container" aria-labelledby="news-title">
            <div class="section-heading"><div><p class="school-eyebrow">Kabar dari sekolah</p><h2 id="news-title">Cerita, karya, dan inspirasi.</h2></div><a class="school-text-link" href="{{ route('school.public.articles', ['school' => $school->slug]) }}">Semua berita sekolah</a></div>
            <div class="news-grid">
                @forelse ($latestNews as $news)
                    <article class="school-news-card"><a href="{{ route('school.public.article-detail', ['school' => $school->slug, 'slug' => $news->slug]) }}">
                        @if ($news->coverImageUrl())
                            <div class="news-image">
                                <img src="{{ $news->coverImageUrl() }}" alt="{{ $news->title }}" width="640" height="420" loading="lazy" decoding="async">
                                <span>Berita sekolah</span>
                            </div>
                        @endif
                        <div class="news-copy"><time @if ($news->published_at) datetime="{{ $news->published_at->toDateString() }}" @endif>{{ $news->published_at?->translatedFormat('d F Y') ?? 'Terbaru' }}</time><h3>{{ $news->title }}</h3><p>{{ $news->excerpt ?: Str::limit(strip_tags($news->content), 120) }}</p><span class="school-text-link">Baca cerita</span></div>
                    </a></article>
                @empty
                    <div class="school-empty"><h3>Ruang cerita sekolah kami.</h3><p>Berita dan cerita kegiatan akan hadir di sini setelah dipublikasikan oleh sekolah.</p><a class="school-text-link" href="{{ route('school.public.profile', ['school' => $school->slug]) }}">Kenali sekolah lebih dekat</a></div>
                @endforelse
            </div>
        </section>

        <section id="events" class="events-section school-section" aria-labelledby="events-title"><div class="school-container events-grid"><div><p class="school-eyebrow">Agenda sekolah</p><h2 id="events-title">Mari terlibat.<br>Mari ambil bagian.</h2><p>Temukan jadwal kegiatan dan momen kebersamaan dalam kalender sekolah.</p><a class="school-text-link" href="{{ route('school.public.events', ['school' => $school->slug]) }}">Buka kalender kegiatan</a></div><div class="event-list">
            @forelse ($upcomingEvents as $event)
                <a class="school-event" href="{{ route('school.public.events', ['school' => $school->slug]) }}"><div class="event-date"><strong>{{ $event->event_date?->format('d') ?? '•' }}</strong><span>{{ $event->event_date?->translatedFormat('M Y') ?? 'Agenda' }}</span></div><div><span class="event-status">{{ $event->event_date && $event->event_date->isPast() ? 'Kegiatan selesai' : 'Agenda mendatang' }}</span><h3>{{ $event->title }}</h3><p>{{ $event->event_location ?: 'Informasi selengkapnya di agenda sekolah' }}</p></div></a>
            @empty
                <div class="school-empty"><h3>Nantikan kegiatan berikutnya.</h3><p>Agenda terbaru akan ditampilkan setelah jadwal dipublikasikan.</p></div>
            @endforelse
        </div></div></section>

        <section class="school-container admission-section" aria-labelledby="admission-title">
            <div class="admission-copy"><p class="school-eyebrow">Awali perjalanan bersama kami</p><h2 id="admission-title">Masa depan yang baik<br>dimulai dari langkah hari ini.</h2><p>{{ $activeAdmissionPeriod ? $activeAdmissionPeriod->name : 'Kenali pilihan pendidikan untuk putra-putri Anda. Tim sekolah siap membantu menjawab pertanyaan seputar penerimaan peserta didik.' }}</p>
                @if ($activeAdmissionPeriod)
                    <div class="admission-tracks">@foreach ($activeAdmissionPeriod->tracks as $track)<span>{{ $track->name }}</span>@endforeach</div>
                @endif
                <div class="school-actions"><x-button :href="$activeAdmissionPeriod ? route('ppdb.register', ['period' => $activeAdmissionPeriod->code]) : route('ppdb.index')" size="lg">{{ $activeAdmissionPeriod ? 'Daftar sekarang' : 'Informasi PPDB' }}</x-button><x-button :href="route('school.public.contact', ['school' => $school->slug])" variant="outline" size="lg">Konsultasi dengan sekolah</x-button></div>
                @if ($activeAdmissionPeriod)
                    <a class="school-text-link admission-status" href="{{ route('ppdb.check-status') }}">Sudah mendaftar? Cek status seleksi</a>
                @endif
            </div>
            <div class="admission-image"><img src="{{ asset('images/edmunhigh/student-portrait-web.webp') }}" alt="Semangat belajar siswa untuk meraih masa depan" width="600" height="750" loading="lazy" decoding="async"></div>
        </section>
    </div>
@endsection
