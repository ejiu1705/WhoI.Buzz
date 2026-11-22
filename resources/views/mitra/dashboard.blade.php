@extends('layouts.app')

@section('title', 'Dashboard Mitra Karier / HRD — WhoI.Buzz')

@section('content')
    <section class="espresso-card rounded-[28px] p-7 sm:p-9 mb-7">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <span class="inline-block px-3 py-0.5 rounded-full bg-[#F2AA00] text-[#4C2500] text-[11px] font-extrabold">
                    Role: MITRA KARIER / HRD • Permission: cari-talenta
                </span>
                <h1 class="mt-2.5 text-2xl sm:text-3xl font-extrabold text-[#FDF6DC]">Portal Rekrutmen & Verifikasi Talenta</h1>
                <p class="text-xs sm:text-sm text-[#F9E7A9]/80 mt-1 max-w-2xl">
                    Cari kandidat mahasiswa berprestasi menggunakan <strong>Semantic Talent Matching (AI Vector)</strong> dan verifikasi keaslian QR/SKPI secara instan.
                </p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-center shrink-0">
                <div class="rounded-2xl bg-[#FDF6DC]/10 border border-[#F9E7A9]/20 px-4 py-2.5">
                    <p class="text-xl font-extrabold text-[#F2AA00]">{{ $stats['public_talents'] }}</p>
                    <p class="text-[10px] text-[#F9E7A9]/80">Talenta Opt-In</p>
                </div>
                <div class="rounded-2xl bg-[#FDF6DC]/10 border border-[#F9E7A9]/20 px-4 py-2.5">
                    <p class="text-xl font-extrabold text-emerald-300">{{ $stats['skpi_ready'] }}</p>
                    <p class="text-[10px] text-[#F9E7A9]/80">Siap Yudisium</p>
                </div>
                <div class="rounded-2xl bg-[#FDF6DC]/10 border border-[#F9E7A9]/20 px-4 py-2.5">
                    <p class="text-xl font-extrabold text-[#F2AA00]">{{ $stats['verified_credentials'] }}</p>
                    <p class="text-[10px] text-[#F9E7A9]/80">Kredensial Sah</p>
                </div>
                <div class="rounded-2xl bg-[#FDF6DC]/10 border border-[#F9E7A9]/20 px-4 py-2.5">
                    <p class="text-xl font-extrabold text-red-300">{{ $stats['revoked_alerts'] }}</p>
                    <p class="text-[10px] text-[#F9E7A9]/80">Status Revoked</p>
                </div>
            </div>
        </div>

        {{-- Semantic Search Bar --}}
        <form method="GET" action="{{ route('mitra.dashboard') }}" class="mt-6 grid grid-cols-1 md:grid-cols-12 gap-2.5">
            <input type="text" name="q" value="{{ $query }}"
                   placeholder="Cari kandidat semantik (cth: mahasiswa semester akhir keamanan siber CTF atau Laravel cloud)"
                   class="md:col-span-7 rounded-2xl bg-[#FDF6DC] text-[#4C2500] px-4 py-3 text-sm font-medium border-2 border-[#F2AA00]">
            <select name="prodi" class="md:col-span-3 rounded-2xl bg-[#FDF6DC] text-[#4C2500] px-3.5 py-3 text-xs font-bold">
                <option value="">Semua Program Studi</option>
                @foreach($prodiList as $p)
                    <option value="{{ $p }}" @selected($prodi === $p)>{{ $p }}</option>
                @endforeach
            </select>
            <button type="submit" class="md:col-span-2 rounded-2xl bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-[#FDF6DC] font-extrabold text-sm py-3 cursor-pointer transition">
                Cari Talenta →
            </button>
        </form>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Matched Candidates --}}
        <section class="lg:col-span-8 space-y-4">
            <h2 class="text-base font-extrabold text-[#4C2500]">Kandidat Terverifikasi Kampus ({{ $results->count() }})</h2>
            @forelse($results as $row)
                @php $p = $row['profile']; @endphp
                <div class="honey-card rounded-3xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full bg-[#4C2500] text-[#F2AA00] text-[10px] font-extrabold">Match {{ $row['similarity'] }}%</span>
                            <span class="font-mono text-xs font-bold text-[#CD7000]">{{ '@' . $p->public_slug }}</span>
                            <span class="text-xs font-semibold text-[#4C2500]/75">{{ $p->prodi_name }} • Smt {{ $p->semester }}</span>
                        </div>
                        <h3 class="text-lg font-extrabold text-[#4C2500]">{{ $p->user->name }}</h3>
                        <p class="text-xs font-semibold text-[#CD7000]">{{ $p->headline }}</p>
                        <div class="flex flex-wrap gap-1 pt-1">
                            @foreach(array_slice($p->skills_json ?? [], 0, 5) as $sk)
                                <span class="px-2 py-0.5 rounded-md bg-[#F9E7A9] text-[#4C2500] text-[10px] font-bold">{{ $sk }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex sm:flex-col items-end gap-2 shrink-0">
                        <span class="text-xs font-extrabold text-[#4C2500]">{{ $row['approved_points'] }} Poin SKPI</span>
                        <a href="{{ route('portfolio.show', $p->public_slug) }}" target="_blank"
                           class="rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F9E7A9] px-4 py-2 text-xs font-extrabold transition">
                            Buka Portofolio →
                        </a>
                    </div>
                </div>
            @empty
                <div class="honey-card rounded-3xl p-8 text-center text-xs text-[#4C2500]/70">Tidak ada kandidat cocok dengan filter saat ini.</div>
            @endforelse
        </section>

        {{-- Right: Quick Cryptographic QR Verifier --}}
        <aside class="lg:col-span-4 space-y-5">
            <div class="honey-card-strong rounded-3xl p-6">
                <h2 class="text-base font-extrabold text-[#4C2500]">Cek Cepat QR / SKPI Pelamar</h2>
                <p class="text-xs text-[#4C2500]/75 mt-1">Verifikasi tanda tangan Ed25519 dan status pencabutan (Revocation).</p>
                <form method="GET" action="{{ route('verify.index') }}" class="mt-3 space-y-2.5">
                    <input type="text" name="code" required placeholder="WB-SKPI-2026-NADIA1"
                           class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2.5 text-xs font-mono font-bold">
                    <button type="submit" class="w-full rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F2AA00] hover:text-[#FDF6DC] py-2.5 text-xs font-extrabold cursor-pointer">
                        Verifikasi Kriptografi →
                    </button>
                </form>
            </div>

            <div class="honey-card rounded-3xl p-6">
                <h3 class="text-sm font-extrabold text-[#4C2500] mb-3">Kredensial Terbaru</h3>
                <div class="space-y-2">
                    @foreach($recentCredentials as $rc)
                        <a href="{{ route('verify.show', $rc->registration_number) }}"
                           class="block rounded-xl bg-[#FDF6DC] hover:bg-[#F9E7A9] border border-[#4C2500]/15 px-3 py-2 text-xs transition">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono font-bold text-[#4C2500]">{{ $rc->registration_number }}</span>
                                <span class="text-[10px] font-extrabold {{ $rc->isRevoked() ? 'text-red-700' : 'text-emerald-800' }}">
                                    {{ $rc->isRevoked() ? 'REVOKED' : 'VALID' }}
                                </span>
                            </div>
                            <p class="text-[11px] text-[#4C2500]/75 truncate">{{ $rc->student?->name }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
@endsection
