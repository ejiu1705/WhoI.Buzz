@extends('layouts.app')

@section('title', 'Ubah Kategori SKPI — WhoI.Buzz')

@section('content')
    <div class="max-w-3xl mx-auto honey-card-strong rounded-[28px] p-7">
        <div class="flex items-center justify-between pb-4 border-b border-[#4C2500]/15 mb-5">
            <h1 class="text-xl font-extrabold text-[#4C2500]">Ubah Kategori: {{ $category->code }}</h1>
            <a href="{{ route('kurikulum-skpi.index') }}" class="text-xs font-bold text-[#CD7000] hover:underline">← Kembali</a>
        </div>

        <form method="POST" action="{{ route('kurikulum-skpi.update', $category) }}" class="space-y-5">
            @csrf
            @method('PUT')
            @include('kurikulum._form')
            <div class="flex gap-3 pt-2">
                <button type="submit" class="rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F2AA00] hover:text-[#FDF6DC] px-6 py-2.5 text-xs font-extrabold cursor-pointer">Perbarui</button>
                <a href="{{ route('kurikulum-skpi.index') }}" class="rounded-xl bg-[#4C2500]/10 text-[#4C2500] px-5 py-2.5 text-xs font-bold">Batal</a>
            </div>
        </form>
    </div>
@endsection
