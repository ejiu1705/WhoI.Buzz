@extends('layouts.app')

@section('title', 'Laporan Progres SKPI Mahasiswa — WhoI.Buzz')

@section('content')
    <section class="espresso-card rounded-[28px] p-6 sm:p-8 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="inline-block px-3 py-0.5 rounded-full bg-[#F2AA00] text-[#4C2500] text-[11px] font-extrabold">
                Multiple Role Route: admin | dosen • Permission: lihat progres
            </span>
            <h1 class="mt-2 text-2xl font-extrabold text-[#FDF6DC]">Laporan Progres Poin SKPI Mahasiswa</h1>
            <p class="text-xs text-[#F9E7A9]/80 mt-0.5">
                Halaman ini hanya dapat diakses oleh role <strong>Admin</strong> atau <strong>Dosen</strong> (Mahasiswa & Mitra otomatis ditolak HTTP 403 Forbidden).
            </p>
        </div>
        <a href="{{ $viewer->dashboardRoute() }}" class="rounded-xl bg-[#F2AA00] text-[#4C2500] px-4 py-2.5 text-xs font-extrabold shrink-0">← Dashboard</a>
    </section>

    <section class="honey-card rounded-3xl p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#4C2500]/15 text-[#CD7000] uppercase text-[10px]">
                        <th class="py-2 pr-3">NIM</th>
                        <th class="py-2 pr-3">Mahasiswa & Prodi</th>
                        <th class="py-2 pr-3">Dosen PA</th>
                        <th class="py-2 pr-3 text-center">Capaian Sah</th>
                        <th class="py-2 pr-3 text-center">Antrean</th>
                        <th class="py-2 pr-3">Progres Poin</th>
                        <th class="py-2">Status SKPI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#4C2500]/10">
                    @foreach($profiles as $row)
                        @php
                            $p = $row['profile'];
                            $pct = min(100, $row['approved_points']);
                        @endphp
                        <tr>
                            <td class="py-3 pr-3 font-mono font-bold">{{ $p->nim }}</td>
                            <td class="py-3 pr-3">
                                <p class="font-extrabold text-[#4C2500]">{{ $p->user->name }}</p>
                                <p class="text-[11px] text-[#4C2500]/70">{{ $p->prodi_name }} • Semester {{ $p->semester }}</p>
                            </td>
                            <td class="py-3 pr-3 text-[11px]">{{ $p->advisor?->name ?? '-' }}</td>
                            <td class="py-3 pr-3 text-center font-mono font-bold">{{ $row['approved_count'] }}</td>
                            <td class="py-3 pr-3 text-center font-mono font-bold text-[#CD7000]">{{ $row['pending_count'] }}</td>
                            <td class="py-3 pr-3 min-w-[160px]">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-2.5 rounded-full bg-[#4C2500]/15 overflow-hidden">
                                        <div class="h-full bg-[#F2AA00]" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="font-mono font-extrabold">{{ $row['approved_points'] }}/100</span>
                                </div>
                            </td>
                            <td class="py-3">
                                @if($row['ready_skpi'])
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-800 text-emerald-100 text-[10px] font-extrabold">
                                        <x-icon name="check" class="w-3 h-3" />
                                        <span>SIAP SKPI</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full bg-[#F9E7A9] text-[#4C2500] text-[10px] font-bold">PROGRES</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
