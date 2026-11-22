@extends('layouts.app')

@section('title', 'Profil Privat (UU PDP) — WhoI.Buzz')

@section('content')
    <div class="max-w-xl mx-auto honey-card-strong rounded-[28px] p-8 text-center my-8">
        <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-[#4C2500] text-[#F2AA00] mx-auto">
            <x-icon name="lock" class="w-7 h-7" />
        </div>
        <h1 class="mt-4 text-2xl font-extrabold text-[#4C2500]">Profil Publik Dinonaktifkan</h1>
        <p class="mt-2 text-xs text-[#4C2500]/80 leading-relaxed">
            Sesuai prinsip <strong>Privacy-by-Default (UU Pelindungan Data Pribadi / UU PDP)</strong> di WhoI.Buzz, halaman portofolio
            <code class="font-mono font-bold">{{ '@' . $profile->public_slug }}</code> bersifat privat secara bawaan dan belum diaktifkan oleh pemiliknya.
        </p>
        <div class="mt-6 flex flex-wrap justify-center gap-2.5">
            <a href="{{ route('talent.index') }}" class="rounded-xl bg-[#4C2500] text-[#F9E7A9] px-5 py-2.5 text-xs font-extrabold">
                Lihat Talent Pool Publik →
            </a>
            <a href="{{ route('home') }}" class="rounded-xl bg-[#FDF6DC] border border-[#4C2500]/20 text-[#4C2500] px-5 py-2.5 text-xs font-bold">
                Kembali ke Beranda
            </a>
        </div>
    </div>
@endsection
