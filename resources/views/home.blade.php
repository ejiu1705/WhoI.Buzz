@extends('layouts.app')

@section('title', 'WhoI.Buzz — Portal Kredensial & E-Portfolio Kampus')

@section('content')
    {{-- HERO BERANDA WHOI.BUZZ --}}
    <section class="relative overflow-hidden rounded-[32px] espresso-card p-8 sm:p-12 lg:p-14 mb-10">
        <div class="ambient-orb pointer-events-none absolute -top-28 -right-28 h-80 w-80 rounded-full bg-[#F2AA00]/18 blur-3xl"></div>
        <div class="ambient-orb-reverse pointer-events-none absolute -bottom-28 -left-24 h-72 w-72 rounded-full bg-[#CD7000]/20 blur-3xl"></div>

        <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 rounded-full bg-[#F9E7A9]/15 border border-[#F2AA00]/45 px-4 py-1.5 text-xs font-semibold text-[#FDF6DC]">
                    <span class="h-2 w-2 rounded-full bg-[#F2AA00]"></span>
                    <span>Portal Resmi Kredensial, Portofolio &amp; SKPI Kampus</span>
                </div>

                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-[#FDF6DC] leading-[1.14]">
                    Rekam Jejak Prestasi &amp;
                    <span class="text-[#F2AA00]">SKPI Mahasiswa</span>
                    Tervalidasi Kampus.
                </h1>

                <p class="text-sm sm:text-base text-[#F9E7A9]/90 leading-relaxed max-w-2xl">
                    <strong class="text-[#FDF6DC]">WhoI.Buzz</strong> memudahkan mahasiswa mendokumentasikan sertifikat dan kegiatan,
                    mempercepat validasi oleh Dosen Pembimbing Akademik, serta menerbitkan SKPI dwibahasa ber-QR digital
                    yang dapat langsung diperiksa keasliannya oleh perusahaan maupun institusi lain.
                </p>

                <div class="flex flex-wrap items-center gap-3 pt-1">
                    @auth
                        <a href="{{ auth()->user()->dashboardRoute() }}"
                           class="btn-shimmer inline-flex items-center gap-2 rounded-2xl bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-[#FDF6DC] font-extrabold text-sm px-7 py-3.5 transition shadow-lg">
                            <span>Buka Dashboard {{ auth()->user()->roleLabel() }}</span>
                            <span>→</span>
                        </a>
                        <a href="{{ route('account.show') }}"
                           class="inline-flex items-center gap-2 rounded-2xl bg-[#FDF6DC]/15 hover:bg-[#FDF6DC]/25 text-[#FDF6DC] border border-[#F9E7A9]/35 font-bold text-sm px-6 py-3.5 transition">
                            Info Akun Saya
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="btn-shimmer inline-flex items-center gap-2 rounded-2xl bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-[#FDF6DC] font-extrabold text-sm px-7 py-3.5 transition shadow-lg">
                            <span>Masuk ke Akun</span>
                            <span>→</span>
                        </a>
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center gap-2 rounded-2xl bg-[#FDF6DC] hover:bg-[#F9E7A9] text-[#4C2500] font-extrabold text-sm px-7 py-3.5 transition shadow">
                            <span>Registrasi Akun Baru</span>
                        </a>
                        <a href="{{ route('verify.index') }}"
                           class="inline-flex items-center gap-2 rounded-2xl bg-[#FDF6DC]/12 hover:bg-[#FDF6DC]/22 text-[#FDF6DC] border border-[#F9E7A9]/30 font-semibold text-sm px-5 py-3.5 transition">
                            Verifikasi QR Publik
                        </a>
                    @endauth
                </div>
            </div>

            {{-- Ringkasan Data & Verifikasi Cepat --}}
            <div class="lg:col-span-5">
                <div class="rounded-[26px] bg-gradient-to-br from-[#FDF6DC] via-[#F9E7A9] to-[#FDF6DC] p-6 sm:p-7 text-[#4C2500] border-2 border-[#F2AA00] shadow-2xl">
                    <div class="flex items-center justify-between gap-4 pb-5 border-b border-[#4C2500]/15">
                        <img src="{{ asset('images/whoibuzz-logo.png') }}" alt="WHOI.BUZZ" class="h-14 sm:h-16 w-auto object-contain">
                        <div class="text-right">
                            <span class="inline-block px-3 py-1 rounded-full bg-[#4C2500] text-[#FDF6DC] text-[10px] font-extrabold uppercase tracking-wider">
                                Aktif
                            </span>
                            <p class="text-[11px] font-bold text-[#CD7000] mt-1">Kurikulum SKPI 2026</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-5">
                        <div class="rounded-2xl bg-[#FDF6DC]/95 p-4 border border-[#4C2500]/12">
                            <p class="text-2xl font-extrabold text-[#4C2500]">{{ $stats['verified_credentials'] }}</p>
                            <p class="text-xs font-semibold text-[#4C2500]/80 mt-0.5">Kredensial Terbit</p>
                        </div>
                        <div class="rounded-2xl bg-[#F2AA00]/25 p-4 border border-[#CD7000]/30">
                            <p class="text-2xl font-extrabold text-[#4C2500]">{{ $stats['approved_activities'] }}</p>
                            <p class="text-xs font-semibold text-[#4C2500]/80 mt-0.5">Capaian Disetujui</p>
                        </div>
                        <div class="rounded-2xl bg-[#F2AA00]/25 p-4 border border-[#CD7000]/30">
                            <p class="text-2xl font-extrabold text-[#4C2500]">{{ $stats['active_students'] }}</p>
                            <p class="text-xs font-semibold text-[#4C2500]/80 mt-0.5">Mahasiswa Aktif</p>
                        </div>
                        <div class="rounded-2xl bg-[#FDF6DC]/95 p-4 border border-[#4C2500]/12">
                            <p class="text-2xl font-extrabold text-[#4C2500]">{{ $stats['public_portfolios'] }}</p>
                            <p class="text-xs font-semibold text-[#4C2500]/80 mt-0.5">Portofolio Publik</p>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('verify.index') }}" class="mt-5 pt-4 border-t border-[#4C2500]/15">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#4C2500] mb-2">
                            Periksa Nomor Kredensial / SKPI
                        </label>
                        <div class="flex gap-2">
                            <input type="text" name="code" required
                                   placeholder="Masukkan nomor registrasi..."
                                   class="flex-1 rounded-xl bg-white border border-[#4C2500]/25 px-3.5 py-2 text-xs font-mono text-[#4C2500] placeholder:text-[#4C2500]/55 focus:outline-none focus:border-[#CD7000]">
                            <button type="submit"
                                    class="rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F2AA00] hover:text-[#FDF6DC] px-4 py-2 text-xs font-extrabold transition cursor-pointer">
                                Periksa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- PERAN PENGGUNA --}}
    <section class="mb-12">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-widest text-[#CD7000]">Layanan Sesuai Peran</p>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-[#4C2500] mt-1">Dashboard Khusus untuk Setiap Pengguna</h2>
            </div>
            @guest
                <a href="{{ route('login') }}" class="text-xs font-bold text-[#CD7000] hover:text-[#4C2500] underline">
                    Masuk sesuai akun Anda →
                </a>
            @endguest
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach([
                [
                    'tag' => 'Mahasiswa',
                    'title' => 'Portofolio & Pengajuan SKPI',
                    'desc' => 'Unggah bukti kegiatan, pantau progres poin kelulusan dan peta kompetensi, cetak CV siap lamaran, serta bagikan sertifikat ke LinkedIn.',
                ],
                [
                    'tag' => 'Dosen PA',
                    'title' => 'Tinjauan & Validasi Berkas',
                    'desc' => 'Periksa bukti sertifikat mahasiswa langsung berdampingan dengan formulir penilaian, berikan catatan revisi, atau setujui secara massal.',
                ],
                [
                    'tag' => 'Admin Kemahasiswaan',
                    'title' => 'Penerbitan & Tata Kelola',
                    'desc' => 'Terbitkan sertifikat kegiatan internal kampus sekaligus untuk banyak NIM, atur kategori poin SKPI, kelola akun, dan pantau riwayat audit.',
                ],
                [
                    'tag' => 'Mitra Karier / HRD',
                    'title' => 'Pencarian Talenta & Cek QR',
                    'desc' => 'Telusuri kandidat mahasiswa berdasarkan keahlian tervalidasi dan pastikan keaslian SKPI pelamar secara langsung lewat kode QR.',
                ],
            ] as $item)
                <div class="honey-card rounded-3xl p-6 flex flex-col justify-between">
                    <div>
                        <span class="inline-block px-3 py-1 rounded-full bg-[#F9E7A9] border border-[#CD7000]/35 text-[11px] font-extrabold text-[#4C2500]">
                            {{ $item['tag'] }}
                        </span>
                        <h3 class="mt-3.5 text-base font-extrabold text-[#4C2500]">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-xs text-[#4C2500]/85 leading-relaxed">{{ $item['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- TINGKAT VERIFIKASI --}}
    <section class="mb-12">
        <div class="mb-6">
            <p class="text-xs font-extrabold uppercase tracking-widest text-[#CD7000]">Transparansi Validasi</p>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#4C2500] mt-1">Tiga Tingkat Verifikasi Sertifikat</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="honey-card-strong rounded-3xl p-6 border-2 border-[#F2AA00]">
                <x-trust-badge :tier="1" />
                <h3 class="mt-3.5 text-lg font-extrabold text-[#4C2500]">Terbitan Langsung Kampus</h3>
                <p class="mt-2 text-xs text-[#4C2500]/85 leading-relaxed">
                    Diterbitkan otomatis oleh unit kemahasiswaan untuk kegiatan resmi internal kampus. Mahasiswa tidak perlu mengunggah ulang berkas.
                </p>
            </div>

            <div class="honey-card rounded-3xl p-6 border border-[#CD7000]/40">
                <x-trust-badge :tier="2" />
                <h3 class="mt-3.5 text-lg font-extrabold text-[#4C2500]">Eksternal Terverifikasi Daring</h3>
                <p class="mt-2 text-xs text-[#4C2500]/85 leading-relaxed">
                    Sertifikat dari lembaga eksternal (seperti BNSP, AWS, Google, Dicoding, Coursera, atau Credly) yang dilengkapi tautan verifikasi resmi.
                </p>
            </div>

            <div class="honey-card rounded-3xl p-6 border border-[#4C2500]/25">
                <x-trust-badge :tier="3" />
                <h3 class="mt-3.5 text-lg font-extrabold text-[#4C2500]">Tinjauan Administratif</h3>
                <p class="mt-2 text-xs text-[#4C2500]/85 leading-relaxed">
                    Sertifikat eksternal tanpa tautan verifikasi daring yang telah diperiksa secara langsung berdasarkan bukti dokumen oleh Dosen PA.
                </p>
            </div>
        </div>
    </section>

    {{-- KEAMANAN & PORTOFOLIO PUBLIK --}}
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-10">
        <div class="lg:col-span-5 espresso-card rounded-3xl p-7 flex flex-col justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-widest text-[#F2AA00]">Perlindungan Data &amp; Integritas</p>
                <h2 class="mt-1.5 text-xl font-extrabold text-[#FDF6DC]">Keamanan Dokumen Akademik</h2>
                <ul class="mt-5 space-y-3 text-xs text-[#F9E7A9]/90">
                    <li class="flex items-start gap-2.5">
                        <x-icon name="shield-check" class="w-4 h-4 text-[#F2AA00] shrink-0 mt-0.5" />
                        <span><strong class="text-[#FDF6DC]">Penyimpanan Berkas Privat:</strong> pengecekan tipe file asli, deteksi duplikasi otomatis, serta tautan pratinjau berbatas waktu.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <x-icon name="qrcode" class="w-4 h-4 text-[#F2AA00] shrink-0 mt-0.5" />
                        <span><strong class="text-[#FDF6DC]">Tanda Tangan Digital &amp; Status Pencabutan:</strong> setiap QR terikat tanda tangan kriptografi dan langsung memperbarui status jika dokumen dicabut.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <x-icon name="lock" class="w-4 h-4 text-[#F2AA00] shrink-0 mt-0.5" />
                        <span><strong class="text-[#FDF6DC]">Privasi Bawaan (UU PDP):</strong> halaman profil publik nonaktif sampai diaktifkan sendiri oleh mahasiswa, lengkap dengan kontrol visibilitas per item.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <x-icon name="document-check" class="w-4 h-4 text-[#F2AA00] shrink-0 mt-0.5" />
                        <span><strong class="text-[#FDF6DC]">Catatan Terkunci &amp; Riwayat Audit:</strong> data kegiatan yang telah disetujui dikunci permanen agar tidak dapat dimanipulasi.</span>
                    </li>
                </ul>
            </div>

            @guest
                <div class="mt-6 pt-5 border-t border-[#F9E7A9]/20 flex flex-wrap gap-2.5">
                    <a href="{{ route('login') }}" class="rounded-xl bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-[#FDF6DC] px-5 py-2.5 text-xs font-extrabold transition">
                        Masuk Sekarang
                    </a>
                    <a href="{{ route('register') }}" class="rounded-xl bg-[#FDF6DC]/15 hover:bg-[#FDF6DC]/25 text-[#FDF6DC] px-5 py-2.5 text-xs font-bold transition">
                        Daftar Akun
                    </a>
                </div>
            @endguest
        </div>

        <div class="lg:col-span-7 honey-card rounded-3xl p-7">
            <div class="flex items-center justify-between gap-2 mb-5">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-widest text-[#CD7000]">Portofolio Publik</p>
                    <h2 class="text-xl font-extrabold text-[#4C2500] mt-0.5">Profil Mahasiswa Terverifikasi</h2>
                </div>
                <a href="{{ route('talent.index') }}" class="text-xs font-bold text-[#CD7000] hover:text-[#4C2500] underline">
                    Lihat Direktori Talenta →
                </a>
            </div>

            <div class="space-y-3.5">
                @foreach($featuredPortfolios as $fp)
                    <a href="{{ route('portfolio.show', $fp->public_slug) }}"
                       class="group block rounded-2xl bg-[#FDF6DC] hover:bg-[#F9E7A9]/80 border border-[#4C2500]/15 hover:border-[#CD7000]/50 p-4 transition">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-extrabold text-[#4C2500] group-hover:text-[#CD7000] transition">{{ $fp->user->name }}</h3>
                                    <span class="font-mono text-[11px] font-bold text-[#CD7000]">{{ '@' . $fp->public_slug }}</span>
                                </div>
                                <p class="text-xs text-[#4C2500]/80 mt-0.5">{{ $fp->prodi_name }} • Semester {{ $fp->semester }} — {{ $fp->headline }}</p>
                            </div>
                            <span class="shrink-0 self-start sm:self-center px-3 py-1 rounded-full bg-[#4C2500] text-[#F2AA00] text-[11px] font-extrabold whitespace-nowrap">
                                {{ $fp->user->activities->sum('awarded_points') }} Poin SKPI
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endsection
