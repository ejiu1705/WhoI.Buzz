@extends('layouts.app')

@section('title', 'Registrasi Akun — WhoI.Buzz')

@section('content')
    <div class="max-w-2xl mx-auto honey-card-strong rounded-[28px] p-7 sm:p-9">
        <div class="flex items-center justify-between gap-4 pb-5 border-b border-[#4C2500]/15">
            <div class="flex items-center gap-3.5">
                <div class="rounded-2xl bg-[#FDF6DC] p-2 border border-[#F2AA00]">
                    <img src="{{ asset('images/whoibuzz-logo.png') }}" alt="WHOI.BUZZ" class="h-11 w-auto object-contain">
                </div>
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#CD7000]">Pendaftaran Akun</span>
                    <h1 class="text-xl font-extrabold text-[#4C2500]">Daftar Akun WhoI.Buzz</h1>
                </div>
            </div>
            <a href="{{ route('login') }}" class="text-xs font-bold text-[#CD7000] hover:underline">Sudah punya akun? Masuk →</a>
        </div>

        <form method="POST" action="{{ route('register.post') }}" class="mt-6 space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#4C2500] mb-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="Nama sesuai KTM atau instansi"
                           class="w-full rounded-xl bg-[#FDF6DC] border @error('name') border-red-600 @else border-[#4C2500]/25 @enderror px-3.5 py-2.5 text-sm text-[#4C2500]">
                    @error('name') <p class="mt-1 text-xs text-red-700 font-semibold">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#4C2500] mb-1">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="nama@campus.ac.id"
                           class="w-full rounded-xl bg-[#FDF6DC] border @error('email') border-red-600 @else border-[#4C2500]/25 @enderror px-3.5 py-2.5 text-sm text-[#4C2500]">
                    @error('email') <p class="mt-1 text-xs text-red-700 font-semibold">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#4C2500] mb-1">Jenis Akun</label>
                <select name="role" id="register-role-select" required
                        class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3.5 py-2.5 text-sm font-bold text-[#4C2500]">
                    <option value="mahasiswa" @selected(old('role', 'mahasiswa') === 'mahasiswa')>Mahasiswa — Portofolio, Pengajuan Sertifikat &amp; SKPI</option>
                    <option value="mitra" @selected(old('role') === 'mitra')>Mitra Karier / HRD — Direktori Talenta &amp; Verifikasi Kredensial</option>
                </select>
                <p class="mt-1 text-[11px] text-[#4C2500]/80">Akun <strong>Admin</strong> dan <strong>Dosen PA</strong> didaftarkan langsung oleh pengelola kemahasiswaan.</p>
            </div>

            <div id="mahasiswa-fields" class="rounded-2xl bg-[#FDF6DC]/90 border border-[#CD7000]/30 p-4 space-y-3">
                <p class="text-xs font-extrabold text-[#CD7000]">Informasi Akademik Mahasiswa</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-[#4C2500] mb-1">NIM</label>
                        <input type="text" name="nim" value="{{ old('nim') }}" placeholder="240411100001"
                               class="w-full rounded-xl bg-white border @error('nim') border-red-600 @else border-[#4C2500]/20 @enderror px-3 py-2 text-xs font-mono">
                        @error('nim') <p class="mt-1 text-[11px] text-red-700 font-semibold">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#4C2500] mb-1">Program Studi</label>
                        <input type="text" name="prodi_name" value="{{ old('prodi_name', 'Sistem Informasi') }}"
                               class="w-full rounded-xl bg-white border @error('prodi_name') border-red-600 @else border-[#4C2500]/20 @enderror px-3 py-2 text-xs">
                        @error('prodi_name') <p class="mt-1 text-[11px] text-red-700 font-semibold">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#4C2500] mb-1">Semester</label>
                        <input type="number" name="semester" value="{{ old('semester', 3) }}" min="1" max="14"
                               class="w-full rounded-xl bg-white border border-[#4C2500]/20 px-3 py-2 text-xs">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#4C2500] mb-1">Kata Sandi (Minimal 8 Karakter)</label>
                    <input type="password" name="password" required
                           class="w-full rounded-xl bg-[#FDF6DC] border @error('password') border-red-600 @else border-[#4C2500]/25 @enderror px-3.5 py-2.5 text-sm">
                    @error('password') <p class="mt-1 text-xs text-red-700 font-semibold">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#4C2500] mb-1">Konfirmasi Kata Sandi</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3.5 py-2.5 text-sm">
                </div>
            </div>

            <button type="submit"
                    class="w-full rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F2AA00] hover:text-[#FDF6DC] py-3 text-sm font-extrabold transition cursor-pointer shadow-md">
                Daftar &amp; Masuk ke Dashboard →
            </button>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const sel = document.getElementById('register-role-select');
        const box = document.getElementById('mahasiswa-fields');
        if (!sel || !box) return;
        const sync = () => box.classList.toggle('hidden', sel.value !== 'mahasiswa');
        sel.addEventListener('change', sync);
        sync();
    })();
</script>
@endpush
