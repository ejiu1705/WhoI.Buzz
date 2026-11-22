@extends('layouts.app')

@section('title', 'Verifikasi Logout — WhoI.Buzz')

@section('content')
    <div class="max-w-lg mx-auto my-6">
        <div class="espresso-card rounded-[30px] p-7 sm:p-9 text-center">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-[#FDF6DC] border-2 border-[#F2AA00] mx-auto shadow">
                <img src="{{ asset('images/whoibuzz-mark.png') }}" alt="WB" class="h-9 w-auto object-contain">
            </div>

            <span class="mt-4 inline-block px-3 py-1 rounded-full bg-[#F2AA00] text-[#4C2500] text-[11px] font-extrabold uppercase tracking-wider">
                Verifikasi Logout Keamanan Sesi
            </span>

            <h1 class="mt-3 text-2xl font-extrabold text-[#FDF6DC]">Yakin Ingin Keluar dari Sesi?</h1>
            <p class="mt-2 text-xs text-[#F9E7A9]/80 leading-relaxed">
                Konfirmasi logout mencegah keluar secara tidak sengaja dan memastikan seluruh sesi serta token Anda ditutup dengan bersih.
            </p>

            {{-- Active Session Summary --}}
            <div class="mt-5 rounded-2xl bg-[#331800]/80 border border-[#F2AA00]/30 p-4 text-left text-xs space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-[#F9E7A9]/70">Pengguna Aktif:</span>
                    <strong class="text-[#FDF6DC]">{{ $user->name }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#F9E7A9]/70">Email:</span>
                    <span class="font-mono text-[#F2AA00]">{{ $user->email }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#F9E7A9]/70">Role RBAC:</span>
                    <span class="px-2 py-0.5 rounded-full bg-[#F2AA00]/25 text-[#F2AA00] font-bold">{{ $user->roleLabel() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#F9E7A9]/70">Token API Sanctum Aktif:</span>
                    <span class="font-mono font-bold text-[#FDF6DC]">{{ $activeTokensCount }} token</span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="mt-5 space-y-4">
                @csrf

                @if($activeTokensCount > 0)
                    <label class="flex items-center gap-2.5 rounded-xl bg-[#FDF6DC]/10 border border-[#F9E7A9]/20 px-4 py-3 text-left text-xs cursor-pointer">
                        <input type="checkbox" name="revoke_api_tokens" value="1" class="rounded text-[#F2AA00]">
                        <span class="text-[#F9E7A9]">Cabut juga semua <strong>{{ $activeTokensCount }} Bearer Token API Sanctum</strong> milik akun ini</span>
                    </label>
                @endif

                <div class="grid grid-cols-2 gap-3 pt-1">
                    <a href="{{ $user->dashboardRoute() }}"
                       class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#FDF6DC]/15 hover:bg-[#FDF6DC]/25 text-[#FDF6DC] py-3 text-xs font-extrabold transition">
                        <span>Batal, Tetap Masuk</span>
                    </a>
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-red-700 hover:bg-red-600 text-white py-3 text-xs font-extrabold transition cursor-pointer shadow-lg">
                        <x-icon name="logout" class="w-4 h-4" />
                        <span>Ya, Keluar Sekarang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
