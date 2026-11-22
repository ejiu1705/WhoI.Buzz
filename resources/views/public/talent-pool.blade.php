@extends('layouts.app')

@section('title', 'Semantic Talent Pool (AI Vector Search) — WhoI.Buzz')

@section('content')
    <section class="espresso-card rounded-[28px] p-7 sm:p-9 mb-7">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#F2AA00] text-[#4C2500] text-[11px] font-extrabold">
            <x-icon name="sparkles" class="w-3.5 h-3.5" />
            <span>Semantic Talent Matching (pgvector Cosine Similarity)</span>
        </span>
        <h1 class="mt-2.5 text-2xl sm:text-3xl font-extrabold text-[#FDF6DC]">Cari Talenta Kampus Secara Semantik</h1>
        <p class="text-xs sm:text-sm text-[#F9E7A9]/80 mt-1 max-w-3xl">
            Bukan sekadar pencarian kata kunci kaku—ketik deskripsi kebutuhan kandidat dalam bahasa natural. Sistem hanya menampilkan mahasiswa yang secara sadar mengaktifkan <em>Public Opt-In</em> (UU PDP).
        </p>

        <form method="GET" action="{{ route('talent.index') }}" class="mt-5 grid grid-cols-1 md:grid-cols-12 gap-2.5">
            <input type="text" name="q" value="{{ $query }}"
                   placeholder="Contoh: Mahasiswa semester akhir yang mahir keamanan siber dan pernah menjuarai CTF"
                   class="md:col-span-7 rounded-2xl bg-[#FDF6DC] text-[#4C2500] px-4 py-3 text-sm font-medium border-2 border-[#F2AA00]">
            <select name="prodi" class="md:col-span-3 rounded-2xl bg-[#FDF6DC] text-[#4C2500] px-3.5 py-3 text-xs font-bold">
                <option value="">Semua Program Studi</option>
                @foreach($prodiList as $p)
                    <option value="{{ $p }}" @selected($prodi === $p)>{{ $p }}</option>
                @endforeach
            </select>
            <button type="submit"
                    class="md:col-span-2 rounded-2xl bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-[#FDF6DC] font-extrabold text-sm px-5 py-3 cursor-pointer transition">
                Cari AI →
            </button>
        </form>

        <div class="mt-3.5 flex flex-wrap items-center gap-2 text-[11px]">
            <span class="text-[#F9E7A9]/70">Contoh kueri semantik:</span>
            @foreach([
                'Mahasiswa semester akhir yang mahir keamanan siber dan pernah menjuarai CTF',
                'Fullstack Laravel backend developer dengan pengalaman kepemimpinan organisasi',
                'Pembicara seminar cloud AWS & Zero-Trust',
            ] as $sampleQ)
                <a href="{{ route('talent.index', ['q' => $sampleQ]) }}"
                   class="px-3 py-1 rounded-full bg-[#FDF6DC]/12 hover:bg-[#F2AA00] hover:text-[#4C2500] text-[#F9E7A9] transition">
                    “{{ \Illuminate\Support\Str::limit($sampleQ, 48) }}”
                </a>
            @endforeach
        </div>
    </section>

    {{-- RESULTS GRID --}}
    <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($results as $row)
            @php $p = $row['profile']; @endphp
            <div class="honey-card rounded-3xl p-6 flex flex-col justify-between hover:border-[#F2AA00] transition">
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="font-mono text-xs font-bold text-[#CD7000]">{{ '@' . $p->public_slug }}</span>
                            <h2 class="text-xl font-extrabold text-[#4C2500]">{{ $p->user->name }}</h2>
                            <p class="text-xs font-semibold text-[#4C2500]/70">{{ $p->prodi_name }} • Semester {{ $p->semester }} • {{ $row['approved_points'] }} Poin SKPI</p>
                        </div>
                        <div class="rounded-2xl bg-[#4C2500] text-[#F2AA00] px-3.5 py-2 text-center shrink-0">
                            <p class="text-lg font-extrabold leading-none">{{ $row['similarity'] }}%</p>
                            <p class="text-[9px] uppercase tracking-wider text-[#F9E7A9] mt-0.5">Vector Match</p>
                        </div>
                    </div>

                    <p class="mt-3 text-xs font-bold text-[#CD7000]">{{ $p->headline }}</p>
                    <p class="mt-1.5 text-xs text-[#4C2500]/80 leading-relaxed">{{ $p->bio }}</p>

                    @if(!empty($row['matched_concepts']))
                        <div class="mt-3 flex flex-wrap items-center gap-1.5">
                            <span class="text-[10px] font-extrabold uppercase text-[#4C2500]/60">Dimensi Vektor Cocok:</span>
                            @foreach($row['matched_concepts'] as $concept)
                                <span class="px-2 py-0.5 rounded-full bg-[#F2AA00]/30 text-[#4C2500] text-[10px] font-extrabold font-mono">{{ $concept }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-3 space-y-1.5">
                        @foreach($p->user->activities->take(3) as $act)
                            <div class="rounded-xl bg-[#FDF6DC] border border-[#4C2500]/12 px-3 py-2 text-xs flex items-center justify-between gap-2">
                                <span class="font-semibold text-[#4C2500] truncate">{{ $act->title_id }}</span>
                                <span class="shrink-0 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-[#F9E7A9] text-[#4C2500]">Tier {{ $act->trust_tier }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-[#4C2500]/10 flex items-center justify-between">
                    <span class="text-xs text-[#4C2500]/70">{{ $p->user->activities->count() }} Kredensial Publik Tervalidasi</span>
                    <a href="{{ route('portfolio.show', $p->public_slug) }}"
                       class="rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F9E7A9] hover:text-[#FDF6DC] px-4 py-2 text-xs font-extrabold transition">
                        Buka E-Portfolio →
                    </a>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 honey-card rounded-3xl p-10 text-center">
                <h2 class="text-lg font-extrabold text-[#4C2500]">Belum ada kandidat publik yang cocok dengan kueri semantik tersebut.</h2>
                <p class="text-xs text-[#4C2500]/70 mt-1">Coba perluas kata kunci atau hapus filter program studi.</p>
            </div>
        @endforelse
    </section>
@endsection
