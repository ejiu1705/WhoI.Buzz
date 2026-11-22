@extends('layouts.app')

@section('title', 'Konfirmasi Keluar — WhoI.Buzz')

@section('content')
    <div class="max-w-lg mx-auto my-6">
        <div class="espresso-card rounded-[30px] p-7 sm:p-9 text-center">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-[#FDF6DC] border-2 border-[#F2AA00] mx-auto shadow">
                <img src="{{ asset('images/whoibuzz-mark.png') }}" alt="WhoI.Buzz" class="h-9 w-auto object-contain">
            </div>

            <span class="mt-4 inline-block px-3 py-1 rounded-full bg-[#F2AA00] text-[#4C2500] text-[11px] font-extrabold uppercase tracking-wider">
                Verifikasi Logout
            </span>

            <h1 class="mt-3 text-2xl font-extrabold text-[#FDF6DC]">Yakin Ingin Keluar dari Sesi?</h1>
            <p class="mt-2 text-xs text-[#F9E7A9]/90 leading-relaxed">
                Pastikan seluruh perubahan Anda telah tersimpan sebelum mengakhiri sesi akun ini.
            </p>

            <div class="mt-5 rounded-2xl bg-[#331800]/85 border border-[#F2AA00]/35 p-4 text-left text-xs space-y-2">
                <div class="flex justify-between gap-3">
                    <span class="text-[#F9E7A9]/80">Nama Akun:</span>
                    <strong class="text-[#FDF6DC]">{{ $user->name }}</strong>
                </div>
                <div class="flex justify-between gap-3">
                    <span class="text-[#F9E7A9]/80">Email:</span>
                    <span class="font-mono text-[#F2AA00]">{{ $user->email }}</span>
                </div>
                <div class="flex justify-between gap-3">
                    <span class="text-[#F9E7A9]/80">Peran:</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-[#F2AA00]/25 text-[#F2AA00] font-bold">{{ $user->roleLabel() }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="mt-5 space-y-4">
                @csrf

                @role('admin')
                    @if($activeTokensCount > 0)
                        <label class="flex items-center gap-2.5 rounded-xl bg-[#FDF6DC]/12 border border-[#F9E7A9]/25 px-4 py-3 text-left text-xs cursor-pointer">
                            <input type="checkbox" name="revoke_api_tokens" value="1" class="rounded text-[#F2AA00]">
                            <span class="text-[#FDF6DC]">Cabut juga <strong>{{ $activeTokensCount }} token integrasi API</strong> yang sedang aktif</span>
                        </label>
                    @endif
                @endrole

                <div class="grid grid-cols-2 gap-3 pt-1">
                    <a href="{{ $user->dashboardRoute() }}"
                       class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#FDF6DC]/15 hover:bg-[#FDF6DC]/25 text-[#FDF6DC] py-3 text-xs font-extrabold transition">
                        <span>Batal</span>
                    </a>
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-red-700 hover:bg-red-600 text-white py-3 text-xs font-extrabold transition cursor-pointer shadow-lg">
                        <x-icon name="logout" class="w-4 h-4" />
                        <span>Ya, Keluar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
