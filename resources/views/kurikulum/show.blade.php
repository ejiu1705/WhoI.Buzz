@extends('layouts.app')

@section('title', 'Detail Kategori SKPI — WhoI.Buzz')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="espresso-card rounded-[28px] p-7 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="px-2.5 py-0.5 rounded-full bg-[#F2AA00] text-[#4C2500] text-[11px] font-mono font-extrabold">{{ $category->code }} • {{ $category->competency_domain }}</span>
                <h1 class="mt-2 text-2xl font-extrabold text-[#FDF6DC]">{{ $category->name_id }}</h1>
                <p class="text-xs italic text-[#F9E7A9]/80">{{ $category->name_en }}</p>
            </div>
            <div class="flex gap-2">
                @can('update', $category)
                    <a href="{{ route('kurikulum-skpi.edit', $category) }}" class="rounded-xl bg-[#F2AA00] text-[#4C2500] px-4 py-2 text-xs font-extrabold">Ubah</a>
                @endcan
                <a href="{{ route('kurikulum-skpi.index') }}" class="rounded-xl bg-[#FDF6DC]/15 text-[#FDF6DC] px-4 py-2 text-xs font-bold">← Kembali</a>
            </div>
        </div>

        <div class="honey-card rounded-3xl p-6 text-xs space-y-3">
            <p><strong>Bobot Poin Default:</strong> +{{ $category->default_points }} Poin SKPI | <strong>Tingkat:</strong> {{ $category->level }} | <strong>Wajib:</strong> {{ $category->is_mandatory ? 'Ya' : 'Tidak' }}</p>
            <p><strong>Dibuat Oleh:</strong> {{ $category->creator?->name ?? 'Sistem' }} | <strong>Total Capaian Mahasiswa:</strong> {{ $category->activities->count() }}</p>
        </div>
    </div>
@endsection
