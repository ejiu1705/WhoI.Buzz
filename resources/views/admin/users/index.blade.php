@extends('layouts.app')

@section('title', 'Kelola Pengguna — WhoI.Buzz Admin')

@section('content')
    <section class="espresso-card rounded-[28px] p-6 sm:p-7 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="inline-block px-3 py-0.5 rounded-full bg-[#F2AA00] text-[#4C2500] text-[11px] font-extrabold">
                Administrasi Akun
            </span>
            <h1 class="mt-2 text-2xl font-extrabold text-[#FDF6DC]">Manajemen Akun Pengguna</h1>
            <p class="text-xs text-[#F9E7A9]/90 mt-0.5">Kelola data akun Admin, Dosen PA, Mahasiswa, dan Mitra Karier beserta peran aksesnya.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.dashboard') }}" class="rounded-xl bg-[#FDF6DC]/15 text-[#FDF6DC] px-4 py-2.5 text-xs font-bold">← Dashboard Admin</a>
            <a href="{{ route('admin.users.create') }}" class="rounded-xl bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-[#FDF6DC] px-4 py-2.5 text-xs font-extrabold transition shadow">
                + Tambah Akun Baru
            </a>
        </div>
    </section>

    {{-- Search & Role Filter (Modul 3: GET + when() + withQueryString()) --}}
    <section class="honey-card rounded-3xl p-5 mb-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3">
            <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nama, email, atau SSO ID..."
                   class="md:col-span-6 rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3.5 py-2 text-xs text-[#4C2500]">
            <select name="role" class="md:col-span-4 rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3.5 py-2 text-xs font-bold text-[#4C2500]">
                <option value="">Semua Role RBAC</option>
                @foreach($roles as $r)
                    <option value="{{ $r->name }}" @selected(request('role') === $r->name)>Role: {{ ucfirst($r->name) }}</option>
                @endforeach
            </select>
            <button type="submit" class="md:col-span-2 rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F9E7A9] text-xs font-extrabold py-2 cursor-pointer transition">
                Filter Data
            </button>
        </form>
    </section>

    {{-- Users Table --}}
    <section class="honey-card rounded-3xl p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#4C2500]/15 text-[#CD7000] uppercase text-[10px]">
                        <th class="py-2.5 pr-3">No</th>
                        <th class="py-2.5 pr-3">Nama & Email</th>
                        <th class="py-2.5 pr-3">Role Spatie</th>
                        <th class="py-2.5 pr-3">NIM / Prodi</th>
                        <th class="py-2.5 pr-3 text-center">Capaian</th>
                        <th class="py-2.5">Aksi CRUD</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#4C2500]/10">
                    @forelse($users as $u)
                        <tr>
                            <td class="py-3 pr-3 font-mono">{{ ($users->firstItem() ?? 1) + $loop->index }}</td>
                            <td class="py-3 pr-3">
                                <p class="font-extrabold text-[#4C2500]">{{ $u->name }}</p>
                                <p class="font-mono text-[11px] text-[#4C2500]/70">{{ $u->email }}</p>
                            </td>
                            <td class="py-3 pr-3 whitespace-nowrap">
                                <span class="inline-flex items-center whitespace-nowrap leading-none px-2.5 py-1 rounded-full bg-[#4C2500] text-[#F2AA00] text-[10px] font-extrabold uppercase">
                                    {{ $u->primaryRole() }}
                                </span>
                            </td>
                            <td class="py-3 pr-3 text-[11px]">
                                @if($u->studentProfile)
                                    <span class="font-mono font-bold">{{ $u->studentProfile->nim }}</span>
                                    <span class="block text-[#4C2500]/70">{{ $u->studentProfile->prodi_name }} (Smt {{ $u->studentProfile->semester }})</span>
                                @else
                                    <span class="text-[#4C2500]/75">—</span>
                                @endif
                            </td>
                            <td class="py-3 pr-3 text-center font-mono font-bold">{{ $u->activities_count }}</td>
                            <td class="py-3">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <a href="{{ route('admin.users.show', $u) }}" class="px-2.5 py-1 rounded-lg bg-[#F9E7A9] text-[#4C2500] font-bold">Detail</a>
                                    <a href="{{ route('admin.users.edit', $u) }}" class="px-2.5 py-1 rounded-lg bg-[#F2AA00] text-[#4C2500] font-extrabold">Ubah</a>
                                    @if($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Hapus akun {{ $u->email }} secara permanen?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-red-800 hover:bg-red-700 text-white font-bold cursor-pointer">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-[#4C2500]/80">Belum ada akun yang cocok dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </section>
@endsection
