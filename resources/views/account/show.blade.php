@extends('layouts.app')

@section('title', 'Info Akun & Pengaturan — WhoI.Buzz')

@section('content')
    <section class="espresso-card rounded-[28px] p-6 sm:p-8 mb-7 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="inline-block px-3 py-0.5 rounded-full bg-[#F2AA00] text-[#4C2500] text-[11px] font-extrabold uppercase">
                Role: {{ $user->primaryRole() }} • {{ $user->roleLabel() }}
            </span>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-[#FDF6DC]">{{ $user->name }}</h1>
            <p class="text-xs font-mono text-[#F9E7A9]/80 mt-1">{{ $user->email }} • SSO ID: {{ $user->sso_id ?? '-' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ $user->dashboardRoute() }}" class="rounded-xl bg-[#F2AA00] text-[#4C2500] px-4 py-2.5 text-xs font-extrabold">← Kembali ke Dashboard</a>
            <a href="{{ route('logout.confirm') }}" class="rounded-xl bg-red-800 hover:bg-red-700 text-white px-4 py-2.5 text-xs font-extrabold">Verifikasi Logout</a>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-7">
        {{-- Left: Update Profile Info (CRUD Update) --}}
        <section class="lg:col-span-7 honey-card-strong rounded-3xl p-6">
            <h2 class="text-lg font-extrabold text-[#4C2500]">Ubah Informasi Akun</h2>
            <p class="text-xs text-[#4C2500]/75 mb-4">Perubahan tersimpan langsung ke database terpusat MySQL.</p>

            <form method="POST" action="{{ route('account.update') }}" class="space-y-3.5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-bold text-[#4C2500] mb-1">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3.5 py-2 text-xs text-[#4C2500]">
                        @error('name') <p class="text-[11px] text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#4C2500] mb-1">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3.5 py-2 text-xs text-[#4C2500]">
                        @error('email') <p class="text-[11px] text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                @if($user->studentProfile)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-[#4C2500] mb-1">Telepon (Terenkripsi UU PDP)</label>
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
                    <span>Aktifkan indikator WebAuthn Passkey / Biometrik</span>
                </label>

                <button type="submit"
                        class="rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F2AA00] hover:text-[#FDF6DC] px-5 py-2.5 text-xs font-extrabold cursor-pointer transition">
                    Simpan Perubahan Akun
                </button>
            </form>

            {{-- Change Password --}}
            <form method="POST" action="{{ route('account.password') }}" class="mt-6 pt-5 border-t border-[#4C2500]/15 space-y-3">
                @csrf
                @method('PUT')
                <h3 class="text-sm font-extrabold text-[#4C2500]">Ubah Kata Sandi</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <input type="password" name="current_password" required placeholder="Sandi saat ini"
                           class="rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs">
                    <input type="password" name="password" required placeholder="Sandi baru (min 8)"
                           class="rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs">
                    <input type="password" name="password_confirmation" required placeholder="Konfirmasi sandi baru"
                           class="rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs">
                </div>
                <button type="submit" class="rounded-xl bg-[#CD7000] hover:bg-[#4C2500] text-[#FDF6DC] px-4 py-2 text-xs font-extrabold cursor-pointer">
                    Perbarui Kata Sandi
                </button>
            </form>
        </section>

        {{-- Right: RBAC Permissions + Sanctum Bearer Tokens + Delete Account --}}
        <aside class="lg:col-span-5 space-y-6">
            {{-- Spatie RBAC Permissions Card --}}
            <div class="honey-card rounded-3xl p-6">
                <h2 class="text-base font-extrabold text-[#4C2500]">Hak Akses RBAC Anda (Spatie)</h2>
                <p class="text-xs text-[#4C2500]/70 mt-0.5">Permission aktif yang melekat pada role <strong>{{ $user->primaryRole() }}</strong>:</p>
                <div class="mt-3 flex flex-wrap gap-1.5">
                    @forelse($permissions as $perm)
                        <span class="px-2.5 py-1 rounded-full bg-[#4C2500] text-[#F9E7A9] text-[11px] font-mono font-bold">{{ $perm }}</span>
                    @empty
                        <span class="text-xs text-[#4C2500]/65">Hanya akses standar role.</span>
                    @endforelse
                </div>
            </div>

            {{-- Sanctum Bearer Token Manager (Modul 4) --}}
            <div class="honey-card rounded-3xl p-6">
                <h2 class="text-base font-extrabold text-[#4C2500]">Bearer Token API (Laravel Sanctum)</h2>
                @if(session('new_api_token'))
                    <div class="mt-3 rounded-xl bg-[#4C2500] text-[#F2AA00] p-3 text-xs font-mono break-all border border-[#F2AA00]">
                        <p class="font-sans font-bold text-[#F9E7A9] text-[11px] mb-1">Salin Bearer Token baru Anda:</p>
                        {{ session('new_api_token') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('account.tokens.store') }}" class="mt-3 flex gap-2">
                    @csrf
                    <input type="text" name="token_name" required placeholder="Nama perangkat / Postman"
                           class="flex-1 rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs">
                    <button type="submit" class="rounded-xl bg-[#4C2500] text-[#F2AA00] px-3.5 py-2 text-xs font-extrabold cursor-pointer">+ Buat Token</button>
                </form>

                <div class="mt-3 space-y-1.5">
                    @forelse($apiTokens as $tok)
                        <div class="flex items-center justify-between rounded-xl bg-[#FDF6DC] border border-[#4C2500]/15 px-3 py-2 text-xs">
                            <div>
                                <p class="font-bold text-[#4C2500]">{{ $tok->name }}</p>
                                <p class="text-[10px] text-[#4C2500]/65">Dibuat {{ $tok->created_at?->diffForHumans() }}</p>
                            </div>
                            <form method="POST" action="{{ route('account.tokens.destroy', $tok->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[11px] font-bold text-red-700 hover:underline cursor-pointer">Cabut</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-[11px] text-[#4C2500]/60 mt-2">Belum ada Bearer Token API aktif.</p>
                    @endforelse
                </div>
            </div>

            {{-- Danger Zone: Delete Account --}}
            <div class="rounded-3xl border-2 border-red-800/35 bg-red-50/80 p-6">
                <h2 class="text-sm font-extrabold text-red-900">Hapus Akun Permanen (CRUD Delete)</h2>
                <p class="text-[11px] text-red-900/80 mt-1">Masukkan kata sandi Anda untuk mengonfirmasi penghapusan akun dari database.</p>
                <form method="POST" action="{{ route('account.destroy') }}" class="mt-3 flex gap-2" onsubmit="return confirm('Hapus akun Anda secara permanen?');">
                    @csrf
                    @method('DELETE')
                    <input type="password" name="delete_password" required placeholder="Konfirmasi kata sandi"
                           class="flex-1 rounded-xl bg-white border border-red-800/30 px-3 py-2 text-xs">
                    <button type="submit" class="rounded-xl bg-red-800 hover:bg-red-700 text-white px-4 py-2 text-xs font-extrabold cursor-pointer">Hapus Akun</button>
                </form>
            </div>
        </aside>
    </div>
@endsection
