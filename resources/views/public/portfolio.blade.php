@extends('layouts.app')

@section('title', $profile->user->name . ' (@' . $profile->public_slug . ') — WhoI.Buzz E-Portfolio')

@section('content')
    {{-- PROFILE HERO --}}
    <section class="espresso-card rounded-[30px] p-7 sm:p-10 mb-7">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2.5 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="px-3 py-1 rounded-full bg-[#F2AA00] text-[#4C2500] font-extrabold font-mono">{{ '@' . $profile->public_slug }}</span>
                    <span class="px-3 py-1 rounded-full bg-[#F9E7A9]/15 text-[#F9E7A9] font-semibold">{{ $profile->prodi_name }} • Semester {{ $profile->semester }}</span>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-500/25 text-emerald-200 font-bold">
                        <x-icon name="shield-check" class="w-3.5 h-3.5" />
                        <span>Campus Verified Portfolio</span>
                    </span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-[#FDF6DC]">{{ $profile->user->name }}</h1>
                <p class="text-sm font-bold text-[#F2AA00]">{{ $profile->headline }}</p>
                <p class="text-xs sm:text-sm text-[#F9E7A9]/85 leading-relaxed">{{ $profile->bio }}</p>

                <div class="flex flex-wrap gap-1.5 pt-2">
                    @foreach($profile->skills_json ?? [] as $skill)
                        <span class="px-3 py-1 rounded-full bg-[#FDF6DC]/12 border border-[#F2AA00]/35 text-[#F9E7A9] text-xs font-semibold">{{ $skill }}</span>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col items-center rounded-3xl bg-[#FDF6DC] text-[#4C2500] p-4 border-2 border-[#F2AA00] shrink-0">
                <div class="w-28 h-28">
                    {!! $qrSvg !!}
                </div>
                <p class="mt-2 text-[10px] font-mono font-extrabold text-[#4C2500]">SCAN VERIFIED PROFILE</p>
                <p class="text-xs font-extrabold text-[#CD7000] mt-0.5">{{ $approvedPoints }} Poin SKPI Sah</p>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Left: Skill Radar + Official SKPI Badges --}}
        <aside class="lg:col-span-4 space-y-6">
            <div class="honey-card-strong rounded-3xl p-6">
                <h2 class="text-base font-extrabold text-[#4C2500]">Verified Skill Radar</h2>
                <p class="text-xs text-[#4C2500]/70 mb-2">Dihitung otomatis dari capaian tervalidasi kampus.</p>
                <x-skill-radar :scores="$skillRadar" />
            </div>

            @if($profile->user->issuedCredentials->where('type', 'skpi')->isNotEmpty())
                <div class="honey-card rounded-3xl p-6">
                    <h2 class="text-sm font-extrabold text-[#4C2500] mb-3">Dokumen SKPI Resmi</h2>
                    @foreach($profile->user->issuedCredentials->where('type', 'skpi') as $skpi)
                        <div class="rounded-2xl bg-[#4C2500] text-[#F9E7A9] p-4 space-y-2 text-xs">
                            <p class="font-mono font-bold text-[#F2AA00]">{{ $skpi->registration_number }}</p>
                            <p class="text-[11px] text-[#F9E7A9]/80">Ditandatangani via {{ $skpi->signature_algo }}</p>
                            <div class="flex gap-2 pt-1">
                                <a href="{{ route('verify.show', $skpi->registration_number) }}" class="px-3 py-1.5 rounded-lg bg-[#F2AA00] text-[#4C2500] font-extrabold">Verifikasi QR</a>
                                <a href="{{ route('credentials.document', $skpi->registration_number) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-[#FDF6DC]/15 text-[#FDF6DC] font-bold">Lihat SKPI</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </aside>

        {{-- Right: Verified Public Credentials Stream --}}
        <section class="lg:col-span-8 honey-card rounded-3xl p-6">
            <div class="flex items-center justify-between gap-2 pb-4 border-b border-[#4C2500]/15">
                <div>
                    <h2 class="text-lg font-extrabold text-[#4C2500]">Kredensial & Capaian Tervalidasi</h2>
                    <p class="text-xs text-[#4C2500]/70">Setiap item dilengkapi label 3-Tier Trust Model & verifikasi kriptografi langsung.</p>
                </div>
                <span class="px-3 py-1 rounded-full bg-[#F9E7A9] text-[#4C2500] text-xs font-extrabold">{{ $profile->user->activities->count() }} Item</span>
            </div>

            <div class="mt-4 space-y-4">
                @foreach($profile->user->activities as $act)
                    <div class="rounded-2xl bg-[#FDF6DC] border border-[#4C2500]/15 p-5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <x-trust-badge :tier="$act->trust_tier" />
                            <span class="text-xs font-mono font-bold text-[#CD7000]">{{ $act->activity_date?->format('d M Y') }} • +{{ $act->awarded_points }} Poin</span>
                        </div>
                        <h3 class="mt-2.5 text-base font-extrabold text-[#4C2500]">{{ $act->title_id }}</h3>
                        <p class="text-xs italic text-[#4C2500]/75">{{ $act->title_en }}</p>
                        <p class="mt-1.5 text-xs text-[#4C2500]/80">
                            <strong>{{ $act->organizer }}</strong> — {{ $act->role_in_activity }} ({{ $act->category?->competency_domain }})
                        </p>
                        <p class="mt-1.5 text-[11px] text-[#4C2500]/65">{{ $act->trustTierMeta()['description'] }}</p>

                        <div class="mt-3 pt-3 border-t border-[#4C2500]/10 flex flex-wrap items-center gap-2">
                            @if($act->credential)
                                <a href="{{ route('verify.show', $act->credential->registration_number) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F9E7A9] px-3.5 py-1.5 text-xs font-extrabold transition">
                                    <x-icon name="shield-check" class="w-3.5 h-3.5" />
                                    <span>Verifikasi {{ $act->credential->registration_number }}</span>
                                </a>
                            @endif
                            @if($act->external_verify_url)
                                <a href="{{ $act->external_verify_url }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-[#F9E7A9] text-[#4C2500] px-3 py-1.5 text-xs font-bold">
                                    <x-icon name="link" class="w-3.5 h-3.5" />
                                    <span>Sumber Verifikasi Eksternal</span>
                                </a>
                            @endif
                            <a href="{{ $act->linkedInAddUrl() }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 rounded-xl bg-[#0A66C2] text-white px-3 py-1.5 text-xs font-bold">
                                <x-icon name="link" class="w-3.5 h-3.5" />
                                <span>Add to LinkedIn</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
