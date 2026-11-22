<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ATS CV — {{ $student->name }} | WhoI.Buzz Verified</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-[#FDF6DC] text-[#1f160c] p-4 sm:p-10">
    <div class="no-print max-w-4xl mx-auto mb-5 flex items-center justify-between gap-3">
        <a href="{{ route('mahasiswa.dashboard') }}" class="rounded-full bg-[#4C2500] text-[#F9E7A9] px-4 py-2 text-xs font-bold">← Kembali ke Dashboard</a>
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-full bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-white px-5 py-2 text-xs font-extrabold cursor-pointer shadow transition">
            <x-icon name="printer" class="w-4 h-4" />
            <span>Cetak / Simpan sebagai ATS PDF</span>
        </button>
    </div>

    <article class="max-w-4xl mx-auto bg-white border border-[#4C2500]/20 shadow-xl rounded-2xl p-8 sm:p-12">
        {{-- ATS Clean Header --}}
        <header class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 border-b-2 border-[#4C2500] pb-6">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-[#4C2500]">{{ strtoupper($student->name) }}</h1>
                <p class="text-sm font-bold text-[#CD7000] mt-1">{{ $profile->headline }}</p>
                <div class="mt-3 text-xs text-neutral-700 space-y-0.5">
                    <p><strong>Email:</strong> {{ $student->email }} | <strong>NIM:</strong> {{ $profile->nim }}</p>
                    <p><strong>Program:</strong> {{ $profile->prodi_name }}, {{ $profile->faculty_name }} (Semester {{ $profile->semester }})</p>
                    <p><strong>Verified Hub URL:</strong> {{ $verifyUrl }}</p>
                </div>
            </div>
            <div class="flex flex-col items-center shrink-0">
                <div class="w-24 h-24 p-1.5 border border-[#4C2500]/25 rounded-xl bg-white">
                    {!! $qrSvg !!}
                </div>
                <span class="mt-1 text-[9px] font-mono font-bold text-[#4C2500]">SCAN TO VERIFY</span>
            </div>
        </header>

        {{-- Professional Summary --}}
        <section class="mt-6">
            <h2 class="text-xs font-extrabold uppercase tracking-widest text-[#4C2500] border-b border-neutral-300 pb-1">Professional Summary</h2>
            <p class="mt-2 text-xs leading-relaxed text-neutral-800">{{ $profile->bio }}</p>
        </section>

        {{-- Core Competencies --}}
        <section class="mt-6">
            <h2 class="text-xs font-extrabold uppercase tracking-widest text-[#4C2500] border-b border-neutral-300 pb-1">Verified Core Skills</h2>
            <p class="mt-2 text-xs text-neutral-800 leading-relaxed">
                {{ implode(' • ', $profile->skills_json ?? []) }}
            </p>
        </section>

        {{-- Verified Credentials & Achievements --}}
        <section class="mt-6">
            <h2 class="text-xs font-extrabold uppercase tracking-widest text-[#4C2500] border-b border-neutral-300 pb-1">
                Campus-Verified Achievements & Certifications ({{ $activities->sum('awarded_points') }} SKPI Points)
            </h2>
            <div class="mt-3 space-y-3.5">
                @foreach($activities as $act)
                    <div>
                        <div class="flex items-baseline justify-between gap-2">
                            <h3 class="text-xs font-extrabold text-neutral-900">{{ $act->title_en }}</h3>
                            <span class="text-[11px] font-mono text-neutral-600 shrink-0">{{ $act->activity_date?->format('M Y') }}</span>
                        </div>
                        <p class="text-xs text-neutral-700">
                            {{ $act->organizer }} — <strong>{{ $act->role_in_activity }}</strong>
                            | Trust Level: <strong>Tier {{ $act->trust_tier }}</strong>
                            @if($act->credential)
                                | Credential ID: <span class="font-mono">{{ $act->credential->registration_number }}</span>
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>
        </section>

        <footer class="mt-8 pt-4 border-t border-neutral-300 flex items-center justify-between text-[10px] text-neutral-700">
            <span>Dokumen dihasilkan otomatis dari portal resmi WhoI.Buzz (Campus Verified Credential Hub)</span>
            @if($latestSkpi)
                <span class="font-mono font-bold text-[#4C2500]">SKPI Seal: {{ $latestSkpi->registration_number }}</span>
            @endif
        </footer>
    </article>
</body>
</html>
