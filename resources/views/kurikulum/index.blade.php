@extends('layouts.app')

@section('title', 'Kurikulum SKPI — WhoI.Buzz')

@section('content')
    <section class="espresso-card rounded-[28px] p-6 sm:p-7 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="inline-block px-3 py-0.5 rounded-full bg-[#F2AA00] text-[#4C2500] text-[11px] font-extrabold">
                Standar Poin Akademik 2026
            </span>
            <h1 class="mt-2 text-2xl font-extrabold text-[#FDF6DC]">Daftar Kategori Kurikulum SKPI</h1>
            <p class="text-xs text-[#F9E7A9]/90 mt-0.5">
                Acuan bobot poin dan kategori wajib untuk pengajuan Surat Keterangan Pendamping Ijazah mahasiswa.
            </p>
        </div>
        @can('kelola-kurikulum-skpi')
            <a href="{{ route('kurikulum-skpi.create') }}"
               class="rounded-xl bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-[#FDF6DC] px-5 py-2.5 text-xs font-extrabold transition shadow shrink-0">
                + Tambah Kategori SKPI
            </a>
        @endcan
    </section>

    {{-- Aggregation Summary (Modul 3 DB::table()->groupBy()) --}}
    <section class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        @foreach($domainSummary as $sum)
            <div class="honey-card rounded-2xl p-4 text-center">
                <p class="text-[11px] font-extrabold uppercase text-[#CD7000]">{{ $sum->competency_domain }}</p>
                <p class="text-xl font-extrabold text-[#4C2500] mt-1">{{ $sum->jumlah_kategori }} Kat.</p>
                <p class="text-[11px] text-[#4C2500]/75">{{ $sum->total_bobot_poin }} total poin</p>
            </div>
        @endforeach
    </section>

    {{-- Search & Filter (Modul 3: GET + when() + Local Query Scopes + withQueryString()) --}}
    <section class="honey-card rounded-3xl p-5 mb-6">
        <form method="GET" action="{{ route('kurikulum-skpi.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari kode atau nama kategori..."
                   class="md:col-span-5 rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3.5 py-2 text-xs">
            <select name="domain" class="md:col-span-3 rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs font-bold">
                <option value="">Semua Domain</option>
                @foreach(['Leadership', 'Technical', 'Communication', 'Social', 'Academic'] as $dom)
                    <option value="{{ $dom }}" @selected(request('domain') === $dom)>{{ $dom }}</option>
                @endforeach
            </select>
            <label class="md:col-span-2 flex items-center gap-2 text-xs font-bold text-[#4C2500]">
                <input type="checkbox" name="hanya_wajib" value="1" @checked(request()->boolean('hanya_wajib'))>
                Hanya Wajib
            </label>
            <button type="submit" class="md:col-span-2 rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F9E7A9] py-2 text-xs font-extrabold cursor-pointer">
                Cari / Filter
            </button>
        </form>
    </section>

    {{-- Data Table --}}
    <section class="honey-card rounded-3xl p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#4C2500]/15 text-[#CD7000] uppercase text-[10px]">
                        <th class="py-2 pr-3">No</th>
                        <th class="py-2 pr-3">Kode</th>
                        <th class="py-2 pr-3">Nama Kategori (ID / EN)</th>
                        <th class="py-2 pr-3">Domain & Tingkat</th>
                        <th class="py-2 pr-3 text-center">Poin</th>
                        <th class="py-2 pr-3">Pengelola</th>
                        <th class="py-2 pr-3 text-center">Dipakai</th>
                        <th class="py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#4C2500]/10">
                    @forelse($categories as $cat)
                        <tr class="align-middle">
                            <td class="py-3 pr-3 font-mono">{{ ($categories->firstItem() ?? 1) + $loop->index }}</td>
                            <td class="py-3 pr-3 font-mono font-extrabold text-[#4C2500] whitespace-nowrap">{{ $cat->code }}</td>
                            <td class="py-3 pr-3">
                                <p class="font-bold text-[#4C2500]">
                                    {{ $cat->name_id }}
                                    @if($cat->is_mandatory)
                                        <span class="ml-1 inline-flex items-center whitespace-nowrap px-1.5 py-0.5 rounded bg-[#F2AA00] text-[#4C2500] text-[9px] font-extrabold">WAJIB</span>
                                    @endif
                                </p>
                                <p class="text-[11px] italic text-[#4C2500]/80">{{ $cat->name_en }}</p>
                            </td>
                            <td class="py-3 pr-3 whitespace-nowrap">{{ $cat->competency_domain }} • {{ $cat->level }}</td>
                            <td class="py-3 pr-3 text-center font-mono font-extrabold text-[#CD7000]">+{{ $cat->default_points }}</td>
                            <td class="py-3 pr-3 text-[11px] whitespace-nowrap">{{ $cat->creator?->name ?? 'Sistem' }}</td>
                            <td class="py-3 pr-3 text-center font-mono">{{ $cat->activities_count }}</td>
                            <td class="py-3 whitespace-nowrap">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <a href="{{ route('kurikulum-skpi.show', $cat) }}" class="px-2.5 py-1 rounded-lg bg-[#F9E7A9] text-[#4C2500] font-bold">Detail</a>
                                    @can('update', $cat)
                                        <a href="{{ route('kurikulum-skpi.edit', $cat) }}" class="px-2.5 py-1 rounded-lg bg-[#F2AA00] text-[#4C2500] font-extrabold">Ubah</a>
                                    @endcan
                                    @can('delete', $cat)
                                        <form method="POST" action="{{ route('kurikulum-skpi.destroy', $cat) }}" onsubmit="return confirm('Hapus kategori {{ $cat->code }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-red-800 text-white font-bold cursor-pointer">Hapus</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-[#4C2500]/80">Belum ada kategori kurikulum.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $categories->links() }}</div>
    </section>
@endsection
