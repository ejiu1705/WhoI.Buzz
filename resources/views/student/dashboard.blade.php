@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa — WhoI.Buzz')

@section('content')
    {{-- STUDENT HEADER & QUICK ACTIONS --}}
    <section class="espresso-card rounded-[28px] p-6 sm:p-8 mb-7">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="px-2.5 py-0.5 rounded-full bg-[#F2AA00] text-[#4C2500] font-extrabold">NIM {{ $profile->nim }}</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-[#F9E7A9]/15 text-[#F9E7A9] font-semibold">{{ $profile->prodi_name }} • Semester {{ $profile->semester }}</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full {{ $profile->is_public_enabled ? 'bg-emerald-500/25 text-emerald-200' : 'bg-red-500/25 text-red-200' }} font-semibold">
                        <x-icon :name="$profile->is_public_enabled ? 'globe' : 'lock'" class="w-3.5 h-3.5" />
                        <span>{{ $profile->is_public_enabled ? 'Portofolio Publik Aktif' : 'Portofolio Privat (UU PDP)' }}</span>
                    </span>
                </div>
                <h1 class="mt-2.5 text-2xl sm:text-3xl font-extrabold text-[#FDF6DC]">{{ $student->name }}</h1>
                <p class="text-xs text-[#F9E7A9]/80 mt-1">
                    Dosen Pembimbing Akademik: <strong class="text-[#F2AA00]">{{ $profile->advisor?->name ?? 'Belum ditetapkan' }}</strong>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('mahasiswa.ats-cv') }}" target="_blank"
                   class="inline-flex items-center gap-1.5 rounded-xl bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-[#FDF6DC] px-4 py-2.5 text-xs font-extrabold transition shadow">
                    <x-icon name="document-text" class="w-4 h-4" />
                    <span>1-Click ATS CV</span>
                </a>
                <a href="{{ route('portfolio.show', $profile->public_slug) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 rounded-xl bg-[#FDF6DC]/15 hover:bg-[#FDF6DC] text-[#FDF6DC] hover:text-[#4C2500] border border-[#F9E7A9]/30 px-4 py-2.5 text-xs font-extrabold transition">
                    <x-icon name="link" class="w-4 h-4" />
                    <span>{{ '@' . $profile->public_slug }}</span>
                </a>
                <form method="POST" action="{{ route('mahasiswa.skpi.request') }}">
                    @csrf
                    <button type="submit"
                            @disabled(!$canRequestSkpi)
                            class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-xs font-extrabold transition {{ $canRequestSkpi ? 'bg-emerald-500 hover:bg-emerald-400 text-[#1d2b16] cursor-pointer shadow' : 'bg-[#FDF6DC]/10 text-[#F9E7A9]/45 cursor-not-allowed' }}"
                            title="{{ $canRequestSkpi ? 'Terbitkan SKPI Dwibahasa (Ed25519)' : 'Penuhi minimal 100 poin & seluruh kategori wajib' }}">
                        <x-icon name="academic-cap" class="w-4 h-4" />
                        <span>{{ $canRequestSkpi ? 'Terbitkan SKPI Final' : 'SKPI Terkunci (<100 Poin)' }}</span>
                    </button>
                </form>
            </div>
        </div>
    </section>

    {{-- TOP ANALYTICS ROW: GRADUATION TRACKER + SKILL RADAR + REAL-TIME NOTIFICATIONS --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-7">
        {{-- Graduation Tracker --}}
        <div class="lg:col-span-5 honey-card-strong rounded-3xl p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-extrabold text-[#4C2500]">Graduation & SKPI Credit Tracker</h2>
                    <span class="text-xs font-extrabold px-2.5 py-1 rounded-full bg-[#4C2500] text-[#F9E7A9]">
                        {{ $totalPoints }} / 100 Poin
                    </span>
                </div>

                <div class="mt-4 h-3.5 w-full rounded-full bg-[#4C2500]/15 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-[#CD7000] to-[#F2AA00] transition-all duration-500"
                         style="width: {{ $progressPercent }}%"></div>
                </div>
                <p class="mt-2 text-xs text-[#4C2500]/80">
                    @if($canRequestSkpi)
                        <strong class="text-emerald-800">Memenuhi syarat kelulusan SKPI.</strong> Anda dapat menerbitkan dokumen SKPI Dwibahasa ber-QR kriptografi kapan saja.
                    @else
                        Butuh <strong>{{ max(0, 100 - $totalPoints) }} poin</strong> lagi dan pemenuhan kategori wajib di bawah ini.
                    @endif
                </p>

                <div class="mt-4 space-y-2">
                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-[#CD7000]">Checklist Kategori Wajib Kurikulum</p>
                    @foreach($mandatoryCategories as $mc)
                        <div class="flex items-center justify-between rounded-xl bg-[#FDF6DC]/80 border border-[#4C2500]/10 px-3 py-2 text-xs">
                            <span class="font-semibold text-[#4C2500]">{{ $mc['category']->name_id }}</span>
                            @if($mc['fulfilled'])
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-700 text-white text-[10px] font-bold">
                                    <x-icon name="check" class="w-3 h-3" />
                                    <span>Terpenuhi</span>
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-[#CD7000]/20 text-[#4C2500] text-[10px] font-bold">Belum</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Issued SKPI Credentials List --}}
            @if($credentials->where('type', 'skpi')->isNotEmpty())
                <div class="mt-5 pt-4 border-t border-[#4C2500]/15">
                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-[#4C2500] mb-2">Dokumen SKPI Resmi Anda</p>
                    @foreach($credentials->where('type', 'skpi')->take(2) as $skpi)
                        <div class="flex items-center justify-between gap-2 rounded-xl bg-[#4C2500] text-[#F9E7A9] px-3.5 py-2.5 text-xs mb-1.5">
                            <span class="font-mono font-bold">{{ $skpi->registration_number }}</span>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('credentials.document', $skpi->registration_number) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-[#F2AA00] text-[#4C2500] font-extrabold">Cetak / PDF</a>
                                <a href="{{ route('verify.show', $skpi->registration_number) }}" target="_blank" class="underline text-[#F9E7A9]">QR</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Skill Radar Chart --}}
        <div class="lg:col-span-4 honey-card rounded-3xl p-6 flex flex-col justify-between">
            <div>
                <h2 class="text-base font-extrabold text-[#4C2500]">Skill Radar Chart</h2>
                <p class="text-xs text-[#4C2500]/70">Pemetaan 5 domain kompetensi dari capaian tervalidasi.</p>
            </div>
            <div class="my-2">
                <x-skill-radar :scores="$skillRadar" />
            </div>
            <p class="text-[11px] text-center text-[#4C2500]/65">Diperbarui otomatis setiap kali Dosen PA menyetujui kegiatan.</p>
        </div>

        {{-- Real-Time Reverb Notifications Feed --}}
        <div class="lg:col-span-3 honey-card rounded-3xl p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-extrabold text-[#4C2500]">Notifikasi Hub</h2>
                    @if($notifications->whereNull('read_at')->isNotEmpty())
                        <form method="POST" action="{{ route('mahasiswa.notifications.read') }}">
                            @csrf
                            <button type="submit" class="text-[11px] font-bold text-[#CD7000] hover:underline cursor-pointer">Tandai dibaca</button>
                        </form>
                    @endif
                </div>
                <div class="space-y-2.5 max-h-[290px] overflow-y-auto pr-1">
                    @forelse($notifications as $notif)
                        <div class="rounded-2xl p-3 text-xs border {{ $notif->read_at ? 'bg-[#FDF6DC]/60 border-[#4C2500]/10' : 'bg-[#F9E7A9]/75 border-[#CD7000]/40' }}">
                            <p class="font-extrabold text-[#4C2500]">{{ $notif->title }}</p>
                            <p class="mt-1 text-[#4C2500]/80 leading-snug">{{ $notif->message }}</p>
                            <div class="mt-1.5 flex items-center justify-between text-[10px] text-[#4C2500]/60">
                                <span>{{ $notif->created_at->diffForHumans() }}</span>
                                @if($notif->action_url)
                                    <a href="{{ $notif->action_url }}" class="font-bold text-[#CD7000] underline">Buka →</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-[#4C2500]/60">Belum ada notifikasi baru.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- PROGRESSIVE 3-STEP AI CERTIFICATE WIZARD + PRIVACY CENTER --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
        {{-- 3-Step AI Certificate Parser Wizard --}}
        <div class="lg:col-span-7 honey-card rounded-3xl p-6">
            <div class="flex flex-wrap items-center justify-between gap-2 pb-4 border-b border-[#4C2500]/15">
                <div>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-[#CD7000]">Laravel AI SDK • Zero-Trust Vault</span>
                    <h2 class="text-lg font-extrabold text-[#4C2500]">Unggah Sertifikat Baru (Progressive Wizard)</h2>
                </div>
                <div class="flex items-center gap-1.5 text-[11px] font-extrabold">
                    <span data-step-indicator="1" class="px-2.5 py-1 rounded-full bg-[#F2AA00] text-[#4C2500]">1. Unggah & AI</span>
                    <span data-step-indicator="2" class="px-2.5 py-1 rounded-full bg-[#4C2500]/10 text-[#4C2500]/70">2. Dwibahasa</span>
                    <span data-step-indicator="3" class="px-2.5 py-1 rounded-full bg-[#4C2500]/10 text-[#4C2500]/70">3. Ajukan</span>
                </div>
            </div>

            <form id="certificate-wizard-form" method="POST" action="{{ route('mahasiswa.activities.store') }}" enctype="multipart/form-data" class="mt-5">
                @csrf
                <input type="hidden" name="ai_confidence_score" id="field-ai-confidence" value="88">

                {{-- STEP 1 --}}
                <div data-wizard-step="1" class="space-y-4">
                    <div class="rounded-2xl border-2 border-dashed border-[#CD7000]/50 bg-[#F9E7A9]/35 p-5">
                        <label class="block text-xs font-extrabold text-[#4C2500] mb-1.5">
                            Berkas Sertifikat Asli (PDF / JPG / PNG, Maks 2 MB)
                        </label>
                        <input type="file" name="certificate_file" id="wizard-file-input" accept=".pdf,.jpg,.jpeg,.png" required
                               class="block w-full text-xs text-[#4C2500] file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-extrabold file:bg-[#4C2500] file:text-[#F9E7A9] hover:file:bg-[#CD7000] cursor-pointer">
                        <p class="mt-2 text-[11px] text-[#4C2500]/70">
                            Dilindungi pemeriksaan <strong>Magic Bytes MIME</strong>, anti-duplikasi <strong>SHA-256</strong>, dan sanitasi metadata EXIF otomatis.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#4C2500] mb-1">Petunjuk Tambahan untuk AI Parser (Opsional)</label>
                        <input type="text" id="wizard-hint-text"
                               placeholder="Contoh: Sertifikat AWS Cloud Practitioner 2026 https://credly.com/badges/..."
                               class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/20 px-3.5 py-2.5 text-xs text-[#4C2500]">
                    </div>

                    <div id="ai-parse-status" class="hidden rounded-xl bg-[#F9E7A9]/60 border border-[#CD7000]/30 p-3"></div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <button type="button" id="ai-parse-trigger" data-parse-url="{{ route('mahasiswa.ai-parse') }}"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-[#FDF6DC] px-5 py-2.5 text-xs font-extrabold transition cursor-pointer shadow">
                            <x-icon name="sparkles" class="w-4 h-4" />
                            <span>Ekstrak Otomatis dengan AI &amp; Lanjut</span>
                        </button>
                        <button type="button" data-next-step="2"
                                class="rounded-xl bg-[#4C2500]/10 hover:bg-[#4C2500]/20 text-[#4C2500] px-4 py-2.5 text-xs font-bold transition cursor-pointer">
                            Isi Manual →
                        </button>
                    </div>
                </div>

                {{-- STEP 2 --}}
                <div data-wizard-step="2" class="hidden space-y-3.5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-[#4C2500] mb-1">Nama Kegiatan (Bahasa Indonesia)</label>
                            <input type="text" name="title_id" id="field-title-id" required
                                   class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#4C2500] mb-1">Activity Title (Formal Academic English — SKPI)</label>
                            <input type="text" name="title_en" id="field-title-en" required
                                   class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-[#4C2500] mb-1">Penyelenggara</label>
                            <input type="text" name="organizer" id="field-organizer" required
                                   class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#4C2500] mb-1">Peran / Prestasi</label>
                            <input type="text" name="role_in_activity" id="field-role" value="Peserta" required
                                   class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#4C2500] mb-1">Tanggal Kegiatan</label>
                            <input type="date" name="activity_date" id="field-date" value="{{ now()->format('Y-m-d') }}" required
                                   class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-[#4C2500] mb-1">Kategori Kurikulum SKPI</label>
                            <select name="category_id" id="field-category-id" required
                                    class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">
                                        [{{ $cat->competency_domain }}] {{ $cat->name_id }} (+{{ $cat->default_points }} poin)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#4C2500] mb-1">URL Verifikasi Eksternal (Opsional → Tier 2)</label>
                            <input type="url" name="external_verify_url" id="field-verify-url"
                                   placeholder="https://credly.com/badges/... atau dicoding.com/..."
                                   class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <button type="button" data-prev-step="1" class="rounded-xl bg-[#4C2500]/10 px-4 py-2 text-xs font-bold text-[#4C2500] cursor-pointer">← Kembali</button>
                        <button type="button" data-next-step="3" class="rounded-xl bg-[#4C2500] text-[#F9E7A9] px-5 py-2 text-xs font-extrabold cursor-pointer">Lanjut ke Konfirmasi →</button>
                    </div>
                </div>

                {{-- STEP 3 --}}
                <div data-wizard-step="3" class="hidden space-y-4">
                    <div class="rounded-2xl bg-[#F9E7A9]/55 border border-[#CD7000]/30 p-4 space-y-2.5 text-xs">
                        <p class="font-extrabold text-[#4C2500]">Kontrol Privasi & Pengajuan Validasi</p>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="is_public" value="1" checked class="rounded border-[#4C2500]/40 text-[#CD7000]">
                            <span class="text-[#4C2500]">Tampilkan capaian ini di Public E-Portfolio saya setelah disetujui</span>
                        </label>
                        <p class="text-[11px] text-[#4C2500]/75">
                            Setelah disetujui Dosen PA, data ini akan <strong>dikunci permanen (Immutable)</strong> dan ditandatangani secara kriptografis.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <button type="button" data-prev-step="2" class="rounded-xl bg-[#4C2500]/10 px-4 py-2.5 text-xs font-bold text-[#4C2500] cursor-pointer">← Edit Data</button>
                        <div class="flex items-center gap-2">
                            <button type="submit" name="submit_mode" value="draft"
                                    class="rounded-xl bg-[#FDF6DC] border border-[#4C2500]/30 hover:bg-[#F9E7A9] text-[#4C2500] px-4 py-2.5 text-xs font-extrabold cursor-pointer">
                                Simpan Draf
                            </button>
                            <button type="submit" name="submit_mode" value="submitted"
                                    class="rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F9E7A9] hover:text-[#FDF6DC] px-5 py-2.5 text-xs font-extrabold cursor-pointer shadow">
                                Ajukan Validasi Dosen PA →
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Privacy-by-Default (UU PDP) Control Center --}}
        <div class="lg:col-span-5 honey-card rounded-3xl p-6">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-[#CD7000]">UU PDP • Privacy-by-Default</span>
            <h2 class="text-lg font-extrabold text-[#4C2500]">Kendali Privasi & Public E-Portfolio</h2>

            <form method="POST" action="{{ route('mahasiswa.privacy.update') }}" class="mt-4 space-y-3">
                @csrf
                <label class="flex items-center justify-between rounded-2xl bg-[#F9E7A9]/65 border border-[#CD7000]/35 px-4 py-3 cursor-pointer">
                    <div>
                        <p class="text-xs font-extrabold text-[#4C2500]">Aktifkan Halaman Portofolio Publik</p>
                        <p class="text-[11px] text-[#4C2500]/75">Opt-in untuk HRD & Semantic Talent Pool</p>
                    </div>
                    <input type="checkbox" name="is_public_enabled" value="1" @checked($profile->is_public_enabled) class="h-5 w-5 rounded text-[#CD7000]">
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-[#4C2500] mb-1">Slug Publik (@username)</label>
                        <input type="text" name="public_slug" value="{{ $profile->public_slug }}" required
                               class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs font-mono text-[#4C2500]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#4C2500] mb-1">Telepon (Terenkripsi di DB)</label>
                        <input type="text" name="phone" value="{{ $profile->phone }}"
                               class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#4C2500] mb-1">Headline Profesional</label>
                    <input type="text" name="headline" value="{{ $profile->headline }}"
                           class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#4C2500] mb-1">Keahlian (pisahkan dengan koma — mengindeks Vektor AI)</label>
                    <input type="text" name="skills_csv" value="{{ implode(', ', $profile->skills_json ?? []) }}"
                           class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#4C2500] mb-1">Bio Singkat</label>
                    <textarea name="bio" rows="2" class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs text-[#4C2500]">{{ $profile->bio }}</textarea>
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F9E7A9] hover:text-[#FDF6DC] py-2.5 text-xs font-extrabold transition cursor-pointer">
                    Simpan Privasi & Perbarui Vektor AI
                </button>
            </form>
        </div>
    </div>

    {{-- ACTIVITIES LEDGER WITH 1-CLICK LINKEDIN & PER-ITEM VISIBILITY --}}
    <section class="honey-card rounded-3xl p-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div>
                <h2 class="text-lg font-extrabold text-[#4C2500]">Portofolio Capaian & Sertifikat Anda</h2>
                <p class="text-xs text-[#4C2500]/70">Setiap capaian yang disetujui terkunci permanen dan memiliki tombol 1-Click Add to LinkedIn.</p>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-[#F9E7A9] text-[#4C2500]">{{ $activities->count() }} Total Catatan</span>
        </div>

        <div class="space-y-3.5">
            @forelse($activities as $act)
                <div class="rounded-2xl bg-[#FDF6DC] border border-[#4C2500]/15 p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-trust-badge :tier="$act->trust_tier" />
                            @php
                                $statusCls = match($act->status) {
                                    'approved' => 'bg-emerald-800 text-emerald-100',
                                    'submitted' => 'bg-[#F2AA00]/30 text-[#4C2500]',
                                    'revision' => 'bg-red-700 text-white',
                                    'revoked' => 'bg-red-950 text-red-200',
                                    default => 'bg-[#4C2500]/15 text-[#4C2500]',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase {{ $statusCls }}">
                                <span>{{ $act->status }}</span>
                                @if($act->isLocked())
                                    <x-icon name="lock" class="w-3 h-3" />
                                    <span>Immutable</span>
                                @endif
                            </span>
                            <span class="text-[11px] font-bold text-[#CD7000]">+{{ $act->awarded_points }} Poin SKPI</span>
                        </div>

                        <h3 class="text-sm font-extrabold text-[#4C2500]">{{ $act->title_id }}</h3>
                        <p class="text-xs italic text-[#4C2500]/75">{{ $act->title_en }}</p>
                        <p class="text-[11px] text-[#4C2500]/70">
                            {{ $act->organizer }} • {{ $act->role_in_activity }} • {{ $act->activity_date?->format('d M Y') }} • {{ $act->category?->competency_domain }}
                        </p>

                        @if($act->status === 'revision' && $act->validations->first())
                            <p class="text-xs text-red-800 bg-red-100 rounded-xl px-3 py-1.5 font-semibold">
                                Catatan Revisi Dosen PA: {{ $act->validations->first()->notes }}
                            </p>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        @if($act->file)
                            <a href="{{ $act->file->temporarySignedPreviewUrl(5) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 rounded-xl bg-[#4C2500]/10 hover:bg-[#4C2500]/20 text-[#4C2500] px-3 py-2 text-xs font-bold">
                                <x-icon name="eye" class="w-3.5 h-3.5" />
                                <span>Vault (5m)</span>
                            </a>
                        @endif

                        @if($act->status === 'approved')
                            <a href="{{ $act->linkedInAddUrl() }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 rounded-xl bg-[#0A66C2] hover:bg-[#084d93] text-white px-3.5 py-2 text-xs font-extrabold transition">
                                <x-icon name="link" class="w-3.5 h-3.5" />
                                <span>Add to LinkedIn</span>
                            </a>
                            @if($act->credential)
                                <a href="{{ route('verify.show', $act->credential->registration_number) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-[#F2AA00] hover:bg-[#CD7000] text-[#4C2500] hover:text-[#FDF6DC] px-3 py-2 text-xs font-extrabold transition">
                                    <x-icon name="qrcode" class="w-3.5 h-3.5" />
                                    <span>QR Verify</span>
                                </a>
                            @endif
                        @elseif(in_array($act->status, ['draft', 'revision']))
                            <form method="POST" action="{{ route('mahasiswa.activities.submit', $act) }}">
                                @csrf
                                <button type="submit" class="rounded-xl bg-[#4C2500] text-[#F9E7A9] px-3.5 py-2 text-xs font-extrabold cursor-pointer">
                                    Ajukan Validasi
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('mahasiswa.activities.visibility', $act) }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-[#4C2500]/20 px-3 py-2 text-xs font-bold cursor-pointer {{ $act->is_public ? 'bg-[#F9E7A9] text-[#4C2500]' : 'bg-white text-[#4C2500]/60' }}">
                                <x-icon :name="$act->is_public ? 'globe' : 'lock'" class="w-3.5 h-3.5" />
                                <span>{{ $act->is_public ? 'Publik' : 'Privat' }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-[#4C2500]/70 py-6 text-center">Belum ada sertifikat di portofolio Anda. Mulai dari Progressive Wizard di atas!</p>
            @endforelse
        </div>
    </section>
@endsection
