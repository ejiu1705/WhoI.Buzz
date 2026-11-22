@extends('layouts.app')

@section('title', 'Masuk ke Akun — WhoI.Buzz')

@section('content')
    <div class="max-w-md mx-auto my-4">
        <div class="honey-card-strong rounded-[30px] p-7 sm:p-9 shadow-2xl">
            {{-- Brand Masthead --}}
            <div class="text-center pb-6 border-b border-[#4C2500]/15">
                <a href="{{ route('home') }}" class="inline-block rounded-2xl bg-[#FDF6DC] px-5 py-3 border border-[#F2AA00] shadow-inner">
                    <img src="{{ asset('images/whoibuzz-logo.png') }}" alt="WHOI.BUZZ" class="h-12 w-auto object-contain mx-auto">
                </a>
                <h1 class="mt-4 text-2xl font-extrabold text-[#4C2500]">Masuk ke WhoI.Buzz</h1>
                <p class="mt-1 text-xs text-[#4C2500]/75">
                    Anda akan diarahkan otomatis ke dashboard sesuai peran akun Anda.
                </p>
            </div>

            {{-- Clean Login Form --}}
            <form method="POST" action="{{ route('login.post') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-bold text-[#4C2500] mb-1.5">Alamat Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="nama@campus.ac.id"
                           class="w-full rounded-xl bg-[#FDF6DC] border @error('email') border-red-600 @else border-[#4C2500]/25 @enderror px-4 py-2.5 text-sm text-[#4C2500] focus:outline-none focus:border-[#CD7000]">
                    @error('email')
                        <p class="mt-1 text-xs font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-[#4C2500] mb-1.5">Kata Sandi</label>
                    <input type="password" id="password" name="password" required
                           placeholder="Masukkan kata sandi"
                           class="w-full rounded-xl bg-[#FDF6DC] border @error('password') border-red-600 @else border-[#4C2500]/25 @enderror px-4 py-2.5 text-sm text-[#4C2500] focus:outline-none focus:border-[#CD7000]">
                    @error('password')
                        <p class="mt-1 text-xs font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-xs text-[#4C2500]/80 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-[#4C2500]/30 text-[#CD7000]">
                    <span>Ingat sesi saya di perangkat ini</span>
                </label>

                <button type="submit"
                        class="w-full rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F2AA00] hover:text-[#FDF6DC] py-3 text-sm font-extrabold transition cursor-pointer shadow-md">
                    Masuk →
                </button>
            </form>

            {{-- Footer Links --}}
            <div class="mt-6 pt-5 border-t border-[#4C2500]/15 flex items-center justify-between text-xs">
                <a href="{{ route('home') }}" class="font-semibold text-[#4C2500]/75 hover:text-[#4C2500]">← Beranda</a>
                <a href="{{ route('register') }}" class="font-extrabold text-[#CD7000] hover:text-[#4C2500] underline">
                    Belum punya akun? Registrasi
                </a>
            </div>

            {{-- Subtle Collapsible Reference for Local Development Accounts --}}
            <details class="mt-5 rounded-2xl bg-[#4C2500]/6 border border-[#4C2500]/12 px-4 py-3 text-xs">
                <summary class="cursor-pointer font-bold text-[#4C2500]/80 select-none">
                    Daftar Akun Uji Lokal (Admin / Dosen / Mahasiswa / Mitra)
                </summary>
                <div class="mt-3 space-y-2 border-t border-[#4C2500]/10 pt-3">
                    <p class="text-[11px] text-[#4C2500]/70">Sandi standar: <code class="font-mono font-bold">password123</code></p>
                    @foreach($demoAccounts as $acc)
                        <div class="flex items-center justify-between gap-2 rounded-xl bg-[#FDF6DC] px-3 py-2 border border-[#4C2500]/10">
                            <div class="min-w-0">
                                <span class="text-[10px] font-extrabold uppercase text-[#CD7000]">{{ $acc->primaryRole() }}</span>
                                <p class="font-mono text-[11px] text-[#4C2500] truncate">{{ $acc->email }}</p>
                            </div>
                            <button type="button"
                                    onclick="document.getElementById('email').value='{{ $acc->email }}'; document.getElementById('password').value='password123';"
                                    class="shrink-0 rounded-lg bg-[#4C2500] hover:bg-[#CD7000] text-[#F9E7A9] px-2.5 py-1 text-[10px] font-bold cursor-pointer">
                                Isi Form
                            </button>
                        </div>
                    @endforeach
                </div>
            </details>
        </div>
    </div>
@endsection
