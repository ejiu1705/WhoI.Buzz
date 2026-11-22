@extends('layouts.app')

@section('title', 'Split-Screen Reviewer Dosen PA — WhoI.Buzz')

@section('content')
    <section class="espresso-card rounded-[28px] p-6 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#F2AA00] text-[#4C2500] text-[11px] font-extrabold">
                <x-icon name="shield-check" class="w-3.5 h-3.5" />
                <span>Passkey Verified • Split-Screen &amp; Bulk Approval</span>
            </span>
            <h1 class="mt-2 text-2xl font-extrabold text-[#FDF6DC]">Panel Validasi Dosen PA / Verifikator</h1>
            <p class="text-xs text-[#F9E7A9]/80 mt-0.5">
                Verifikasi bukti sertifikat langsung dari Private Vault via Temporary Signed URL (berlaku 5 menit) tanpa mengunduh file ke komputer lokal.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('laporan.progres') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#F2AA00] text-[#4C2500] px-4 py-2.5 text-xs font-extrabold">
                <x-icon name="chart-bar" class="w-4 h-4" />
                <span>Laporan Progres</span>
            </a>
            <a href="{{ route('kurikulum-skpi.index') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#FDF6DC]/15 text-[#FDF6DC] px-4 py-2.5 text-xs font-bold">
                <x-icon name="book-open" class="w-4 h-4" />
                <span>Kurikulum SKPI</span>
            </a>
            <div class="rounded-2xl bg-[#FDF6DC]/10 border border-[#F2AA00]/35 px-4 py-2 text-center">
                <p class="text-xl font-extrabold text-[#F2AA00]">{{ $pendingQueue->count() }}</p>
                <p class="text-[10px] uppercase tracking-wider text-[#F9E7A9]">Antrean</p>
            </div>
        </div>
    </section>

    {{-- BULK APPROVAL QUEUE STRIP --}}
    @if($pendingQueue->isNotEmpty())
        <section class="honey-card rounded-3xl p-5 mb-6">
            <form method="POST" action="{{ route('dosen.bulk-approve') }}">
                @csrf
                <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                    <div>
                        <h2 class="text-sm font-extrabold text-[#4C2500]">Antrean Validasi &amp; Bulk Approval (3× Lebih Cepat)</h2>
                        <p class="text-[11px] text-[#4C2500]/70">Klik item untuk membuka Split-Screen Reviewer di bawah, atau centang beberapa item berkeyakinan AI tinggi untuk persetujuan massal.</p>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F9E7A9] hover:text-[#FDF6DC] px-4 py-2 text-xs font-extrabold cursor-pointer transition shadow">
                        <x-icon name="bolt" class="w-4 h-4" />
                        <span>Setujui Massal (Bulk Approve)</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @foreach($pendingQueue as $item)
                        @php $isSelected = $selectedActivity && $selectedActivity->id === $item->id; @endphp
                        <div class="rounded-2xl p-3.5 border transition {{ $isSelected ? 'bg-[#F9E7A9] border-[#CD7000] ring-2 ring-[#F2AA00]' : 'bg-[#FDF6DC] border-[#4C2500]/15' }}">
                            <div class="flex items-start gap-2.5">
                                <input type="checkbox" name="activity_ids[]" value="{{ $item->id }}"
                                       class="mt-1 rounded border-[#4C2500]/40 text-[#CD7000]">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-[#4C2500] text-[#F9E7A9]">
                                            AI {{ $item->ai_confidence_score ?? 85 }}%
                                        </span>
                                        <span class="text-[10px] font-bold text-[#CD7000]">+{{ $item->awarded_points }} pts</span>
                                    </div>
                                    <a href="{{ route('dosen.dashboard', ['selected' => $item->id]) }}"
                                       class="mt-1.5 block text-xs font-extrabold text-[#4C2500] hover:text-[#CD7000] line-clamp-2">
                                        {{ $item->title_id }}
                                    </a>
                                    <p class="text-[11px] text-[#4C2500]/70 mt-0.5">{{ $item->student->name }} ({{ $item->student->studentProfile?->nim }})</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </form>
        </section>

        {{-- SPLIT-SCREEN REVIEWER PANEL --}}
        @if($selectedActivity)
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
                {{-- LEFT PANE: PRIVATE VAULT TEMPORARY SIGNED PREVIEW --}}
                <div class="lg:col-span-7 honey-card rounded-3xl p-5 flex flex-col">
                    <div class="flex items-center justify-between gap-2 pb-3 border-b border-[#4C2500]/15">
                        <div>
                            <p class="text-xs font-extrabold text-[#4C2500]">Pratinjau Dokumen Private Vault</p>
                            <p class="text-[11px] text-[#4C2500]/70 font-mono">
                                SHA-256: {{ substr($selectedActivity->file?->sha256_hash ?? '-', 0, 20) }}… • Signed URL (5 menit)
                            </p>
                        </div>
                        @if($signedPreviewUrl)
                            <a href="{{ $signedPreviewUrl }}" target="_blank"
                               class="rounded-lg bg-[#4C2500] text-[#F9E7A9] px-3 py-1.5 text-[11px] font-bold">
                                Buka Tab Baru ↗
                            </a>
                        @endif
                    </div>

                    <div class="mt-4 flex-1 rounded-2xl bg-[#331800] p-3 flex items-center justify-center min-h-[380px] overflow-hidden">
                        @if($signedPreviewUrl && str_starts_with((string) $selectedActivity->file?->mime_type, 'image/'))
                            <img src="{{ $signedPreviewUrl }}" alt="Pratinjau Sertifikat" class="max-h-[460px] w-auto rounded-xl shadow-lg border border-[#F2AA00]/40">
                        @elseif($signedPreviewUrl)
                            <iframe src="{{ $signedPreviewUrl }}" class="w-full h-[460px] rounded-xl bg-white" title="Pratinjau PDF Sertifikat"></iframe>
                        @else
                            <p class="text-xs text-[#F9E7A9]/75">Tidak ada lampiran dokumen.</p>
                        @endif
                    </div>
                </div>

                {{-- RIGHT PANE: STRUCTURED DATA + AI CONFIDENCE + DECISION FORM --}}
                <div class="lg:col-span-5 honey-card-strong rounded-3xl p-6">
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-[#4C2500]/15">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#4C2500] text-[#F2AA00] text-xs font-extrabold">
                            <x-icon name="sparkles" class="w-3.5 h-3.5" />
                            <span>AI Confidence Score: {{ $selectedActivity->ai_confidence_score ?? 85 }}%</span>
                        </span>
                        <x-trust-badge :tier="$selectedActivity->trust_tier" />
                    </div>

                    <div class="mt-4 space-y-2 text-xs">
                        <p><strong class="text-[#CD7000]">Mahasiswa:</strong> {{ $selectedActivity->student->name }} (NIM {{ $selectedActivity->student->studentProfile?->nim }})</p>
                        <p><strong class="text-[#CD7000]">Judul (ID):</strong> {{ $selectedActivity->title_id }}</p>
                        <p><strong class="text-[#CD7000]">Title (EN):</strong> <em>{{ $selectedActivity->title_en }}</em></p>
                        <p><strong class="text-[#CD7000]">Penyelenggara:</strong> {{ $selectedActivity->organizer }}</p>
                        <p><strong class="text-[#CD7000]">Peran & Tanggal:</strong> {{ $selectedActivity->role_in_activity }} • {{ $selectedActivity->activity_date?->format('d M Y') }}</p>
                        <p><strong class="text-[#CD7000]">Kategori SKPI:</strong> {{ $selectedActivity->category?->name_id }} ({{ $selectedActivity->category?->competency_domain }})</p>

                        @if($selectedActivity->external_verify_url)
                            <div class="rounded-xl bg-[#FDF6DC] border border-[#CD7000]/40 p-3 mt-2">
                                <p class="text-[11px] font-bold text-[#4C2500]">Tautan Verifikasi Eksternal (Tier 2):</p>
                                <a href="{{ $selectedActivity->external_verify_url }}" target="_blank" rel="noopener"
                                   class="text-[11px] font-mono text-[#CD7000] underline break-all">
                                    {{ $selectedActivity->external_verify_url }} ↗
                                </a>
                            </div>
                        @else
                            <div class="rounded-xl bg-[#4C2500]/8 p-2.5 text-[11px] text-[#4C2500]/80">
                                Tidak ada tautan verifikasi eksternal → direkomendasikan sebagai <strong>Tier 3 (Administratively Reviewed)</strong>.
                            </div>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('dosen.decide', $selectedActivity) }}" class="mt-5 pt-4 border-t border-[#4C2500]/15 space-y-3.5">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-[#4C2500] mb-1">Tetapkan Trust Tier</label>
                                <select name="trust_tier" class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                                    <option value="2" @selected($selectedActivity->trust_tier === 2)>Tier 2 — Verified External</option>
                                    <option value="3" @selected($selectedActivity->trust_tier === 3)>Tier 3 — Admin Reviewed</option>
                                    <option value="1" @selected($selectedActivity->trust_tier === 1)>Tier 1 — Campus Issued</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-[#4C2500] mb-1">Poin SKPI Diberikan</label>
                                <input type="number" name="awarded_points" value="{{ $selectedActivity->awarded_points }}" min="5" max="100"
                                       class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs font-bold text-[#4C2500]">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-[#4C2500] mb-1">Catatan Validasi / Alasan Revisi (wajib jika Revisi)</label>
                            <textarea name="notes" rows="2" placeholder="Contoh: Tautan verifikasi resmi sesuai, disetujui dan dikunci..."
                                      class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 pt-1">
                            <button type="submit" name="decision" value="revision"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-red-800 hover:bg-red-700 text-white py-2.5 text-xs font-extrabold cursor-pointer transition">
                                <x-icon name="refresh" class="w-4 h-4" />
                                <span>Minta Revisi</span>
                            </button>
                            <button type="submit" name="decision" value="approved"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F2AA00] hover:text-[#FDF6DC] py-2.5 text-xs font-extrabold cursor-pointer transition shadow">
                                <x-icon name="check" class="w-4 h-4" />
                                <span>Setujui &amp; Kunci</span>
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        @endif
    @else
        <section class="honey-card rounded-3xl p-10 text-center mb-8">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[#4C2500] text-[#F2AA00] mx-auto">
                <x-icon name="check" class="w-6 h-6" />
            </div>
            <h2 class="mt-3 text-lg font-extrabold text-[#4C2500]">Semua Antrean Validasi Selesai</h2>
            <p class="text-xs text-[#4C2500]/70 mt-1">Tidak ada sertifikat mahasiswa yang menunggu tinjauan saat ini.</p>
        </section>
    @endif

    {{-- RECENT VALIDATION HISTORY --}}
    <section class="honey-card rounded-3xl p-6">
        <h2 class="text-base font-extrabold text-[#4C2500] mb-3">Riwayat Keputusan Validasi Terbaru</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#4C2500]/15 text-[#CD7000] uppercase text-[10px]">
                        <th class="py-2 pr-3">Waktu</th>
                        <th class="py-2 pr-3">Mahasiswa</th>
                        <th class="py-2 pr-3">Kegiatan</th>
                        <th class="py-2 pr-3">Keputusan</th>
                        <th class="py-2">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#4C2500]/10">
                    @foreach($recentDecisions as $dec)
                        <tr>
                            <td class="py-2.5 pr-3 font-mono text-[11px]">{{ $dec->reviewed_at?->format('d M H:i') }}</td>
                            <td class="py-2.5 pr-3 font-bold">{{ $dec->activity?->student?->name }}</td>
                            <td class="py-2.5 pr-3">{{ $dec->activity?->title_id }}</td>
                            <td class="py-2.5 pr-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $dec->decision === 'approved' ? 'bg-emerald-800 text-emerald-100' : 'bg-red-800 text-white' }}">
                                    {{ $dec->decision }}
                                </span>
                            </td>
                            <td class="py-2.5 text-[#4C2500]/75">{{ $dec->notes }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
