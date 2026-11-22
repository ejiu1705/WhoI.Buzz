@extends('layouts.app')

@section('title', 'Info Akun — WhoI.Buzz')

@section('content')
    <section class="espresso-card rounded-[28px] p-6 sm:p-8 mb-7 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="inline-block px-3 py-0.5 rounded-full bg-[#F2AA00] text-[#4C2500] text-[11px] font-extrabold uppercase">
                {{ $user->roleLabel() }}
            </span>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-[#FDF6DC]">{{ $user->name }}</h1>
            <p class="text-xs font-mono text-[#F9E7A9]/90 mt-1">{{ $user->email }} • ID: {{ $user->sso_id ?? '-' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ $user->dashboardRoute() }}" class="rounded-xl bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-[#FDF6DC] px-4 py-2.5 text-xs font-extrabold transition">← Kembali ke Dashboard</a>
            <a href="{{ route('logout.confirm') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-red-800 hover:bg-red-700 text-white px-4 py-2.5 text-xs font-extrabold transition">
                <x-icon name="logout" class="w-4 h-4" />
                <span>Keluar</span>
            </a>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-7">
        {{-- Kiri: Edit Profil & Kata Sandi --}}
        <section class="lg:col-span-7 honey-card-strong rounded-3xl p-6">
            <h2 class="text-lg font-extrabold text-[#4C2500]">Pengaturan Profil Akun</h2>
            <p class="text-xs text-[#4C2500]/80 mb-4">Perbarui identitas akun dan informasi dasar Anda di bawah ini.</p>

            <form method="POST" action="{{ route('account.update') }}" class="space-y-3.5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-bold text-[#4C2500] mb-1">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3.5 py-2 text-xs text-[#4C2500]">
                        @error('name') <p class="text-[11px] text-red-700 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#4C2500] mb-1">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3.5 py-2 text-xs text-[#4C2500]">
                        @error('email') <p class="text-[11px] text-red-700 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>
                </div>

                @if($user->studentProfile)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-[#4C2500] mb-1">Nomor Telepon (Terenkripsi)</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->studentProfile->phone) }}"
                                   class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3.5 py-2 text-xs text-[#4C2500]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#4C2500] mb-1">Headline Portofolio</label>
                            <input type="text" name="headline" value="{{ old('headline', $user->studentProfile->headline) }}"
                                   class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3.5 py-2 text-xs text-[#4C2500]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#4C2500] mb-1">Bio Singkat</label>
                        <textarea name="bio" rows="2" class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3.5 py-2 text-xs text-[#4C2500]">{{ old('bio', $user->studentProfile->bio) }}</textarea>
                    </div>
                @endif

                <label class="flex items-center gap-2 text-xs font-bold text-[#4C2500] cursor-pointer">
                    <input type="checkbox" name="passkey_enabled" value="1" @checked(old('passkey_enabled', $user->passkey_enabled))>
                    <span>Aktifkan verifikasi Passkey / Biometrik perangkat</span>
                </label>

                <button type="submit"
                        class="rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F2AA00] hover:text-[#FDF6DC] px-5 py-2.5 text-xs font-extrabold cursor-pointer transition">
                    Simpan Perubahan
                </button>
            </form>

            <form method="POST" action="{{ route('account.password') }}" class="mt-6 pt-5 border-t border-[#4C2500]/15 space-y-3">
                @csrf
                @method('PUT')
                <h3 class="text-sm font-extrabold text-[#4C2500]">Ganti Kata Sandi</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <input type="password" name="current_password" required placeholder="Sandi saat ini"
                           class="rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                    <input type="password" name="password" required placeholder="Sandi baru (min 8)"
                           class="rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                    <input type="password" name="password_confirmation" required placeholder="Ulangi sandi baru"
                           class="rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                </div>
                <button type="submit" class="rounded-xl bg-[#CD7000] hover:bg-[#4C2500] text-[#FDF6DC] px-4 py-2 text-xs font-extrabold cursor-pointer transition">
                    Perbarui Kata Sandi
                </button>
            </form>
        </section>

        {{-- Kanan: Ringkasan Hak Akses + Token API (Khusus Admin) + Hapus Akun --}}
        <aside class="lg:col-span-5 space-y-6">
            <div class="honey-card rounded-3xl p-6">
                <h2 class="text-base font-extrabold text-[#4C2500]">Hak Akses Akun Anda</h2>
                <p class="text-xs text-[#4C2500]/80 mt-0.5">Kewenangan aktif untuk peran <strong>{{ $user->roleLabel() }}</strong>:</p>
                <div class="mt-3 flex flex-wrap gap-1.5">
                    @forelse($permissions as $perm)
                        <span class="px-2.5 py-1 rounded-full bg-[#4C2500] text-[#FDF6DC] text-[11px] font-semibold">{{ $perm }}</span>
                    @empty
                        <span class="text-xs text-[#4C2500]/80">Akses standar pengguna.</span>
                    @endforelse
                </div>
            </div>

            {{-- Token Integrasi API hanya ditampilkan bagi Admin --}}
            @role('admin')
                <div class="honey-card rounded-3xl p-6">
                    <h2 class="text-base font-extrabold text-[#4C2500]">Token Akses Integrasi (Khusus Admin)</h2>
                    <p class="text-xs text-[#4C2500]/80 mt-0.5">Digunakan untuk sinkronisasi sistem internal dan pengujian melalui Postman.</p>

                    @if(session('new_api_token'))
                        <div class="mt-3 rounded-xl bg-[#4C2500] text-[#F2AA00] p-3 text-xs font-mono break-all border border-[#F2AA00]">
                            <p class="font-sans font-bold text-[#FDF6DC] text-[11px] mb-1">Salin token berikut (hanya ditampilkan satu kali):</p>
                            {{ session('new_api_token') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('account.tokens.store') }}" class="mt-3 flex gap-2">
                        @csrf
                        <input type="text" name="token_name" required placeholder="Label token (cth: Postman SIAKAD)"
                               class="flex-1 rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                        <button type="submit" class="rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F2AA00] hover:text-[#FDF6DC] px-3.5 py-2 text-xs font-extrabold cursor-pointer transition">+ Buat</button>
                    </form>

                    <div class="mt-3 space-y-1.5">
                        @forelse($apiTokens as $tok)
                            <div class="flex items-center justify-between rounded-xl bg-[#FDF6DC] border border-[#4C2500]/15 px-3 py-2 text-xs">
                                <div>
                                    <p class="font-bold text-[#4C2500]">{{ $tok->name }}</p>
                                    <p class="text-[11px] text-[#4C2500]/80">Dibuat {{ $tok->created_at?->diffForHumans() }}</p>
                                </div>
                                <form method="POST" action="{{ route('account.tokens.destroy', $tok->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-[11px] font-bold text-red-700 hover:underline cursor-pointer">Cabut</button>
                                </form>
                            </div>
                        @empty
                            <p class="text-xs text-[#4C2500]/80 mt-2">Belum ada token integrasi yang aktif.</p>
                        @endforelse
                    </div>
                </div>
            @endrole

            <div class="rounded-3xl border-2 border-red-800/35 bg-red-50/90 p-6">
                <h2 class="text-sm font-extrabold text-red-900">Hapus Akun</h2>
                <p class="text-xs text-red-900/85 mt-1">Tindakan ini menghapus akun Anda secara permanen. Masukkan kata sandi untuk melanjutkan.</p>
                <form method="POST" action="{{ route('account.destroy') }}" class="mt-3 flex gap-2" onsubmit="return confirm('Hapus akun Anda secara permanen?');">
                    @csrf
                    @method('DELETE')
                    <input type="password" name="delete_password" required placeholder="Konfirmasi kata sandi"
                           class="flex-1 rounded-xl bg-white border border-red-800/30 px-3 py-2 text-xs text-[#4C2500]">
                    <button type="submit" class="rounded-xl bg-red-800 hover:bg-red-700 text-white px-4 py-2 text-xs font-extrabold cursor-pointer transition">Hapus Akun</button>
                </form>
            </div>
        </aside>
    </div>
@endsection
