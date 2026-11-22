@extends('layouts.app')

@section('title', 'Verifikasi Kredensial QR Publik — WhoI.Buzz')

@section('content')
    <section class="max-w-4xl mx-auto">
        {{-- Search Box --}}
        <div class="espresso-card rounded-[28px] p-7 mb-7">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#F2AA00] text-[#4C2500] text-[11px] font-extrabold">
                <x-icon name="shield-check" class="w-3.5 h-3.5" />
                <span>Public Cryptographic Verification (Ed25519 + HMAC-SHA256)</span>
            </span>
            <h1 class="mt-2.5 text-2xl sm:text-3xl font-extrabold text-[#FDF6DC]">Portal Verifikasi QR & SKPI Publik</h1>
            <p class="text-xs text-[#F9E7A9]/80 mt-1">
                HRD, institusi pendidikan, atau mitra industri dapat memverifikasi keaslian sertifikat dan SKPI mahasiswa secara langsung tanpa perlu login.
            </p>

            <form method="GET" action="{{ route('verify.index') }}" class="mt-5 flex flex-col sm:flex-row gap-2.5">
                <input type="text" name="code" value="{{ $searchedCode }}" required
                       placeholder="Masukkan Nomor Registrasi (cth: WB-SKPI-2026-NADIA1)"
                       class="flex-1 rounded-2xl bg-[#FDF6DC] text-[#4C2500] px-4 py-3 text-sm font-mono font-bold border-2 border-[#F2AA00]">
                <button type="submit"
                        class="rounded-2xl bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-[#FDF6DC] px-6 py-3 text-sm font-extrabold cursor-pointer transition">
                    Cek Keaslian →
                </button>
            </form>
        </div>

        {{-- Verification Result Card --}}
        @if($notFound)
            <div class="rounded-3xl border-2 border-red-800 bg-red-950 text-[#FDF6DC] p-7 mb-7">
                <h2 class="inline-flex items-center gap-2 text-xl font-extrabold text-red-200">
                    <x-icon name="x-mark" class="w-5 h-5" />
                    <span>KREDENSIAL TIDAK DITEMUKAN</span>
                </h2>
                <p class="mt-2 text-xs text-red-100">
                    Nomor registrasi <code class="font-mono font-bold">{{ $searchedCode }}</code> tidak tercatat pada pangkalan data resmi WhoI.Buzz. Harap pastikan nomor sudah benar atau waspadai potensi pemalsuan dokumen.
                </p>
            </div>
        @elseif($credential && $verification)
            @if($verification['is_revoked'])
                <div class="rounded-3xl border-2 border-red-700 bg-red-950 text-[#FDF6DC] p-7 mb-7 shadow-xl">
                    <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-red-800">
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-600 text-white text-xs font-extrabold uppercase tracking-wider">
                                <x-icon name="exclamation" class="w-4 h-4" />
                                <span>DICABUT / TIDAK BERLAKU (REVOKED)</span>
                            </span>
                            <h2 class="mt-2 text-2xl font-extrabold text-white">{{ $credential->title }}</h2>
                            <p class="text-xs font-mono text-red-200 mt-1">Reg: {{ $credential->registration_number }}</p>
                        </div>
                        <div class="bg-white p-2 rounded-2xl w-28 h-28 shrink-0">
                            {!! $qrSvg !!}
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl bg-red-900/60 border border-red-700 p-4 text-xs space-y-1.5">
                        <p><strong>Waktu Pencabutan Resmi:</strong> {{ $credential->revoked_at?->format('d M Y H:i:s') }} WIB</p>
                        <p><strong>Alasan Pencabutan:</strong> {{ $credential->revoke_reason }}</p>
                        <p class="text-red-200">Kredensial ini telah dibatalkan oleh otoritas kampus dan tidak lagi sah digunakan untuk keperluan rekrutmen maupun akademik.</p>
                    </div>
                </div>
            @elseif($verification['is_authentic'])
                <div class="honey-card-strong rounded-3xl p-7 border-2 border-[#F2AA00] mb-7 shadow-xl">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-5 pb-5 border-b border-[#4C2500]/15">
                        <div class="space-y-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-800 text-emerald-100 text-xs font-extrabold">
                                <x-icon name="check" class="w-4 h-4" />
                                <span>TERVERIFIKASI SAH SECARA KRIPTOGRAFIS</span>
                            </span>
                            <h2 class="text-2xl font-extrabold text-[#4C2500]">{{ $credential->title }}</h2>
                            <p class="font-mono text-xs font-bold text-[#CD7000]">{{ $credential->registration_number }}</p>
                            @if($credential->activity)
                                <div>
                                    <x-trust-badge :tier="$credential->activity->trust_tier" />
                                    <p class="text-[11px] text-[#4C2500]/75 mt-1">{{ $credential->activity->trustTierMeta()['description'] }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-col items-center shrink-0">
                            <div class="bg-[#FDF6DC] border-2 border-[#4C2500] rounded-2xl p-2.5 w-32 h-32 shadow">
                                {!! $qrSvg !!}
                            </div>
                            <span class="mt-1.5 text-[10px] font-mono font-bold text-[#4C2500]">Ed25519 SEAL</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-5 text-xs">
                        <div class="rounded-2xl bg-[#FDF6DC] border border-[#4C2500]/15 p-4 space-y-1.5">
                            <p class="text-[10px] font-extrabold uppercase tracking-wider text-[#CD7000]">Pemilik Kredensial</p>
                            <p class="text-sm font-extrabold text-[#4C2500]">{{ $credential->student?->name }}</p>
                            <p>NIM: <span class="font-mono font-bold">{{ $credential->student?->studentProfile?->nim }}</span></p>
                            <p>{{ $credential->student?->studentProfile?->prodi_name }} — {{ $credential->student?->studentProfile?->faculty_name }}</p>
                            @if($credential->student?->studentProfile?->is_public_enabled)
                                <a href="{{ route('portfolio.show', $credential->student->studentProfile->public_slug) }}"
                                   class="inline-block mt-1 font-bold text-[#CD7000] underline">
                                    Lihat Public E-Portfolio ({{ '@' . $credential->student->studentProfile->public_slug }}) →
                                </a>
                            @endif
                        </div>

                        <div class="rounded-2xl bg-[#4C2500] text-[#F9E7A9] p-4 space-y-1.5 font-mono text-[11px]">
                            <p class="text-[10px] font-sans font-extrabold uppercase tracking-wider text-[#F2AA00]">Bukti Kriptografi</p>
                            <p>Algo: {{ $credential->signature_algo }}</p>
                            <p>Digest: {{ $verification['fingerprint'] }}</p>
                            <p>Issued: {{ $credential->issued_at?->format('Y-m-d H:i') }} WIB</p>
                            <p class="text-emerald-300 font-bold">Signature Match: VALID</p>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center gap-2.5">
                        @if($credential->type === 'skpi')
                            <a href="{{ route('credentials.document', $credential->registration_number) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F2AA00] hover:text-[#FDF6DC] px-4 py-2.5 text-xs font-extrabold transition">
                                <x-icon name="document-text" class="w-4 h-4" />
                                <span>Buka Dokumen SKPI Dwibahasa Resmi</span>
                            </a>
                        @endif
                        @if($credential->activity?->external_verify_url)
                            <a href="{{ $credential->activity->external_verify_url }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 text-[#4C2500] px-4 py-2.5 text-xs font-bold">
                                <x-icon name="link" class="w-4 h-4" />
                                <span>Tautan Verifikasi Lembaga Asal</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        {{-- Sample Credentials Directory for Quick Demo --}}
        <div class="honey-card rounded-3xl p-6">
            <h2 class="text-sm font-extrabold text-[#4C2500] mb-3">Kredensial Terbaru yang Terbit</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                @foreach($recentCredentials as $rc)
                    <a href="{{ route('verify.show', $rc->registration_number) }}"
                       class="rounded-2xl bg-[#FDF6DC] hover:bg-[#F9E7A9] border border-[#4C2500]/15 px-4 py-3 flex items-center justify-between gap-2 transition">
                        <div class="min-w-0">
                            <p class="font-mono text-xs font-extrabold text-[#4C2500]">{{ $rc->registration_number }}</p>
                            <p class="text-[11px] text-[#4C2500]/75 truncate">{{ $rc->student?->name }} • {{ $rc->title }}</p>
                        </div>
                        <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $rc->isRevoked() ? 'bg-red-800 text-white' : 'bg-emerald-800 text-emerald-100' }}">
                            {{ $rc->isRevoked() ? 'REVOKED' : 'VALID' }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endsection
