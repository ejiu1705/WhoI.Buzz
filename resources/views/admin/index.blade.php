@extends('layouts.app')

@section('title', 'Admin Kemahasiswaan Hub — WhoI.Buzz')

@section('content')
    <section class="espresso-card rounded-[28px] p-6 sm:p-8 mb-7">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#F2AA00] text-[#4C2500] text-[11px] font-extrabold">
                    <x-icon name="building" class="w-3.5 h-3.5" />
                    <span>Direktorat Kemahasiswaan • Zero-Trust Governance</span>
                </span>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-[#FDF6DC]">Admin Hub, Auto-Claim &amp; Revocation Registry</h1>
                <p class="text-xs text-[#F9E7A9]/80 mt-1">
                    Terbitkan sertifikat internal kampus Tier 1 (Gold Badge) secara massal, kelola akun &amp; role, kurikulum SKPI, dan audit sistem.
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @can('kelola pengguna')
                        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-[#FDF6DC] px-4 py-2 text-xs font-extrabold transition">
                            <x-icon name="users" class="w-4 h-4" />
                            <span>Kelola Akun Pengguna (CRUD)</span>
                        </a>
                    @endcan
                    <a href="{{ route('kurikulum-skpi.index') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#FDF6DC]/15 hover:bg-[#F2AA00] text-[#FDF6DC] hover:text-[#4C2500] px-4 py-2 text-xs font-bold transition">
                        <x-icon name="book-open" class="w-4 h-4" />
                        <span>CRUD Kurikulum SKPI</span>
                    </a>
                    <a href="{{ route('laporan.progres') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#FDF6DC]/15 hover:bg-[#F2AA00] text-[#FDF6DC] hover:text-[#4C2500] px-4 py-2 text-xs font-bold transition">
                        <x-icon name="chart-bar" class="w-4 h-4" />
                        <span>Laporan Progres</span>
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-center">
                <div class="rounded-2xl bg-[#FDF6DC]/10 border border-[#F9E7A9]/20 px-4 py-2.5">
                    <p class="text-xl font-extrabold text-[#F2AA00]">{{ $summary['total_students'] }}</p>
                    <p class="text-[10px] text-[#F9E7A9]/80">Mahasiswa</p>
                </div>
                <div class="rounded-2xl bg-[#FDF6DC]/10 border border-[#F9E7A9]/20 px-4 py-2.5">
                    <p class="text-xl font-extrabold text-[#F2AA00]">{{ $summary['tier1_autoclaims'] }}</p>
                    <p class="text-[10px] text-[#F9E7A9]/80">Tier 1 Auto-Claim</p>
                </div>
                <div class="rounded-2xl bg-[#FDF6DC]/10 border border-[#F9E7A9]/20 px-4 py-2.5">
                    <p class="text-xl font-extrabold text-emerald-300">{{ $summary['active_credentials'] }}</p>
                    <p class="text-[10px] text-[#F9E7A9]/80">Kredensial Sah</p>
                </div>
                <div class="rounded-2xl bg-[#FDF6DC]/10 border border-[#F9E7A9]/20 px-4 py-2.5">
                    <p class="text-xl font-extrabold text-red-300">{{ $summary['revoked_credentials'] }}</p>
                    <p class="text-[10px] text-[#F9E7A9]/80">Dicabut (Revoked)</p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-7">
        {{-- AUTO-CLAIM INTERNAL CAMPUS BATCH ISSUER --}}
        <section class="lg:col-span-7 honey-card-strong rounded-3xl p-6">
            <div class="flex items-center justify-between gap-2 mb-4">
                <div>
                    <x-trust-badge :tier="1" />
                    <h2 class="mt-2 text-lg font-extrabold text-[#4C2500]">Auto-Claim Sertifikat Internal Kampus</h2>
                    <p class="text-xs text-[#4C2500]/75">Impor daftar NIM peserta lulus acara kampus → langsung masuk ke portofolio mahasiswa dengan QR Ed25519 tanpa perlu unggah manual.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.auto-claim') }}" class="space-y-3.5">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-[#4C2500] mb-1">Nama Kegiatan (ID)</label>
                        <input type="text" name="title_id" required placeholder="Seminar Nasional AI & Zero-Trust Campus 2026"
                               class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#4C2500] mb-1">Event Title (EN — SKPI)</label>
                        <input type="text" name="title_en" required placeholder="National Seminar on AI & Zero-Trust Campus 2026"
                               class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-[#4C2500] mb-1">Unit Penyelenggara</label>
                        <input type="text" name="organizer" required value="Direktorat Kemahasiswaan Kampus"
                               class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#4C2500] mb-1">Peran Peserta</label>
                        <input type="text" name="role_in_activity" required value="Peserta Terverifikasi"
                               class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#4C2500] mb-1">Tanggal</label>
                        <input type="date" name="activity_date" required value="{{ now()->format('Y-m-d') }}"
                               class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#4C2500] mb-1">Kategori Poin SKPI</label>
                    <select name="category_id" required class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->code }} — {{ $cat->name_id }} (+{{ $cat->default_points }} pts)</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#4C2500] mb-1">Daftar NIM Penerima (pisahkan koma / baris baru)</label>
                    <textarea name="nims_list" rows="2" required
                              class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs font-mono text-[#4C2500]">{{ $students->pluck('nim')->implode(', ') }}</textarea>
                    <p class="mt-1 text-[11px] text-[#4C2500]/70">NIM tersedia: {{ $students->map(fn($s) => $s->nim.' ('.$s->user->name.')')->implode(' • ') }}</p>
                </div>

                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F2AA00] hover:text-[#FDF6DC] py-3 text-xs font-extrabold transition cursor-pointer shadow-md">
                    <x-icon name="star" class="w-4 h-4" />
                    <span>Terbitkan Batch Auto-Claim Tier 1 (Gold Badge) Sekarang</span>
                </button>
            </form>
        </section>

        {{-- SKPI CURRICULUM MASTER --}}
        <section class="lg:col-span-5 honey-card rounded-3xl p-6">
            <h2 class="text-lg font-extrabold text-[#4C2500]">Master Kurikulum SKPI (2026-V1)</h2>
            <p class="text-xs text-[#4C2500]/70 mb-4">Kelola bobot poin, domain kompetensi Skill Radar, dan status wajib.</p>

            <div class="space-y-2 max-h-[240px] overflow-y-auto pr-1 mb-4">
                @foreach($categories as $cat)
                    <div class="rounded-xl bg-[#FDF6DC] border border-[#4C2500]/15 px-3 py-2 text-xs flex items-center justify-between gap-2">
                        <div>
                            <p class="font-extrabold text-[#4C2500]">{{ $cat->name_id }}</p>
                            <p class="text-[11px] text-[#4C2500]/70">{{ $cat->code }} • {{ $cat->competency_domain }} • {{ $cat->level }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="px-2 py-0.5 rounded-full bg-[#F2AA00] text-[#4C2500] font-extrabold text-[11px]">+{{ $cat->default_points }}</span>
                            @if($cat->is_mandatory)
                                <span class="block text-[10px] font-bold text-[#CD7000] mt-0.5">WAJIB</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('admin.categories.store') }}" class="pt-3 border-t border-[#4C2500]/15 space-y-2.5">
                @csrf
                <p class="text-xs font-extrabold text-[#4C2500]">Tambah Kategori Kurikulum Baru</p>
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="code" required placeholder="SKPI-NEW-06" class="rounded-xl bg-[#FDF6DC] border border-[#4C2500]/20 px-3 py-1.5 text-xs font-mono">
                    <select name="competency_domain" class="rounded-xl bg-[#FDF6DC] border border-[#4C2500]/20 px-3 py-1.5 text-xs">
                        @foreach(['Leadership', 'Technical', 'Communication', 'Social', 'Academic'] as $dom)
                            <option value="{{ $dom }}">{{ $dom }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="text" name="name_id" required placeholder="Nama Kategori (Indonesia)" class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/20 px-3 py-1.5 text-xs">
                <input type="text" name="name_en" required placeholder="Category Name (English)" class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/20 px-3 py-1.5 text-xs">
                <div class="grid grid-cols-3 gap-2 items-center">
                    <input type="number" name="default_points" value="20" min="5" max="60" class="rounded-xl bg-[#FDF6DC] border border-[#4C2500]/20 px-3 py-1.5 text-xs">
                    <select name="level" class="rounded-xl bg-[#FDF6DC] border border-[#4C2500]/20 px-2 py-1.5 text-xs">
                        <option value="Lokal">Lokal</option>
                        <option value="Nasional">Nasional</option>
                        <option value="Internasional">Internasional</option>
                    </select>
                    <label class="flex items-center gap-1.5 text-xs font-bold text-[#4C2500]">
                        <input type="checkbox" name="is_mandatory" value="1"> Wajib
                    </label>
                </div>
                <button type="submit" class="w-full rounded-xl bg-[#CD7000] hover:bg-[#4C2500] text-[#FDF6DC] py-2 text-xs font-extrabold cursor-pointer transition">
                    + Simpan Kategori SKPI
                </button>
            </form>
        </section>
    </div>

    {{-- CREDENTIAL REVOCATION REGISTRY --}}
    <section class="honey-card rounded-3xl p-6 mb-7">
        <h2 class="text-lg font-extrabold text-[#4C2500]">Daftar Kredensial Terbit & Pencabutan (Revocation List)</h2>
        <p class="text-xs text-[#4C2500]/70 mb-4">Mencabut kredensial akan seketika memicu status <strong>DICABUT / TIDAK BERLAKU</strong> saat QR dipindai HRD.</p>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#4C2500]/15 text-[#CD7000] uppercase text-[10px]">
                        <th class="py-2 pr-3">No. Registrasi</th>
                        <th class="py-2 pr-3">Mahasiswa</th>
                        <th class="py-2 pr-3">Kredensial</th>
                        <th class="py-2 pr-3">Status</th>
                        <th class="py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#4C2500]/10">
                    @foreach($credentials as $cred)
                        <tr>
                            <td class="py-3 pr-3 font-mono font-bold">
                                <a href="{{ route('verify.show', $cred->registration_number) }}" target="_blank" class="underline text-[#4C2500] hover:text-[#CD7000]">
                                    {{ $cred->registration_number }}
                                </a>
                            </td>
                            <td class="py-3 pr-3 font-semibold">{{ $cred->student?->name }}</td>
                            <td class="py-3 pr-3">{{ $cred->title }}</td>
                            <td class="py-3 pr-3">
                                @if($cred->isRevoked())
                                    <span class="px-2.5 py-0.5 rounded-full bg-red-800 text-white text-[10px] font-extrabold">DICABUT</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-800 text-emerald-100 text-[10px] font-extrabold">AKTIF SAH</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if(!$cred->isRevoked())
                                    <form method="POST" action="{{ route('admin.credentials.revoke', $cred) }}" class="flex items-center gap-1.5">
                                        @csrf
                                        <input type="text" name="revoke_reason" required placeholder="Alasan pencabutan resmi..."
                                               class="rounded-lg bg-[#FDF6DC] border border-red-800/30 px-2.5 py-1 text-[11px] w-48">
                                        <button type="submit" class="rounded-lg bg-red-800 hover:bg-red-700 text-white px-2.5 py-1 text-[11px] font-bold cursor-pointer">
                                            Cabut
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[11px] text-red-800 italic">{{ $cred->revoke_reason }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- APPEND-ONLY AUDIT TRAIL --}}
    <section class="honey-card rounded-3xl p-6">
        <h2 class="text-base font-extrabold text-[#4C2500] mb-3">Append-Only Audit Trail (Anti-Tamper Log)</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#4C2500]/15 text-[#CD7000] uppercase text-[10px]">
                        <th class="py-2 pr-3">Timestamp</th>
                        <th class="py-2 pr-3">Aktor</th>
                        <th class="py-2 pr-3">Aksi</th>
                        <th class="py-2 pr-3">IP</th>
                        <th class="py-2">Detail Diff</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#4C2500]/10 font-mono text-[11px]">
                    @foreach($auditLogs as $log)
                        <tr>
                            <td class="py-2 pr-3">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td class="py-2 pr-3 font-sans font-bold">{{ $log->actor?->name ?? 'System' }}</td>
                            <td class="py-2 pr-3 text-[#CD7000] font-bold">{{ $log->action }}</td>
                            <td class="py-2 pr-3">{{ $log->ip_address ?? '-' }}</td>
                            <td class="py-2 text-[#4C2500]/75">{{ \Illuminate\Support\Str::limit(json_encode($log->changes_json), 95) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
