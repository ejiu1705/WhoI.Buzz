@php
    $user = auth()->user();
    if ($user) {
        $user->loadMissing('roles');
    }
    $isPublicHome = request()->routeIs('home');
@endphp

{{-- Expandable Floating Pill Navbar: starts as sleek centered icon orb, expands horizontally on hover/click to reveal WHOI.BUZZ logo & SVG icon links --}}
<header class="no-print fixed top-4 left-1/2 -translate-x-1/2 z-50 flex flex-col items-center w-full max-w-[96vw] pointer-events-none">
    <nav id="whoibuzz-floating-pill"
         aria-label="Navigasi Utama WhoI.Buzz"
         class="floating-pill-shell is-shrunk pointer-events-auto inline-flex items-center bg-[#4C2500]/90 text-[#F9E7A9] backdrop-blur-xl border border-[#F2AA00]/50 shadow-[0_14px_38px_-10px_rgba(76,37,0,0.55)] max-w-full">

        {{-- CENTERED ICON ORB TRIGGER (Always visible; hover or click expands the pill horizontally) --}}
        <button type="button"
                id="pill-orb-trigger"
                aria-expanded="false"
                aria-label="Ekspansi navigasi WhoI.Buzz"
                title="WhoI.Buzz — Arahkan kursor atau klik untuk membuka navigasi"
                class="relative flex items-center justify-center h-10 w-10 rounded-full bg-[#FDF6DC] border-2 border-[#F2AA00] shadow-inner shrink-0 cursor-pointer hover:scale-105 transition">
            <img src="{{ asset('images/whoibuzz-mark.png') }}" alt="WB" class="h-5 w-auto object-contain">
        </button>

        {{-- HORIZONTAL EXPANDABLE CONTENT (Reveals Full WHOI.BUZZ Logo + Crisp SVG Icon Links) --}}
        <div class="pill-expandable gap-3">
            {{-- Revealed Full WHOI.BUZZ Logo --}}
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 rounded-full bg-[#FDF6DC] px-3 py-1 border border-[#F2AA00]/60 shadow-inner shrink-0 hover:bg-[#F9E7A9] transition"
               title="Beranda WhoI.Buzz">
                <img src="{{ asset('images/whoibuzz-logo.png') }}" alt="WHOI.BUZZ" class="h-6 w-auto object-contain">
            </a>

            {{-- Divider --}}
            <span class="hidden md:inline-block h-5 w-px bg-[#F9E7A9]/20 shrink-0"></span>

            {{-- Desktop Horizontal Role-Aware SVG Icon Navigation --}}
            <div class="hidden md:flex items-center gap-1">
                <a href="{{ route('home') }}"
                   class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ $isPublicHome ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9]/90 hover:bg-[#FDF6DC]/12 hover:text-[#FDF6DC]' }}">
                    <x-icon name="home" class="w-3.5 h-3.5" />
                    <span>Beranda</span>
                </a>

                @auth
                    @role('admin')
                        <a href="{{ route('admin.dashboard') }}"
                           class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('admin.dashboard') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9]/90 hover:bg-[#FDF6DC]/12 hover:text-[#FDF6DC]' }}">
                            <x-icon name="building" class="w-3.5 h-3.5" />
                            <span>Admin</span>
                        </a>
                        <a href="{{ route('admin.users.index') }}"
                           class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('admin.users.*') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9]/90 hover:bg-[#FDF6DC]/12 hover:text-[#FDF6DC]' }}">
                            <x-icon name="users" class="w-3.5 h-3.5" />
                            <span>Akun</span>
                        </a>
                    @endrole

                    @role('dosen')
                        <a href="{{ route('dosen.dashboard') }}"
                           class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('dosen.dashboard') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9]/90 hover:bg-[#FDF6DC]/12 hover:text-[#FDF6DC]' }}">
                            <x-icon name="clipboard-check" class="w-3.5 h-3.5" />
                            <span>Validasi</span>
                        </a>
                    @endrole

                    @role('mahasiswa')
                        <a href="{{ route('mahasiswa.dashboard') }}"
                           class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('mahasiswa.dashboard') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9]/90 hover:bg-[#FDF6DC]/12 hover:text-[#FDF6DC]' }}">
                            <x-icon name="academic-cap" class="w-3.5 h-3.5" />
                            <span>Dashboard</span>
                        </a>
                    @endrole

                    @role('mitra')
                        <a href="{{ route('mitra.dashboard') }}"
                           class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('mitra.dashboard') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9]/90 hover:bg-[#FDF6DC]/12 hover:text-[#FDF6DC]' }}">
                            <x-icon name="sparkles" class="w-3.5 h-3.5" />
                            <span>Talenta</span>
                        </a>
                    @endrole

                    @can('lihat progres')
                        <a href="{{ route('laporan.progres') }}"
                           class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('laporan.progres') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9]/90 hover:bg-[#FDF6DC]/12 hover:text-[#FDF6DC]' }}">
                            <x-icon name="chart-bar" class="w-3.5 h-3.5" />
                            <span>Progres</span>
                        </a>
                    @endcan

                    <a href="{{ route('kurikulum-skpi.index') }}"
                       class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('kurikulum-skpi.*') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9]/90 hover:bg-[#FDF6DC]/12 hover:text-[#FDF6DC]' }}">
                        <x-icon name="book-open" class="w-3.5 h-3.5" />
                        <span>SKPI</span>
                    </a>

                    <a href="{{ route('verify.index') }}"
                       class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('verify.*') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9]/90 hover:bg-[#FDF6DC]/12 hover:text-[#FDF6DC]' }}">
                        <x-icon name="qrcode" class="w-3.5 h-3.5" />
                        <span>Verifikasi</span>
                    </a>

                    <span class="mx-1 h-5 w-px bg-[#F9E7A9]/20"></span>

                    <a href="{{ route('account.show') }}"
                       class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('account.*') ? 'bg-[#F2AA00] text-[#4C2500]' : 'bg-[#CD7000]/80 text-[#FDF6DC] hover:bg-[#F2AA00] hover:text-[#4C2500]' }}"
                       title="Info Akun ({{ $user->name }})">
                        <x-icon name="user-circle" class="w-3.5 h-3.5" />
                        <span>Akun</span>
                    </a>

                    <a href="{{ route('logout.confirm') }}"
                       class="inline-flex items-center justify-center rounded-full p-2 text-red-200 hover:bg-red-700 hover:text-white transition"
                       title="Verifikasi Logout">
                        <x-icon name="logout" class="w-4 h-4" />
                    </a>
                @else
                    <a href="{{ route('verify.index') }}"
                       class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('verify.*') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9]/90 hover:bg-[#FDF6DC]/12 hover:text-[#FDF6DC]' }}">
                        <x-icon name="qrcode" class="w-3.5 h-3.5" />
                        <span>Verifikasi</span>
                    </a>

                    <a href="{{ route('talent.index') }}"
                       class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('talent.*') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9]/90 hover:bg-[#FDF6DC]/12 hover:text-[#FDF6DC]' }}">
                        <x-icon name="sparkles" class="w-3.5 h-3.5" />
                        <span>Talent Pool</span>
                    </a>

                    <a href="{{ route('api.client') }}"
                       class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('api.client') ? 'bg-[#F2AA00] text-[#4C2500]' : 'text-[#F9E7A9]/90 hover:bg-[#FDF6DC]/12 hover:text-[#FDF6DC]' }}">
                        <x-icon name="code" class="w-3.5 h-3.5" />
                        <span>API</span>
                    </a>

                    {{-- Login & Register only appear in navbar outside the initial public dashboard (which already has dedicated Login/Register buttons) --}}
                    @unless($isPublicHome)
                        <span class="mx-1 h-5 w-px bg-[#F9E7A9]/20"></span>

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

            {{-- Mobile Menu Toggle Button --}}
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

    {{-- MOBILE COLLAPSIBLE CAPSULE MENU --}}
    <div id="pill-mobile-menu" class="pill-mobile-menu md:hidden pointer-events-auto w-[92vw] max-w-sm mt-2">
        <div class="overflow-hidden">
            <div class="rounded-3xl bg-[#4C2500]/95 backdrop-blur-xl border border-[#F2AA00]/45 shadow-2xl p-4 text-[#F9E7A9]">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold bg-[#FDF6DC]/10 text-[#FDF6DC]">
                        <x-icon name="home" class="w-4 h-4" /><span>Beranda</span>
                    </a>
                    <a href="{{ route('verify.index') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold bg-[#FDF6DC]/10 text-[#FDF6DC]">
                        <x-icon name="qrcode" class="w-4 h-4" /><span>Verifikasi</span>
                    </a>
                    @auth
                        <a href="{{ $user->dashboardRoute() }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-extrabold bg-[#F2AA00] text-[#4C2500]">
                            <x-icon name="academic-cap" class="w-4 h-4" /><span>Dashboard</span>
                        </a>
                        <a href="{{ route('kurikulum-skpi.index') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold bg-[#FDF6DC]/10 text-[#FDF6DC]">
                            <x-icon name="book-open" class="w-4 h-4" /><span>Kurikulum</span>
                        </a>
                        @role('admin')
                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold bg-[#FDF6DC]/10 text-[#FDF6DC]">
                                <x-icon name="users" class="w-4 h-4" /><span>Kelola Akun</span>
                            </a>
                        @endrole
                        @can('lihat progres')
                            <a href="{{ route('laporan.progres') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold bg-[#FDF6DC]/10 text-[#FDF6DC]">
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
                        <a href="{{ route('talent.index') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold bg-[#FDF6DC]/10 text-[#FDF6DC]">
                            <x-icon name="sparkles" class="w-4 h-4" /><span>Talent Pool</span>
                        </a>
                        <a href="{{ route('api.client') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold bg-[#FDF6DC]/10 text-[#FDF6DC]">
                            <x-icon name="code" class="w-4 h-4" /><span>API Client</span>
                        </a>
                        @unless($isPublicHome)
                            <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-xs font-extrabold bg-[#F2AA00] text-[#4C2500]">
                                <x-icon name="login" class="w-4 h-4" /><span>Masuk</span>
                            </a>
                            <a href="{{ route('register') }}" class="flex items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-xs font-extrabold bg-[#FDF6DC] text-[#4C2500]">
                                <x-icon name="user-plus" class="w-4 h-4" /><span>Registrasi</span>
                            </a>
                        @endunless
                    @endauth
                </div>
            </div>
        </div>
    </div>
</header>
