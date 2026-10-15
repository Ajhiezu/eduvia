<x-layouts.app>
    <x-slot:headerTitle>
        Konfigurasi Bobot Penilaian Semester
    </x-slot:headerTitle>

    <div class="space-y-6">
        @if (session('success'))
            <x-alert variant="success" dismissible>
                {{ session('success') }}
            </x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" dismissible>
                {{ session('error') }}
            </x-alert>
        @endif

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Konfigurasi Bobot Penilaian
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Atur skema proporsi bobot tugas, kuis/formatif, UTS, UAS, dan batas KKM kelulusan untuk perhitungan nilai akhir semester.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <x-button href="{{ route('reports.gradebook.index') }}" variant="secondary" size="sm">
                    Kembali ke Buku Nilai
                </x-button>
                <x-button href="{{ route('reports.weights.create') }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Skema Bobot
                </x-button>
            </div>
        </div>

        <!-- Table of Configurations -->
        <x-card padding="none" class="border-zinc-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-zinc-700">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Nama Skema</th>
                            <th class="py-3 px-4">Ruang Lingkup</th>
                            <th class="py-3 px-4 text-center">Tugas</th>
                            <th class="py-3 px-4 text-center">Kuis</th>
                            <th class="py-3 px-4 text-center">UTS</th>
                            <th class="py-3 px-4 text-center">UAS</th>
                            <th class="py-3 px-4 text-center">KKM</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse($configurations as $config)
                            <tr class="hover:bg-zinc-50/75 transition-colors">
                                <td class="py-3.5 px-4 font-semibold text-zinc-900">
                                    {{ $config->name }}
                                </td>
                                <td class="py-3.5 px-4 text-zinc-600">
                                    @if($config->subject)
                                        <span class="font-medium text-brand-600">{{ $config->subject->name }}</span>
                                    @else
                                        <span class="text-zinc-500 italic">Semua Mata Pelajaran (Standar Sekolah)</span>
                                    @endif
                                    @if($config->academicYear)
                                        <div class="text-[11px] text-zinc-400 mt-0.5">
                                            {{ $config->academicYear->name }} {{ $config->semester ? '• '.$config->semester->name : '' }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-zinc-800">{{ number_format($config->assignment_weight, 0) }}%</td>
                                <td class="py-3.5 px-4 text-center font-bold text-zinc-800">{{ number_format($config->quiz_weight, 0) }}%</td>
                                <td class="py-3.5 px-4 text-center font-bold text-zinc-800">{{ number_format($config->midterm_weight, 0) }}%</td>
                                <td class="py-3.5 px-4 text-center font-bold text-zinc-800">{{ number_format($config->final_weight, 0) }}%</td>
                                <td class="py-3.5 px-4 text-center font-extrabold text-brand-700">{{ number_format($config->passing_grade, 0) }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    <x-badge :variant="$config->is_active ? 'success' : 'neutral'" size="xs">
                                        {{ $config->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-badge>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-button href="{{ route('reports.weights.edit', $config) }}" variant="ghost" size="xs">
                                            Edit
                                        </x-button>
                                        <form action="{{ route('reports.weights.destroy', $config) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus skema bobot ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-semibold px-2 py-1 rounded hover:bg-red-50">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-zinc-400">
                                    Belum ada konfigurasi bobot penilaian tersimpan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($configurations->hasPages())
                <div class="p-4 border-t border-zinc-200">
                    {{ $configurations->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
