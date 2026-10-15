<x-layouts.app>
    <x-slot:headerTitle>
        Edit Jadwal Pelajaran
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">
        @php
            $ta = $schedule->teachingAssignment;
        @endphp

        <!-- Top Back Navigation & Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('schedules.index') }}"
                    class="p-2 rounded-lg text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 transition-colors"
                    title="Kembali ke Jadwal"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                        Edit Jadwal Pelajaran #{{ $schedule->id }}
                    </h1>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Perbarui rincian rombel, guru, waktu, atau ruang. Sistem akan memvalidasi bentrok jadwal terkini secara otomatis.
                    </p>
                </div>
            </div>

            <x-badge variant="{{ $schedule->is_active ? 'success' : 'neutral' }}" size="sm">
                {{ $schedule->is_active ? 'Status Aktif' : 'Nonaktif' }}
            </x-badge>
        </div>

        <!-- Validation Alerts -->
        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                <div class="font-bold mb-1">Gagal Memperbarui Jadwal:</div>
                <ul class="list-disc list-inside space-y-1 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <!-- Form Card -->
        <x-card padding>
            <form method="POST" action="{{ route('schedules.update', $schedule) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Section 1: Entitas Akademik (Kelas, Mapel, Guru) -->
                <div>
                    <h2 class="text-sm font-bold text-zinc-900 border-b border-zinc-100 pb-2 mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-brand-600"></span>
                        1. Informasi Akademik & Penugasan
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Kelas / Rombel -->
                        <div>
                            <label for="classroom_id" class="block text-xs font-bold text-zinc-700 mb-1">
                                Rombongan Belajar (Kelas) <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="classroom_id"
                                name="classroom_id"
                                required
                                class="w-full rounded-lg border {{ $errors->has('classroom_id') ? 'border-rose-400 bg-rose-50/20' : 'border-zinc-300' }} text-zinc-900 text-xs py-2.5 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                            >
                                @foreach ($classrooms as $cls)
                                    <option value="{{ $cls->id }}" @selected(old('classroom_id', $ta?->classroom_id) == $cls->id)>
                                        {{ $cls->name }} ({{ $cls->academicYear?->name ?? 'Tahun Aktif' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('classroom_id')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Mata Pelajaran -->
                        <div>
                            <label for="subject_id" class="block text-xs font-bold text-zinc-700 mb-1">
                                Mata Pelajaran <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="subject_id"
                                name="subject_id"
                                required
                                class="w-full rounded-lg border {{ $errors->has('subject_id') ? 'border-rose-400 bg-rose-50/20' : 'border-zinc-300' }} text-zinc-900 text-xs py-2.5 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                            >
                                @foreach ($subjects as $sbj)
                                    <option value="{{ $sbj->id }}" @selected(old('subject_id', $ta?->subject_id) == $sbj->id)>
                                        {{ $sbj->name }} ({{ $sbj->code ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Guru Pengampu -->
                        <div class="sm:col-span-2">
                            <label for="teacher_id" class="block text-xs font-bold text-zinc-700 mb-1">
                                Guru Pengampu <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="teacher_id"
                                name="teacher_id"
                                required
                                class="w-full rounded-lg border {{ $errors->has('teacher_id') ? 'border-rose-400 bg-rose-50/20' : 'border-zinc-300' }} text-zinc-900 text-xs py-2.5 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                            >
                                @foreach ($teachers as $t)
                                    <option value="{{ $t->id }}" @selected(old('teacher_id', $ta?->teacher_id) == $t->id)>
                                        {{ $t->name }} ({{ $t->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('teacher_id')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Waktu & Tempat (Hari, Jam Mulai, Jam Selesai, Ruang) -->
                <div>
                    <h2 class="text-sm font-bold text-zinc-900 border-b border-zinc-100 pb-2 mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-brand-600"></span>
                        2. Jadwal & Alokasi Ruang
                    </h2>

                    <div class="space-y-4">
                        <!-- Hari Belajar -->
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-2">
                                Hari Belajar <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                                @foreach ($days as $num => $label)
                                    <label class="cursor-pointer">
                                        <input
                                            type="radio"
                                            name="day_of_week"
                                            value="{{ $num }}"
                                            class="sr-only peer"
                                            @checked((int) old('day_of_week', $schedule->day_of_week) === (int) $num)
                                        >
                                        <div class="p-2.5 rounded-lg border border-zinc-200 text-center text-xs font-bold transition-all peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:text-brand-700 hover:bg-zinc-50">
                                            {{ $label }}
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('day_of_week')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Interval Waktu -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="start_time" class="block text-xs font-bold text-zinc-700 mb-1">
                                    Waktu Mulai <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="time"
                                    id="start_time"
                                    name="start_time"
                                    value="{{ old('start_time', $schedule->formattedStartTime()) }}"
                                    required
                                    class="w-full rounded-lg border {{ $errors->has('start_time') ? 'border-rose-400 bg-rose-50/20' : 'border-zinc-300' }} text-zinc-900 text-xs py-2.5 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                >
                                @error('start_time')
                                    <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="end_time" class="block text-xs font-bold text-zinc-700 mb-1">
                                    Waktu Selesai <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="time"
                                    id="end_time"
                                    name="end_time"
                                    value="{{ old('end_time', $schedule->formattedEndTime()) }}"
                                    required
                                    class="w-full rounded-lg border {{ $errors->has('end_time') ? 'border-rose-400 bg-rose-50/20' : 'border-zinc-300' }} text-zinc-900 text-xs py-2.5 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                >
                                @error('end_time')
                                    <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Ruang / Lokasi Fasilitas -->
                        <div>
                            <label for="room" class="block text-xs font-bold text-zinc-700 mb-1">
                                Ruang / Fasilitas Belajar <span class="text-zinc-400 font-normal">(Opsional)</span>
                            </label>
                            <input
                                type="text"
                                id="room"
                                name="room"
                                list="room-suggestions"
                                value="{{ old('room', $schedule->room) }}"
                                placeholder="Contoh: Ruang 101, Lab Komputer 1, Lapangan Basket"
                                class="w-full rounded-lg border {{ $errors->has('room') ? 'border-rose-400 bg-rose-50/20' : 'border-zinc-300' }} text-zinc-900 text-xs py-2.5 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                            >
                            <datalist id="room-suggestions">
                                @foreach ($existingRooms as $r)
                                    <option value="{{ $r }}">
                                @endforeach
                            </datalist>
                            <p class="text-[11px] text-zinc-500 mt-1">
                                Pemeriksaan bentrok secara otomatis mengecualikan jadwal ini dari pemeriksaan konflik terhadap dirinya sendiri.
                            </p>
                            @error('room')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 3: Pengaturan Status -->
                <div class="pt-2 border-t border-zinc-100">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="rounded border-zinc-300 text-brand-600 focus:ring-brand-500"
                            @checked(old('is_active', $schedule->is_active))
                        >
                        <span class="text-xs font-semibold text-zinc-800">
                            Jadwal aktif pada kalender pembelajaran
                        </span>
                    </label>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-zinc-100 flex items-center justify-between">
                    <button
                        type="button"
                        onclick="if(confirm('Hapus jadwal pelajaran ini?')) { document.getElementById('delete-schedule-form').submit(); }"
                        class="text-xs font-bold text-rose-600 hover:text-rose-700 hover:underline"
                    >
                        Hapus Jadwal
                    </button>

                    <div class="flex items-center gap-3">
                        <x-button href="{{ route('schedules.index') }}" variant="outline" size="sm">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary" size="sm">
                            Simpan Perubahan Jadwal
                        </x-button>
                    </div>
                </div>
            </form>

            <form id="delete-schedule-form" method="POST" action="{{ route('schedules.destroy', $schedule) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </x-card>
    </div>
</x-layouts.app>
