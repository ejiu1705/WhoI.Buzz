@extends('layouts.app')

@section('title', 'RESTful API Client (Sanctum Bearer Token) — WhoI.Buzz')

@section('content')
    <section class="espresso-card rounded-[28px] p-6 sm:p-8 mb-6">
        <span class="inline-block px-3 py-0.5 rounded-full bg-[#F2AA00] text-[#4C2500] text-[11px] font-extrabold">
            Modul 4 • RESTful API v1 + Laravel Sanctum Bearer Token + Fetch API (async/await)
        </span>
        <h1 class="mt-2 text-2xl font-extrabold text-[#FDF6DC]">Klien Konsumsi RESTful API Kurikulum SKPI</h1>
        <p class="text-xs text-[#F9E7A9]/80 mt-1">
            Seluruh data pada halaman ini diambil langsung dari <code class="font-mono text-[#F2AA00]">/api/v1/skpi-categories</code> via JavaScript <code>fetch()</code> dengan escaping XSS <code>esc()</code> dan autentikasi <code>Authorization: Bearer &lt;token&gt;</code>.
        </p>
    </section>

    <div id="api-alert-area" class="mb-4"></div>

    {{-- Sanctum Login / Token Status Box --}}
    <section class="honey-card-strong rounded-3xl p-5 mb-6">
        <div id="api-form-login" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            <input type="email" id="api-login-email" value="admin.kemahasiswaan@campus.ac.id" placeholder="Email akun (Admin/Dosen untuk tulis)"
                   class="md:col-span-5 rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3.5 py-2 text-xs">
            <input type="password" id="api-login-password" value="password123" placeholder="Password"
                   class="md:col-span-4 rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3.5 py-2 text-xs">
            <button type="button" id="api-btn-login"
                    class="md:col-span-3 inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#4C2500] hover:bg-[#CD7000] text-[#F2AA00] hover:text-[#FDF6DC] py-2 text-xs font-extrabold cursor-pointer transition">
                <x-icon name="key" class="w-3.5 h-3.5" />
                <span>Login API (Bearer Token)</span>
            </button>
            <p id="api-err-login" class="md:col-span-12 text-xs font-bold text-red-700"></p>
        </div>

        <div id="api-info-user" class="hidden flex flex-wrap items-center justify-between gap-3 text-xs">
            <div>
                Terautentikasi via Bearer Token sebagai <strong id="api-nama-user" class="text-[#4C2500]"></strong>
                (<span id="api-role-user" class="font-mono font-bold text-[#CD7000]"></span>)
            </div>
            <div class="flex gap-2">
                <button type="button" id="api-btn-tambah" class="rounded-xl bg-[#4C2500] text-[#F2AA00] px-4 py-2 text-xs font-extrabold cursor-pointer">+ Tambah Kategori via POST</button>
                <button type="button" id="api-btn-logout" class="rounded-xl bg-red-800 text-white px-4 py-2 text-xs font-bold cursor-pointer">Cabut Token (Logout API)</button>
            </div>
        </div>
    </section>

    {{-- Create / Edit Form via API --}}
    <section id="api-kartu-form" class="hidden honey-card rounded-3xl p-6 mb-6">
        <h2 class="text-sm font-extrabold text-[#4C2500] mb-3">Formulir Kategori SKPI (POST / PUT JSON)</h2>
        <form id="api-form-cat" class="grid grid-cols-1 md:grid-cols-12 gap-3" novalidate>
            <input type="hidden" id="cat-id">
            <div class="md:col-span-3">
                <input id="cat-code" placeholder="Kode (SKPI-API-01)" class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs font-mono">
                <small id="err-code" class="text-red-700 text-[11px] font-semibold"></small>
            </div>
            <div class="md:col-span-4">
                <input id="cat-name_id" placeholder="Nama Kategori (ID)" class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs">
                <small id="err-name_id" class="text-red-700 text-[11px] font-semibold"></small>
            </div>
            <div class="md:col-span-5">
                <input id="cat-name_en" placeholder="Category Name (EN)" class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs">
                <small id="err-name_en" class="text-red-700 text-[11px] font-semibold"></small>
            </div>
            <div class="md:col-span-3">
                <select id="cat-competency_domain" class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs font-bold">
                    <option value="Technical">Technical</option>
                    <option value="Academic">Academic</option>
                    <option value="Leadership">Leadership</option>
                    <option value="Communication">Communication</option>
                    <option value="Social">Social</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <input type="number" id="cat-default_points" value="20" placeholder="Poin" class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs">
                <small id="err-default_points" class="text-red-700 text-[11px] font-semibold"></small>
            </div>
            <div class="md:col-span-3">
                <select id="cat-level" class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs font-bold">
                    <option value="Nasional">Nasional</option>
                    <option value="Lokal">Lokal</option>
                    <option value="Internasional">Internasional</option>
                </select>
            </div>
            <div class="md:col-span-4 flex items-center gap-2">
                <button type="submit" class="rounded-xl bg-[#4C2500] text-[#F2AA00] px-4 py-2 text-xs font-extrabold cursor-pointer">Simpan JSON</button>
                <button type="button" id="api-btn-batal" class="rounded-xl bg-[#4C2500]/10 text-[#4C2500] px-4 py-2 text-xs font-bold cursor-pointer">Batal</button>
            </div>
        </form>
    </section>

    {{-- Filter + Live API Table --}}
    <section class="honey-card rounded-3xl p-6">
        <form id="api-form-filter" class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-4">
            <input id="api-filter-cari" placeholder="Cari kode / nama via GET /api/v1/skpi-categories..."
                   class="md:col-span-6 rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3.5 py-2 text-xs">
            <select id="api-filter-domain" class="md:col-span-4 rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs font-bold">
                <option value="">Semua Domain</option>
                <option value="Technical">Technical</option>
                <option value="Academic">Academic</option>
                <option value="Leadership">Leadership</option>
                <option value="Communication">Communication</option>
                <option value="Social">Social</option>
            </select>
            <button type="submit" class="md:col-span-2 rounded-xl bg-[#4C2500] text-[#F9E7A9] py-2 text-xs font-extrabold cursor-pointer">Fetch Data</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#4C2500]/15 text-[#CD7000] uppercase text-[10px]">
                        <th class="py-2 pr-3">No</th>
                        <th class="py-2 pr-3">Kode</th>
                        <th class="py-2 pr-3">Nama Kategori</th>
                        <th class="py-2 pr-3">Domain</th>
                        <th class="py-2 pr-3">Poin</th>
                        <th class="py-2">Aksi (Bearer Token)</th>
                    </tr>
                </thead>
                <tbody id="api-tbody" class="divide-y divide-[#4C2500]/10"></tbody>
            </table>
        </div>

        <div class="mt-4 flex items-center justify-between text-xs">
            <span id="api-info-halaman" class="font-bold text-[#4C2500]"></span>
            <div class="flex gap-2">
                <button type="button" id="api-btn-prev" class="rounded-lg bg-[#4C2500]/10 px-3 py-1 font-bold disabled:opacity-40 cursor-pointer">&laquo; Prev</button>
                <button type="button" id="api-btn-next" class="rounded-lg bg-[#4C2500]/10 px-3 py-1 font-bold disabled:opacity-40 cursor-pointer">Next &raquo;</button>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script src="{{ asset('js/skpi-api-client.js') }}"></script>
@endpush
