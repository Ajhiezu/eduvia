@props([
    'user',
    'school' => null,
    'academicYear' => null,
    'semester' => null,
    'enrollment' => null,
    'teachingAssignments' => null,
    'todaySchedules' => null,
])

@php
    $isStudent = $user->hasRole(\App\Enums\UserRole::STUDENT);
    $isTeacher = $user->hasRole(\App\Enums\UserRole::TEACHER);
    $isParent = $user->hasRole(\App\Enums\UserRole::PARENT);
    $isAdmin = $user->hasRole(\App\Enums\UserRole::SUPER_ADMIN, \App\Enums\UserRole::SCHOOL_ADMIN);
    $profileIdentifier = $user->email;
    $shortcuts = match (true) {
        $isStudent => [
            ['Materi Pelajaran', 'student.materials.index', 'Baca dan pelajari materi kelas'],
            ['Tugas & Evaluasi', 'student.assignments.index', 'Lanjutkan tugas pembelajaran'],
            ['Presensi Saya', 'attendance.my', 'Lihat seluruh catatan kehadiran'],
        ],
        $isTeacher => [
            ['Materi Pembelajaran', 'learning-materials.index', 'Kelola bahan ajar'],
            ['Tugas & Penilaian', 'assignments.index', 'Tinjau pekerjaan siswa'],
            ['Buat Sesi Presensi', 'attendance-sessions.create', 'Catat kehadiran kelas'],
        ],
        $isParent => [
            ['Akademik Anak', 'parent.grades', 'Pantau perkembangan belajar'],
            ['Tagihan Sekolah', 'parent.finance.index', 'Lihat rincian administrasi'],
            ['Tautkan Akun Anak', 'parent.children.index', 'Kelola anak yang terhubung'],
        ],
        $isAdmin => [
            ['Data Siswa', 'students.index', 'Kelola peserta didik'],
            ['Presensi Sekolah', 'attendance.report', 'Tinjau kehadiran rombongan belajar'],
            ['Profil Sekolah', 'school.profile', 'Lengkapi informasi sekolah'],
        ],
        default => [],
    };
@endphp

<aside class="dashboard-rail" aria-label="Profil dan agenda">
    <div class="dashboard-section-heading">
        <h2>Profil Saya</h2>
        <a href="{{ route('account.security') }}" class="dashboard-icon-button" aria-label="Pengaturan keamanan akun">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 3 4 6v6c0 4 4 7 8 9 4-2 8-5 8-9V6l-8-3Z" stroke-width="1.5" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    </div>

    <div class="dashboard-profile">
        @if ($user->avatar)
            <img class="dashboard-avatar" src="{{ asset('storage/'.$user->avatar) }}" alt="Foto profil {{ $user->name }}" />
        @else
            <div class="dashboard-avatar dashboard-avatar-initials" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
        @endif
        <h2>{{ $user->name }}</h2>
        <p class="dashboard-profile-id">{{ $profileIdentifier }}</p>
        <span class="dashboard-role-label">{{ $user->role->label() }}</span>
    </div>

    <dl class="dashboard-profile-details">
        <div><dt>Tahun ajaran</dt><dd>{{ $academicYear?->name ?? 'Belum diatur' }}</dd></div>
        <div><dt>Semester</dt><dd>{{ $semester?->name ?? 'Belum diatur' }}</dd></div>
        @if ($enrollment?->classroom)
            <div><dt>Kelas</dt><dd>{{ $enrollment->classroom->name }}</dd></div>
            @if ($isStudent)
                <div><dt>Wali kelas</dt><dd>{{ $enrollment->classroom->homeroomTeacher?->name ?? 'Belum ditentukan' }}</dd></div>
            @endif
        @endif
        <div><dt>Sekolah</dt><dd>{{ $school?->name ?? 'Belum ditentukan' }}</dd></div>
    </dl>

    <x-dashboard-calendar />

    @if ($isTeacher)
        <section class="dashboard-agenda">
            <div class="dashboard-section-heading">
                <h2>Jadwal Mengajar</h2>
                <a href="{{ route('teacher.schedule.index') }}" class="dashboard-text-link">Lihat jadwal</a>
            </div>
            @forelse (($teachingAssignments ?? collect())->take(3) as $assignment)
                <div class="dashboard-agenda-item">
                    <div class="dashboard-agenda-symbol" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                    <div><h3>{{ $assignment->subject?->name ?? 'Mata Pelajaran' }}</h3><p>{{ $assignment->classroom?->name ?? 'Rombongan belajar' }} &middot; Penugasan mengajar</p></div>
                </div>
            @empty
                <p class="dashboard-empty">Belum ada penugasan mengajar.</p>
            @endforelse
        </section>
    @elseif ($isStudent)
        <section class="dashboard-agenda">
            <div class="dashboard-section-heading"><h2>Agenda Belajar</h2></div>
            <p class="dashboard-muted">Lihat waktu pelajaran dan ruang kelas pada jadwal belajar Anda.</p>
            <a href="{{ route('student.schedule.index') }}" class="dashboard-agenda-link">Buka jadwal pelajaran <span aria-hidden="true">&rarr;</span></a>
        </section>
    @elseif ($isParent && ($todaySchedules ?? collect())->isNotEmpty())
        <section class="dashboard-agenda">
            <div class="dashboard-section-heading"><h2>Pelajaran Hari Ini</h2></div>
            @foreach ($todaySchedules->take(3) as $schedule)
                <div class="dashboard-agenda-item">
                    <div class="dashboard-agenda-symbol" aria-hidden="true">{{ $loop->iteration }}</div>
                    <div><h3>{{ $schedule->teachingAssignment?->subject?->name ?? 'Mata Pelajaran' }}</h3><p>{{ substr($schedule->start_time, 0, 5) }} &ndash; {{ substr($schedule->end_time, 0, 5) }}</p></div>
                </div>
            @endforeach
        </section>
    @endif

    @if ($shortcuts)
        <section>
            <div class="dashboard-section-heading"><h2>{{ $isTeacher ? 'Pintasan Guru' : 'Akses Cepat' }}</h2></div>
            <div class="dashboard-shortcuts">
                @foreach ($shortcuts as [$label, $routeName, $description])
                    <a href="{{ route($routeName) }}"><div><strong>{{ $label }}</strong><span>{{ $description }}</span></div><span aria-hidden="true">&nearr;</span></a>
                @endforeach
            </div>
        </section>
    @endif
</aside>
