@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-zinc-200">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('website.dashboard') }}" class="text-xs font-semibold text-zinc-500 hover:text-zinc-800">&larr; Ringkasan CMS</a>
            </div>
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">
                Kelola Fasilitas & Program Unggulan
            </h1>
            <p class="text-xs sm:text-sm text-zinc-500 mt-0.5">
                Kelola sarana prasarana sekolah dan program keahlian / kurikulum unggulan.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('website.facilities.create', ['category' => 'facility']) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-zinc-800 hover:bg-zinc-900 text-white transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Fasilitas</span>
            </a>
            <a href="{{ route('website.facilities.create', ['category' => 'featured_program']) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C71827] text-white transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Program Unggulan</span>
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Col 1: Program Unggulan -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-zinc-100 flex items-center justify-between bg-rose-50/50">
                <h2 class="font-bold text-sm text-zinc-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#E11D2E]"></span>
                    <span>Program Unggulan ({{ $programs->count() }})</span>
                </h2>
                <a href="{{ route('website.facilities.create', ['category' => 'featured_program']) }}" class="text-xs font-bold text-[#E11D2E]">
                    + Tambah
                </a>
            </div>

            @if ($programs->isEmpty())
                <div class="p-8 text-center text-zinc-400 text-xs">Belum ada program unggulan.</div>
            @else
                <div class="divide-y divide-zinc-100 text-xs">
                    @foreach ($programs as $prog)
                        <div class="p-4 flex items-start justify-between gap-3 hover:bg-zinc-50">
                            <div class="min-w-0">
                                <h3 class="font-bold text-zinc-900">{{ $prog->name }}</h3>
                                <p class="text-zinc-500 text-[11px] mt-0.5 line-clamp-2">{{ $prog->description }}</p>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="{{ route('website.facilities.edit', $prog) }}" class="px-2 py-1 text-[11px] font-semibold rounded bg-zinc-100 hover:bg-zinc-200">Edit</a>
                                <form method="POST" action="{{ route('website.facilities.destroy', $prog) }}" onsubmit="return confirm('Hapus program ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1 text-[11px] font-semibold rounded hover:bg-rose-50 text-rose-600">Hapus</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Col 2: Sarana & Fasilitas -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-zinc-100 flex items-center justify-between bg-emerald-50/50">
                <h2 class="font-bold text-sm text-zinc-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                    <span>Sarana & Fasilitas ({{ $facilities->count() }})</span>
                </h2>
                <a href="{{ route('website.facilities.create', ['category' => 'facility']) }}" class="text-xs font-bold text-emerald-700">
                    + Tambah
                </a>
            </div>

            @if ($facilities->isEmpty())
                <div class="p-8 text-center text-zinc-400 text-xs">Belum ada fasilitas kampus.</div>
            @else
                <div class="divide-y divide-zinc-100 text-xs">
                    @foreach ($facilities as $fac)
                        <div class="p-4 flex items-start justify-between gap-3 hover:bg-zinc-50">
                            <div class="min-w-0">
                                <h3 class="font-bold text-zinc-900">{{ $fac->name }}</h3>
                                <p class="text-zinc-500 text-[11px] mt-0.5 line-clamp-2">{{ $fac->description }}</p>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="{{ route('website.facilities.edit', $fac) }}" class="px-2 py-1 text-[11px] font-semibold rounded bg-zinc-100 hover:bg-zinc-200">Edit</a>
                                <form method="POST" action="{{ route('website.facilities.destroy', $fac) }}" onsubmit="return confirm('Hapus fasilitas ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1 text-[11px] font-semibold rounded hover:bg-rose-50 text-rose-600">Hapus</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
