<x-layouts.app>
    <x-slot:headerTitle>
        Hubungan Akun & Data Anak
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Top Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-zinc-200">
            <div>
                <a href="{{ route('parent.dashboard') }}" class="text-xs text-zinc-500 hover:text-zinc-900 inline-flex items-center gap-1 font-semibold">
                    &larr; Kembali ke Dashboard
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 mt-1">
                    Hubungan Akun & Penautan Anak
                </h1>
                <p class="text-xs text-zinc-500">
                    Kelola relasi resmi akun orang tua dengan peserta didik di sekolah
                </p>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Card: Input Kode Penautan Mandiri -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs p-5 sm:p-6">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-extrabold text-sm sm:text-base text-zinc-900">Tautkan Akun Siswa Baru</h3>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Masukkan kode penautan unik 14 karakter yang diberikan oleh pihak sekolah (Wali Kelas / Bagian Kesiswaan).
                    </p>

                    <form action="{{ route('parent.children.claim-code') }}" method="POST" class="mt-4 flex flex-col sm:flex-row gap-3">
                        @csrf
                        <div class="flex-1">
                            <input
                                type="text"
                                name="code"
                                value="{{ old('code') }}"
                                placeholder="Contoh: EDUV-ABCD-1234"
                                required
                                class="w-full uppercase tracking-wider text-xs font-mono font-bold rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E] placeholder:normal-case placeholder:font-sans placeholder:font-normal"
                            >
                            @error('code')
                                <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C81423] text-white shadow-xs transition-colors shrink-0"
                        >
                            <span>Tautkan Akun</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Card: Daftar Anak yang Sudah Terhubung -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-sm text-zinc-900">Daftar Anak Terhubung</h3>
                    <p class="text-[11px] text-zinc-500">Status verifikasi dan relasi dengan akun Anda</p>
                </div>
                <span class="text-xs font-bold text-zinc-700 bg-zinc-100 px-2.5 py-1 rounded-lg">
                    {{ $relations->count() }} Terdaftar
                </span>
            </div>

            <div class="divide-y divide-zinc-100">
                @forelse ($relations as $rel)
                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-zinc-50/50 transition-colors">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-xl bg-zinc-100 text-zinc-800 flex items-center justify-center font-bold text-lg shrink-0">
                                {{ substr($rel->student?->name ?? 'A', 0, 2) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="font-extrabold text-sm text-zinc-900">{{ $rel->student?->name }}</h4>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $rel->statusBadgeClasses() }}">
                                        {{ $rel->statusLabel() }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-zinc-500 mt-1 flex-wrap">
                                    <span>Hubungan: <strong class="text-zinc-700">{{ $rel->relationshipLabel() }}</strong></span>
                                    <span>&bull;</span>
                                    <span>NIS: {{ $rel->student?->studentProfile?->nis ?? '-' }}</span>
                                    <span>&bull;</span>
                                    <span>Kelas: {{ $rel->student?->classroomEnrollments?->first()?->classroom?->name ?? '-' }}</span>
                                </div>
                                @if ($rel->notes)
                                    <p class="text-[11px] text-zinc-400 mt-0.5 italic">{{ $rel->notes }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
                            @if ($rel->isActive())
                                <a
                                    href="{{ route('parent.dashboard', ['child_id' => $rel->student_id]) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-zinc-100 hover:bg-brand-50 hover:text-brand-600 text-zinc-800 transition-colors"
                                >
                                    <span>Buka Dashboard</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @else
                                <span class="text-xs text-zinc-400 italic">Menunggu verifikasi admin sekolah</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-zinc-400">
                        <svg class="w-10 h-10 mx-auto mb-2 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <p class="text-xs text-zinc-500 font-medium">Belum ada akun anak yang ditautkan</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</x-layouts.app>
