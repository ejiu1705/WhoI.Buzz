@php
    $user = auth()->user();
    if ($user) {
        $user->loadMissing('roles');
    }
    $isPublicHome = request()->routeIs('home');
@endphp

{{-- Expandable Floating Pill Navbar: ikon bulat melayang yang berekspansi horizontal saat hover/klik --}}
<header class="no-print fixed top-4 left-1/2 -translate-x-1/2 z-50 flex flex-col items-center w-full max-w-[96vw] pointer-events-none">
    <nav id="whoibuzz-floating-pill"
         aria-label="Navigasi Utama WhoI.Buzz"
         class="floating-pill-shell is-shrunk pointer-events-auto inline-flex items-center bg-[#4C2500]/92 text-[#F9E7A9] backdrop-blur-xl border border-[#F2AA00]/50 shadow-[0_14px_38px_-10px_rgba(76,37,0,0.55)] max-w-full">

        {{-- Logo Bulat Tunggal --}}
        <button type="button"
                id="pill-orb-trigger"
                aria-expanded="false"
                aria-label="Buka navigasi WhoI.Buzz"
                title="WhoI.Buzz — Arahkan kursor atau klik untuk membuka menu"
                class="pill-orb-btn relative flex items-center justify-center h-10 w-10 rounded-full bg-[#FDF6DC] border-2 border-[#F2AA00] shadow-inner shrink-0 cursor-pointer">
            <img src="{{ asset('images/whoibuzz-mark.png') }}" alt="WhoI.Buzz" class="h-5 w-auto object-contain">
        </button>

        {{-- Konten Ekspansi Horizontal --}}
        <div class="pill-expandable gap-2.5">
            <a href="{{ route('home') }}"
               class="text-sm font-extrabold tracking-tight text-[#FDF6DC] hover:text-[#F2AA00] transition shrink-0 pr-1"
               title="Beranda WhoI.Buzz">
                WhoI<span class="text-[#F2AA00]">.Buzz</span>
            </a>

            <span class="hidden md:inline-block h-4 w-px bg-[#F9E7A9]/25 shrink-0"></span>

            {{-- Navigasi Desktop Berikon SVG --}}
            <div class="hidden md:flex items-center gap-1">
                <a href="{{ route('home') }}"
                   class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ $isPublicHome ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9] hover:bg-[#FDF6DC]/15 hover:text-[#FDF6DC]' }}">
                    <x-icon name="home" class="w-3.5 h-3.5" />
                    <span>Beranda</span>
                </a>

                @auth
                    @role('admin')
                        <a href="{{ route('admin.dashboard') }}"
                           class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('admin.dashboard') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9] hover:bg-[#FDF6DC]/15 hover:text-[#FDF6DC]' }}">
                            <x-icon name="building" class="w-3.5 h-3.5" />
                            <span>Admin</span>
                        </a>
                        <a href="{{ route('admin.users.index') }}"
                           class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('admin.users.*') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9] hover:bg-[#FDF6DC]/15 hover:text-[#FDF6DC]' }}">
                            <x-icon name="users" class="w-3.5 h-3.5" />
                            <span>Pengguna</span>
                        </a>
                    @endrole

                    @role('dosen')
                        <a href="{{ route('dosen.dashboard') }}"
                           class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('dosen.dashboard') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9] hover:bg-[#FDF6DC]/15 hover:text-[#FDF6DC]' }}">
                            <x-icon name="clipboard-check" class="w-3.5 h-3.5" />
                            <span>Validasi</span>
                        </a>
                    @endrole

                    @role('mahasiswa')
                        <a href="{{ route('mahasiswa.dashboard') }}"
                           class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('mahasiswa.dashboard') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9] hover:bg-[#FDF6DC]/15 hover:text-[#FDF6DC]' }}">
                            <x-icon name="academic-cap" class="w-3.5 h-3.5" />
                            <span>Dashboard</span>
                        </a>
                    @endrole

                    @role('mitra')
                        <a href="{{ route('mitra.dashboard') }}"
                           class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('mitra.dashboard') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9] hover:bg-[#FDF6DC]/15 hover:text-[#FDF6DC]' }}">
                            <x-icon name="sparkles" class="w-3.5 h-3.5" />
                            <span>Talenta</span>
                        </a>
                    @endrole

                    @can('lihat-progres')
                        <a href="{{ route('laporan.progres') }}"
                           class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('laporan.progres') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9] hover:bg-[#FDF6DC]/15 hover:text-[#FDF6DC]' }}">
                            <x-icon name="chart-bar" class="w-3.5 h-3.5" />
                            <span>Progres</span>
                        </a>
                    @endcan

                    <a href="{{ route('kurikulum-skpi.index') }}"
                       class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('kurikulum-skpi.*') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9] hover:bg-[#FDF6DC]/15 hover:text-[#FDF6DC]' }}">
                        <x-icon name="book-open" class="w-3.5 h-3.5" />
                        <span>SKPI</span>
                    </a>

                    <a href="{{ route('verify.index') }}"
                       class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('verify.*') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9] hover:bg-[#FDF6DC]/15 hover:text-[#FDF6DC]' }}">
                        <x-icon name="qrcode" class="w-3.5 h-3.5" />
                        <span>Verifikasi</span>
                    </a>

                    <span class="mx-1 h-5 w-px bg-[#F9E7A9]/25"></span>

                    <a href="{{ route('account.show') }}"
                       class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('account.*') ? 'bg-[#F2AA00] text-[#4C2500]' : 'bg-[#CD7000] text-[#FDF6DC] hover:bg-[#F2AA00] hover:text-[#4C2500]' }}"
                       title="Info Akun ({{ $user->name }})">
                        <x-icon name="user-circle" class="w-3.5 h-3.5" />
                        <span>Akun</span>
                    </a>

                    <a href="{{ route('logout.confirm') }}"
                       class="inline-flex items-center justify-center rounded-full p-2 text-[#FDF6DC] hover:bg-red-700 hover:text-white transition"
                       title="Keluar dari Sesi">
                        <x-icon name="logout" class="w-4 h-4" />
                    </a>
                @else
                    <a href="{{ route('verify.index') }}"
                       class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('verify.*') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9] hover:bg-[#FDF6DC]/15 hover:text-[#FDF6DC]' }}">
                        <x-icon name="qrcode" class="w-3.5 h-3.5" />
                        <span>Verifikasi</span>
                    </a>

                    <a href="{{ route('talent.index') }}"
                       class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('talent.*') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9] hover:bg-[#FDF6DC]/15 hover:text-[#FDF6DC]' }}">
                        <x-icon name="sparkles" class="w-3.5 h-3.5" />
                        <span>Talent Pool</span>
                    </a>

                    @unless($isPublicHome)
                        <span class="mx-1 h-5 w-px bg-[#F9E7A9]/25"></span>

                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-extrabold transition {{ request()->routeIs('login') ? 'bg-[#F2AA00] text-[#4C2500]' : 'bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-[#FDF6DC]' }}">
                            <x-icon name="login" class="w-3.5 h-3.5" />
                            <span>Masuk</span>
                        </a>

                        <a href="{{ route('register') }}"
                           class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-extrabold transition {{ request()->routeIs('register') ? 'bg-[#F2AA00] text-[#4C2500]' : 'bg-[#FDF6DC] hover:bg-[#F9E7A9] text-[#4C2500]' }}">
                            <x-icon name="user-plus" class="w-3.5 h-3.5" />
                            <span>Daftar</span>
                        </a>
                    @endunless
                @endauth
            </div>

            {{-- Tombol Menu Mobile --}}
            <button type="button"
                    id="pill-mobile-toggle"
                    aria-expanded="false"
                    aria-label="Buka menu navigasi"
                    class="md:hidden inline-flex items-center gap-1.5 rounded-full bg-[#F2AA00] text-[#4C2500] px-3 py-1.5 text-xs font-extrabold cursor-pointer">
                <x-icon name="menu" class="w-4 h-4" />
                <span>Menu</span>
            </button>
        </div>
    </nav>

    {{-- Menu Dropdown Mobile --}}
    <div id="pill-mobile-menu" class="pill-mobile-menu md:hidden pointer-events-auto w-[92vw] max-w-sm mt-2">
        <div class="overflow-hidden">
            <div class="rounded-3xl bg-[#4C2500]/96 backdrop-blur-xl border border-[#F2AA00]/45 shadow-2xl p-4 text-[#F9E7A9]">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold bg-[#FDF6DC]/12 text-[#FDF6DC]">
                        <x-icon name="home" class="w-4 h-4" /><span>Beranda</span>
                    </a>
                    <a href="{{ route('verify.index') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold bg-[#FDF6DC]/12 text-[#FDF6DC]">
                        <x-icon name="qrcode" class="w-4 h-4" /><span>Verifikasi</span>
                    </a>
                    @auth
                        <a href="{{ $user->dashboardRoute() }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-extrabold bg-[#F2AA00] text-[#4C2500]">
                            <x-icon name="academic-cap" class="w-4 h-4" /><span>Dashboard</span>
                        </a>
                        <a href="{{ route('kurikulum-skpi.index') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold bg-[#FDF6DC]/12 text-[#FDF6DC]">
                            <x-icon name="book-open" class="w-4 h-4" /><span>Kurikulum</span>
                        </a>
                        @role('admin')
                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold bg-[#FDF6DC]/12 text-[#FDF6DC]">
                                <x-icon name="users" class="w-4 h-4" /><span>Pengguna</span>
                            </a>
                        @endrole
                        @can('lihat-progres')
                            <a href="{{ route('laporan.progres') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold bg-[#FDF6DC]/12 text-[#FDF6DC]">
                                <x-icon name="chart-bar" class="w-4 h-4" /><span>Progres</span>
                            </a>
                        @endcan
                        <a href="{{ route('account.show') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold bg-[#CD7000] text-[#FDF6DC]">
                            <x-icon name="user-circle" class="w-4 h-4" /><span>Info Akun</span>
                        </a>
                        <a href="{{ route('logout.confirm') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-extrabold bg-red-800 text-white">
                            <x-icon name="logout" class="w-4 h-4" /><span>Logout</span>
                        </a>
                    @else
                        <a href="{{ route('talent.index') }}" class="col-span-2 flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold bg-[#FDF6DC]/12 text-[#FDF6DC]">
                            <x-icon name="sparkles" class="w-4 h-4" /><span>Talent Pool</span>
                        </a>
                        @unless($isPublicHome)
                            <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-xs font-extrabold bg-[#F2AA00] text-[#4C2500]">
                                <x-icon name="login" class="w-4 h-4" /><span>Masuk</span>
                            </a>
                            <a href="{{ route('register') }}" class="flex items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-xs font-extrabold bg-[#FDF6DC] text-[#4C2500]">
                                <x-icon name="user-plus" class="w-4 h-4" /><span>Daftar</span>
                            </a>
                        @endunless
                    @endauth
                </div>
            </div>
        </div>
    </div>
</header>
