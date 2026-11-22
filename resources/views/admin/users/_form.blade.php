@php
    $currentRole = old('role', $user->exists ? $user->primaryRole() : 'mahasiswa');
@endphp

<div class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-[#4C2500] mb-1">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                   class="w-full rounded-xl bg-[#FDF6DC] border @error('name') border-red-600 @else border-[#4C2500]/25 @enderror px-3.5 py-2.5 text-sm">
            @error('name') <p class="mt-1 text-xs text-red-700 font-semibold">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-[#4C2500] mb-1">Alamat Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                   class="w-full rounded-xl bg-[#FDF6DC] border @error('email') border-red-600 @else border-[#4C2500]/25 @enderror px-3.5 py-2.5 text-sm">
            @error('email') <p class="mt-1 text-xs text-red-700 font-semibold">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-[#4C2500] mb-1">Role RBAC (Spatie)</label>
            <select name="role" class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3.5 py-2.5 text-sm font-bold text-[#4C2500]">
                @foreach($roles as $r)
                    <option value="{{ $r->name }}" @selected($currentRole === $r->name)>{{ strtoupper($r->name) }}</option>
                @endforeach
            </select>
            @error('role') <p class="mt-1 text-xs text-red-700 font-semibold">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-end pb-2">
            <label class="flex items-center gap-2 text-xs font-bold text-[#4C2500] cursor-pointer">
                <input type="checkbox" name="passkey_enabled" value="1" @checked(old('passkey_enabled', $user->passkey_enabled))>
                <span>Aktifkan WebAuthn Passkey</span>
            </label>
        </div>
    </div>

    <div class="rounded-2xl bg-[#FDF6DC]/80 border border-[#CD7000]/30 p-4 space-y-3">
        <p class="text-xs font-extrabold text-[#CD7000]">Informasi Profil Mahasiswa (diisi jika Role = MAHASISWA)</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-bold text-[#4C2500] mb-1">NIM</label>
                <input type="text" name="nim" value="{{ old('nim', $user->studentProfile?->nim) }}"
                       class="w-full rounded-xl bg-white border @error('nim') border-red-600 @else border-[#4C2500]/20 @enderror px-3 py-2 text-xs font-mono">
                @error('nim') <p class="mt-1 text-[11px] text-red-700 font-semibold">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-[#4C2500] mb-1">Program Studi</label>
                <input type="text" name="prodi_name" value="{{ old('prodi_name', $user->studentProfile?->prodi_name ?? 'Sistem Informasi') }}"
                       class="w-full rounded-xl bg-white border border-[#4C2500]/20 px-3 py-2 text-xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#4C2500] mb-1">Semester</label>
                <input type="number" name="semester" value="{{ old('semester', $user->studentProfile?->semester ?? 1) }}" min="1" max="14"
                       class="w-full rounded-xl bg-white border border-[#4C2500]/20 px-3 py-2 text-xs">
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-[#4C2500] mb-1">
                Kata Sandi {{ $user->exists ? '(Kosongkan jika tidak ingin mengubah)' : '(Minimal 8 karakter)' }}
            </label>
            <input type="password" name="password" @required(!$user->exists)
                   class="w-full rounded-xl bg-[#FDF6DC] border @error('password') border-red-600 @else border-[#4C2500]/25 @enderror px-3.5 py-2.5 text-sm">
            @error('password') <p class="mt-1 text-xs text-red-700 font-semibold">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-bold text-[#4C2500] mb-1">Konfirmasi Kata Sandi</label>
            <input type="password" name="password_confirmation" @required(!$user->exists)
                   class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3.5 py-2.5 text-sm">
        </div>
    </div>
</div>
