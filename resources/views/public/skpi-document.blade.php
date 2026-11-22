<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SKPI Dwibahasa — {{ $credential->registration_number }} | WhoI.Buzz</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-[#FDF6DC] text-[#2a1704] p-4 sm:p-10">
    <div class="no-print max-w-4xl mx-auto mb-5 flex items-center justify-between gap-3">
        <a href="{{ route('verify.show', $credential->registration_number) }}" class="rounded-full bg-[#4C2500] text-[#F9E7A9] px-4 py-2 text-xs font-bold">← Kembali ke Verifikasi QR</a>
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-full bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-white px-5 py-2 text-xs font-extrabold cursor-pointer shadow transition">
            <x-icon name="printer" class="w-4 h-4" />
            <span>Cetak / Simpan PDF SKPI Resmi</span>
        </button>
    </div>

    <article class="max-w-4xl mx-auto bg-white border-2 border-[#4C2500] shadow-2xl rounded-2xl p-8 sm:p-12">
        {{-- Official Bilingual Masthead --}}
        <header class="flex items-center justify-between gap-4 border-b-4 border-double border-[#4C2500] pb-5">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/whoibuzz-logo.png') }}" alt="WHOI.BUZZ" class="h-14 w-auto object-contain">
                <div>
                    <h1 class="text-lg font-extrabold tracking-tight text-[#4C2500]">SURAT KETERANGAN PENDAMPING IJAZAH (SKPI)</h1>
                    <p class="text-xs italic font-semibold text-[#CD7000]">DIPLOMA SUPPLEMENT — BILINGUAL VERIFIED EDITION</p>
                    <p class="text-[11px] font-mono text-neutral-600 mt-0.5">Nomor Registrasi: {{ $credential->registration_number }}</p>
                </div>
            </div>
            <div class="w-24 h-24 p-1 border border-[#4C2500]/30 rounded-xl shrink-0">
                {!! $qrSvg !!}
            </div>
        </header>

        {{-- Identity Block --}}
        <section class="mt-6 grid grid-cols-2 gap-4 text-xs bg-[#FDF6DC]/70 border border-[#4C2500]/15 rounded-xl p-4">
            <div>
                <p class="text-[10px] uppercase font-bold text-[#CD7000]">Nama Lengkap / Full Name</p>
                <p class="font-extrabold text-sm text-[#4C2500]">{{ $credential->student->name }}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase font-bold text-[#CD7000]">Nomor Induk Mahasiswa / Student ID</p>
                <p class="font-mono font-extrabold text-sm text-[#4C2500]">{{ $credential->student->studentProfile->nim }}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase font-bold text-[#CD7000]">Program Studi / Study Program</p>
                <p class="font-bold">{{ $credential->student->studentProfile->prodi_name }}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase font-bold text-[#CD7000]">Fakultas / Faculty</p>
                <p class="font-bold">{{ $credential->student->studentProfile->faculty_name }}</p>
            </div>
        </section>

        {{-- Bilingual Achievements Table --}}
        <section class="mt-6">
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-[#4C2500] mb-2">
                Rekam Jejak Kompetensi Tervalidasi / Verified Competency & Achievement Record
            </h2>
            <table class="w-full text-left text-xs border-collapse border border-[#4C2500]/30">
                <thead>
                    <tr class="bg-[#4C2500] text-[#F9E7A9]">
                        <th class="p-2.5 border border-[#4C2500]/30">Capaian (Bahasa Indonesia)</th>
                        <th class="p-2.5 border border-[#4C2500]/30">Achievement (Academic English)</th>
                        <th class="p-2.5 border border-[#4C2500]/30 text-center">Trust Tier</th>
                        <th class="p-2.5 border border-[#4C2500]/30 text-center">Poin</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($credential->student->activities as $act)
                        <tr class="odd:bg-white even:bg-[#FDF6DC]/45">
                            <td class="p-2.5 border border-[#4C2500]/20">
                                <p class="font-bold text-[#4C2500]">{{ $act->title_id }}</p>
                                <p class="text-[10px] text-neutral-600">{{ $act->organizer }} • {{ $act->activity_date?->format('d M Y') }}</p>
                            </td>
                            <td class="p-2.5 border border-[#4C2500]/20 italic">
                                <p class="font-semibold">{{ $act->title_en }}</p>
                                <p class="text-[10px] text-neutral-600 not-italic">{{ $act->category?->name_en }}</p>
                            </td>
                            <td class="p-2.5 border border-[#4C2500]/20 text-center font-bold">Tier {{ $act->trust_tier }}</td>
                            <td class="p-2.5 border border-[#4C2500]/20 text-center font-mono font-bold">{{ $act->awarded_points }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        {{-- Cryptographic Signature Seal Footer --}}
        <footer class="mt-8 pt-4 border-t border-[#4C2500]/25 flex flex-col sm:flex-row sm:items-end justify-between gap-4 text-[11px]">
            <div class="space-y-0.5 font-mono text-[10px] text-neutral-600">
                <p class="font-sans font-extrabold text-[#4C2500] text-xs">Segel Tanda Tangan Digital Kriptografis</p>
                <p>Algorithm : {{ $credential->signature_algo }}</p>
                <p>Fingerprint: {{ $verification['fingerprint'] }}</p>
                <p>Status    : {{ $verification['is_authentic'] ? 'AUTHENTIC & ACTIVE' : 'REVOKED / INVALID' }}</p>
                <p>Verify URL : {{ route('verify.show', $credential->registration_number) }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-neutral-600">Diterbitkan secara elektronik pada {{ $credential->issued_at?->format('d F Y') }}</p>
                <p class="mt-4 font-extrabold text-[#4C2500]">Direktorat Kemahasiswaan & Akademik</p>
                <p class="text-[10px] text-[#CD7000]">WhoI.Buzz Campus Verified Credential Hub</p>
            </div>
        </footer>
    </article>
</body>
</html>
