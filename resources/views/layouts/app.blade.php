<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WhoI.Buzz — Portal Kredensial & E-Portfolio Kampus')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased selection:bg-[#F2AA00] selection:text-[#4C2500] honeycomb-bg">

    <x-floating-navbar />

    {{-- Notifikasi Sistem --}}
    <div class="no-print max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24">
        @if (session('success'))
            <div class="mb-6 rounded-2xl border border-[#CD7000]/40 bg-[#4C2500] text-[#FDF6DC] px-5 py-3.5 shadow-lg flex items-center gap-3 text-sm font-medium">
                <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#F2AA00] text-[#4C2500]">
                    <x-icon name="check" class="w-3.5 h-3.5" />
                </span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-2xl border border-red-800/40 bg-red-950 text-[#FDF6DC] px-5 py-3.5 shadow-lg flex items-center gap-3 text-sm font-medium">
                <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-600 text-white">
                    <x-icon name="exclamation" class="w-3.5 h-3.5" />
                </span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-[#CD7000] bg-[#FDF6DC] text-[#4C2500] px-5 py-4 shadow-md">
                <p class="font-bold text-sm mb-1.5 text-[#CD7000]">Mohon periksa kembali isian berikut:</p>
                <ul class="list-disc list-inside text-xs space-y-1 text-[#4C2500]">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Konten Utama --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20 @if(!session('success') && !session('error') && !$errors->any()) pt-4 @endif">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="no-print border-t border-[#4C2500]/15 bg-[#4C2500] text-[#F9E7A9]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="bg-[#FDF6DC] rounded-2xl px-3.5 py-2 shadow-inner">
                    <img src="{{ asset('images/whoibuzz-logo.png') }}" alt="WHOI.BUZZ" class="h-10 w-auto object-contain">
                </div>
                <div>
                    <p class="font-extrabold tracking-tight text-[#FDF6DC] text-sm">WhoI.Buzz — Portal Kredensial &amp; E-Portfolio Kampus</p>
                    <p class="text-xs text-[#F9E7A9]/85">Verifikasi sertifikat, pencatatan poin SKPI dwibahasa, dan portofolio karier mahasiswa.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-xs">
                <a href="{{ route('verify.index') }}" class="px-3.5 py-1.5 rounded-full bg-[#F9E7A9]/15 hover:bg-[#F2AA00] text-[#FDF6DC] hover:text-[#4C2500] transition font-semibold">Verifikasi Kredensial</a>
                <a href="{{ route('talent.index') }}" class="px-3.5 py-1.5 rounded-full bg-[#F9E7A9]/15 hover:bg-[#F2AA00] text-[#FDF6DC] hover:text-[#4C2500] transition font-semibold">Direktori Talenta</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
