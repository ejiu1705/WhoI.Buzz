@extends('layouts.app')

@section('title', 'Detail Akun — Admin WhoI.Buzz')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="espresso-card rounded-[28px] p-7 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="px-2.5 py-0.5 rounded-full bg-[#F2AA00] text-[#4C2500] text-[11px] font-extrabold uppercase">Role: {{ $user->primaryRole() }}</span>
                <h1 class="mt-2 text-2xl font-extrabold text-[#FDF6DC]">{{ $user->name }}</h1>
                <p class="text-xs font-mono text-[#F9E7A9]/80">{{ $user->email }} • SSO: {{ $user->sso_id ?? '-' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.users.edit', $user) }}" class="rounded-xl bg-[#F2AA00] text-[#4C2500] px-4 py-2 text-xs font-extrabold">Ubah Akun</a>
                <a href="{{ route('admin.users.index') }}" class="rounded-xl bg-[#FDF6DC]/15 text-[#FDF6DC] px-4 py-2 text-xs font-bold">← Kembali</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="honey-card rounded-3xl p-6 text-xs space-y-2">
                <h2 class="text-sm font-extrabold text-[#4C2500]">Metadata & Hak Akses</h2>
                <p><strong>ULID:</strong> <span class="font-mono">{{ $user->id }}</span></p>
                <p><strong>Passkey:</strong> {{ $user->passkey_enabled ? 'Aktif' : 'Nonaktif' }}</p>
                <p><strong>Terdaftar:</strong> {{ $user->created_at?->format('d M Y H:i') }}</p>
                <div class="pt-2 flex flex-wrap gap-1.5">
                    @foreach($user->getAllPermissions() as $p)
                        <span class="px-2 py-0.5 rounded-full bg-[#4C2500] text-[#F9E7A9] font-mono text-[10px]">{{ $p->name }}</span>
                    @endforeach
                </div>
            </div>

            <div class="honey-card rounded-3xl p-6 text-xs space-y-2">
                <h2 class="text-sm font-extrabold text-[#4C2500]">Profil Akademik & Kredensial</h2>
                @if($user->studentProfile)
                    <p><strong>NIM:</strong> <span class="font-mono font-bold">{{ $user->studentProfile->nim }}</span></p>
                    <p><strong>Prodi:</strong> {{ $user->studentProfile->prodi_name }} (Semester {{ $user->studentProfile->semester }})</p>
                    <p><strong>Dosen PA:</strong> {{ $user->studentProfile->advisor?->name ?? '-' }}</p>
                    <p><strong>Portofolio Publik:</strong> {{ $user->studentProfile->is_public_enabled ? 'Aktif (@'.$user->studentProfile->public_slug.')' : 'Privat (UU PDP)' }}</p>
                @else
                    <p class="text-[#4C2500]/70">Bukan akun mahasiswa (tidak memiliki profil SKPI).</p>
                @endif
                <p><strong>Total Capaian:</strong> {{ $user->activities->count() }} • <strong>Kredensial Terbit:</strong> {{ $user->issuedCredentials->count() }}</p>
            </div>
        </div>
    </div>
@endsection
