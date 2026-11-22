@extends('layouts.app')

@section('title', 'WhoI.Buzz — Campus Verified Credential & E-Portfolio Hub')

@section('content')
    {{-- HERO: PROFESSIONAL PUBLIC DASHBOARD ABOUT WHOI.BUZZ --}}
    <section class="relative overflow-hidden rounded-[32px] espresso-card p-8 sm:p-12 lg:p-14 mb-10">
        <div class="pointer-events-none absolute -top-28 -right-28 h-80 w-80 rounded-full bg-[#F2AA00]/18 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 -left-24 h-72 w-72 rounded-full bg-[#CD7000]/20 blur-3xl"></div>

        <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            {{-- Left Column: Clear Brand Positioning & Login / Register CTAs --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 rounded-full bg-[#F9E7A9]/12 border border-[#F2AA00]/40 px-4 py-1.5 text-xs font-semibold text-[#F9E7A9]">
                    <span class="h-2 w-2 rounded-full bg-[#F2AA00]"></span>
                    <span>Campus Verified Credential &amp; E-Portfolio Hub</span>
                </div>

                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-[#FDF6DC] leading-[1.12]">
                    Pusat Kebenaran Tunggal
                    <span class="text-[#F2AA00]">Kredensial &amp; SKPI</span>
                    Mahasiswa Terverifikasi.
                </h1>

                <p class="text-sm sm:text-base text-[#F9E7A9]/85 leading-relaxed max-w-2xl">
                    <strong class="text-[#FDF6DC]">WhoI.Buzz</strong> menghubungkan pencatatan prestasi akademik,
                    validasi Dosen Pembimbing Akademik, penerbitan SKPI dwibahasa bertanda tangan kriptografi
                    <span class="text-[#F2AA00] font-semibold">Ed25519</span>, serta portofolio karier terverifikasi
                    dalam satu ekosistem kampus yang aman dan selaras dengan <strong class="text-[#FDF6DC]">UU PDP</strong>.
                </p>

                {{-- Primary Action Buttons: Login & Register (or Go to Dashboard if authenticated) --}}
                <div class="flex flex-wrap items-center gap-3 pt-1">
                    @auth
                        <a href="{{ auth()->user()->dashboardRoute() }}"
                           class="inline-flex items-center gap-2 rounded-2xl bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-[#FDF6DC] font-extrabold text-sm px-7 py-3.5 transition shadow-lg">
                            <span>Buka Dashboard {{ auth()->user()->roleLabel() }}</span>
                            <span>→</span>
                        </a>
                        <a href="{{ route('account.show') }}"
                           class="inline-flex items-center gap-2 rounded-2xl bg-[#FDF6DC]/12 hover:bg-[#FDF6DC]/20 text-[#FDF6DC] border border-[#F9E7A9]/30 font-bold text-sm px-6 py-3.5 transition">
                            Info Akun Saya
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-2 rounded-2xl bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-[#FDF6DC] font-extrabold text-sm px-7 py-3.5 transition shadow-lg">
                            <span>Masuk ke Akun</span>
                            <span>→</span>
                        </a>
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center gap-2 rounded-2xl bg-[#FDF6DC] hover:bg-[#F9E7A9] text-[#4C2500] font-extrabold text-sm px-7 py-3.5 transition shadow">
                            <span>Registrasi Akun Baru</span>
                        </a>
                        <a href="{{ route('verify.index') }}"
                           class="inline-flex items-center gap-2 rounded-2xl bg-[#FDF6DC]/10 hover:bg-[#FDF6DC]/20 text-[#F9E7A9] border border-[#F9E7A9]/25 font-semibold text-sm px-5 py-3.5 transition">
                            Verifikasi QR Publik
                        </a>
                    @endauth
                </div>
            </div>

            {{-- Right Column: Brand Identity Card & Live Hub Metrics --}}
            <div class="lg:col-span-5">
                <div class="rounded-[26px] bg-gradient-to-br from-[#FDF6DC] via-[#F9E7A9] to-[#FDF6DC] p-6 sm:p-7 text-[#4C2500] border-2 border-[#F2AA00] shadow-2xl">
                    <div class="flex items-center justify-between gap-4 pb-5 border-b border-[#4C2500]/15">
                        <img src="{{ asset('images/whoibuzz-logo.png') }}" alt="WHOI.BUZZ" class="h-14 sm:h-16 w-auto object-contain">
                        <div class="text-right">
                            <span class="inline-block px-3 py-1 rounded-full bg-[#4C2500] text-[#F9E7A9] text-[10px] font-extrabold uppercase tracking-wider">
                                Sistem Aktif
                            </span>
                            <p class="text-[11px] font-semibold text-[#CD7000] mt-1">Kurikulum SKPI 2026-V1</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-5">
                        <div class="rounded-2xl bg-[#FDF6DC]/90 p-4 border border-[#4C2500]/10">
                            <p class="text-2xl font-extrabold text-[#4C2500]">{{ $stats['verified_credentials'] }}</p>
                            <p class="text-xs font-medium text-[#4C2500]/75 mt-0.5">Kredensial Terbit</p>
                        </div>
                        <div class="rounded-2xl bg-[#F2AA00]/22 p-4 border border-[#CD7000]/25">
                            <p class="text-2xl font-extrabold text-[#4C2500]">{{ $stats['approved_activities'] }}</p>
                            <p class="text-xs font-medium text-[#4C2500]/75 mt-0.5">Capaian Tervalidasi</p>
                        </div>
                        <div class="rounded-2xl bg-[#F2AA00]/22 p-4 border border-[#CD7000]/25">
                            <p class="text-2xl font-extrabold text-[#4C2500]">{{ $stats['active_students'] }}</p>
                            <p class="text-xs font-medium text-[#4C2500]/75 mt-0.5">Mahasiswa Terdaftar</p>
                        </div>
                        <div class="rounded-2xl bg-[#FDF6DC]/90 p-4 border border-[#4C2500]/10">
                            <p class="text-2xl font-extrabold text-[#4C2500]">{{ $stats['public_portfolios'] }}</p>
                            <p class="text-xs font-medium text-[#4C2500]/75 mt-0.5">Portofolio Publik</p>
                        </div>
                    </div>

                    {{-- Quick Verification Lookup --}}
                    <form method="GET" action="{{ route('verify.index') }}" class="mt-5 pt-4 border-t border-[#4C2500]/15">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#4C2500]/75 mb-2">
                            Verifikasi Cepat Nomor Kredensial / SKPI
                        </label>
                        <div class="flex gap-2">
                            <input type="text" name="code" required
                                   placeholder="cth: WB-SKPI-2026-NADIA1"
                                   class="flex-1 rounded-xl bg-white border border-[#4C2500]/20 px-3.5 py-2 text-xs font-mono text-[#4C2500] focus:outline-none focus:border-[#CD7000]">
                            <button type="submit"
                                    class="rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F2AA00] hover:text-[#FDF6DC] px-4 py-2 text-xs font-extrabold transition cursor-pointer">
                                Cek
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- EKOSISTEM PERAN & WORKSPACE KHUSUS --}}
    <section class="mb-12">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-widest text-[#CD7000]">Ekosistem Berbasis Peran (RBAC)</p>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-[#4C2500] mt-1">Satu Platform, Empat Ruang Kerja Terdedikasi</h2>
            </div>
            @guest
                <a href="{{ route('login') }}" class="text-xs font-bold text-[#CD7000] hover:text-[#4C2500] underline">
                    Masuk sesuai peran Anda →
                </a>
            @endguest
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach([
                [
                    'tag' => 'Mahasiswa',
                    'title' => 'Portofolio & Pengajuan SKPI',
                    'desc' => 'Unggah sertifikat dengan AI Smart Parser, pantau Graduation Tracker & Skill Radar, buat ATS CV instan, dan ekspor 1-Click ke LinkedIn.',
                ],
                [
                    'tag' => 'Dosen PA / Verifikator',
                    'title' => 'Validasi Split-Screen',
                    'desc' => 'Tinjau dokumen langsung melalui Temporary Signed URL aman, periksa skor keyakinan AI, dan lakukan Bulk Approval tanpa hambatan.',
                ],
                [
                    'tag' => 'Admin Kemahasiswaan',
                    'title' => 'Tata Kelola & Auto-Claim',
                    'desc' => 'Terbitkan sertifikat internal Tier 1 secara massal, kelola akun & kurikulum SKPI, pantau Audit Log, serta daftar pencabutan (Revocation).',
                ],
                [
                    'tag' => 'Mitra Karier / HRD',
                    'title' => 'Talent Matching & Verifikasi',
                    'desc' => 'Temukan kandidat mahasiswa secara semantik berbasis vektor kompetensi dan verifikasi keaslian QR/SKPI secara real-time.',
                ],
            ] as $item)
                <div class="honey-card rounded-3xl p-6 flex flex-col justify-between">
                    <div>
                        <span class="inline-block px-3 py-1 rounded-full bg-[#F9E7A9] border border-[#CD7000]/30 text-[11px] font-extrabold text-[#4C2500]">
                            {{ $item['tag'] }}
                        </span>
                        <h3 class="mt-3.5 text-base font-extrabold text-[#4C2500]">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-xs text-[#4C2500]/75 leading-relaxed">{{ $item['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 3-TIER VERIFICATION TRUST MODEL --}}
    <section class="mb-12">
        <div class="mb-6">
            <p class="text-xs font-extrabold uppercase tracking-widest text-[#CD7000]">Standar Kepercayaan Akademik</p>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#4C2500] mt-1">3-Tier Verification Trust Model</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="honey-card-strong rounded-3xl p-6 border-2 border-[#F2AA00]">
                <x-trust-badge :tier="1" />
                <h3 class="mt-3.5 text-lg font-extrabold text-[#4C2500]">Campus Issued (Gold Badge)</h3>
                <p class="mt-2 text-xs text-[#4C2500]/80 leading-relaxed">
                    Diterbitkan langsung oleh sistem kampus melalui mekanisme <strong>Auto-Claim</strong> untuk kegiatan resmi internal. Validitas 100% dijamin kriptografi kampus.
                </p>
            </div>

            <div class="honey-card rounded-3xl p-6 border border-[#CD7000]/40">
                <x-trust-badge :tier="2" />
                <h3 class="mt-3.5 text-lg font-extrabold text-[#4C2500]">Digitally Verified External</h3>
                <p class="mt-2 text-xs text-[#4C2500]/80 leading-relaxed">
                    Sertifikat lembaga bereputasi eksternal (AWS, Google, BNSP, Dicoding, Coursera, Credly) dengan tautan verifikasi daring resmi yang telah terkonfirmasi.
                </p>
            </div>

            <div class="honey-card rounded-3xl p-6 border border-[#4C2500]/20">
                <x-trust-badge :tier="3" />
                <h3 class="mt-3.5 text-lg font-extrabold text-[#4C2500]">Administratively Reviewed</h3>
                <p class="mt-2 text-xs text-[#4C2500]/80 leading-relaxed">
                    Sertifikat eksternal tanpa sistem verifikasi daring yang telah diperiksa secara visual oleh Dosen PA dengan pelabelan transparan di halaman publik.
                </p>
            </div>
        </div>
    </section>

    {{-- KEAMANAN ZERO-TRUST & PORTOFOLIO PUBLIK TERVERIFIKASI --}}
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-10">
        {{-- Left: Zero-Trust Security Pillars --}}
        <div class="lg:col-span-5 espresso-card rounded-3xl p-7 flex flex-col justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-widest text-[#F2AA00]">Keamanan Zero-Trust &amp; UU PDP</p>
                <h2 class="mt-1.5 text-xl font-extrabold text-[#FDF6DC]">Dirancang untuk Integritas Data Kampus</h2>
                <ul class="mt-5 space-y-3 text-xs text-[#F9E7A9]/85">
                    <li class="flex items-start gap-2.5">
                        <x-icon name="shield-check" class="w-4 h-4 text-[#F2AA00] shrink-0 mt-0.5" />
                        <span><strong class="text-[#FDF6DC]">Zero-Trust File Vault:</strong> validasi Magic Bytes MIME, anti-duplikasi <code class="font-mono">SHA-256</code>, sanitasi gambar, dan Signed URL 5 menit.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <x-icon name="qrcode" class="w-4 h-4 text-[#F2AA00] shrink-0 mt-0.5" />
                        <span><strong class="text-[#FDF6DC]">Tanda Tangan Ed25519 + Revocation:</strong> QR anti-pemalsuan dengan pengecekan pencabutan secara real-time.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <x-icon name="lock" class="w-4 h-4 text-[#F2AA00] shrink-0 mt-0.5" />
                        <span><strong class="text-[#FDF6DC]">Privacy-by-Default (UU PDP):</strong> profil publik nonaktif secara bawaan, enkripsi kolom sensitif, serta kontrol visibilitas per item.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <x-icon name="document-check" class="w-4 h-4 text-[#F2AA00] shrink-0 mt-0.5" />
                        <span><strong class="text-[#FDF6DC]">Immutable &amp; Audit Trail:</strong> capaian tervalidasi terkunci permanen dengan log audit <em>append-only</em>.</span>
                    </li>
                </ul>
            </div>

            @guest
                <div class="mt-6 pt-5 border-t border-[#F9E7A9]/15 flex flex-wrap gap-2.5">
                    <a href="{{ route('login') }}" class="rounded-xl bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-[#FDF6DC] px-5 py-2.5 text-xs font-extrabold transition">
                        Masuk Sekarang
                    </a>
                    <a href="{{ route('register') }}" class="rounded-xl bg-[#FDF6DC]/15 hover:bg-[#FDF6DC]/25 text-[#FDF6DC] px-5 py-2.5 text-xs font-bold transition">
                        Daftar Akun
                    </a>
                </div>
            @endguest
        </div>

        {{-- Right: Featured Opt-In Portfolios --}}
        <div class="lg:col-span-7 honey-card rounded-3xl p-7">
            <div class="flex items-center justify-between gap-2 mb-5">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-widest text-[#CD7000]">Portofolio Publik Opt-In</p>
                    <h2 class="text-xl font-extrabold text-[#4C2500] mt-0.5">Talenta Kampus Terverifikasi</h2>
                </div>
                <a href="{{ route('talent.index') }}" class="text-xs font-bold text-[#CD7000] hover:text-[#4C2500] underline">
                    Jelajahi Talent Pool →
                </a>
            </div>

            <div class="space-y-3.5">
                @foreach($featuredPortfolios as $fp)
                    <a href="{{ route('portfolio.show', $fp->public_slug) }}"
                       class="group block rounded-2xl bg-[#FDF6DC] hover:bg-[#F9E7A9]/70 border border-[#4C2500]/12 hover:border-[#CD7000]/45 p-4 transition">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-extrabold text-[#4C2500] group-hover:text-[#CD7000] transition">{{ $fp->user->name }}</h3>
                                    <span class="font-mono text-[11px] font-bold text-[#CD7000]">{{ '@' . $fp->public_slug }}</span>
                                </div>
                                <p class="text-xs text-[#4C2500]/70 mt-0.5">{{ $fp->prodi_name }} • Semester {{ $fp->semester }} — {{ $fp->headline }}</p>
                            </div>
                            <span class="shrink-0 self-start sm:self-center px-3 py-1 rounded-full bg-[#4C2500] text-[#F2AA00] text-[11px] font-extrabold">
                                {{ $fp->user->activities->sum('awarded_points') }} Poin SKPI
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endsection
