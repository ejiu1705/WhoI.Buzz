const API = '/api/v1';
const FIELDS = ['code', 'name_id', 'name_en', 'default_points'];
const $ = (id) => document.getElementById(id);
let halaman = 1;

// XSS Escaping (Modul 4 & 6)
const esc = (t) => String(t ?? '').replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
}[c]));

class ApiError extends Error {
    constructor(status, message, errors = {}) {
        super(message);
        this.status = status;
        this.errors = errors;
    }
}

const showAlert = (text, kind = 'success') => {
    const cls = kind === 'success'
        ? 'bg-[#4C2500] text-[#F9E7A9] border-[#F2AA00]'
        : 'bg-red-950 text-red-100 border-red-600';
    $('api-alert-area').innerHTML = `<div class="rounded-2xl border px-4 py-3 text-xs font-bold ${cls}">${esc(text)}</div>`;
};

const getToken = () => localStorage.getItem('wb_api_token');

function renderAuthState() {
    const hasToken = Boolean(getToken());
    $('api-form-login').classList.toggle('hidden', hasToken);
    $('api-info-user').classList.toggle('hidden', !hasToken);
    $('api-nama-user').textContent = localStorage.getItem('wb_api_name') ?? '';
    $('api-role-user').textContent = localStorage.getItem('wb_api_role') ?? '';
    if (!hasToken) $('api-kartu-form').classList.add('hidden');
}

async function apiRequest(path, { method = 'GET', body = null, auth = false } = {}) {
    const headers = { Accept: 'application/json' };
    if (body) headers['Content-Type'] = 'application/json';
    if (auth && getToken()) headers.Authorization = `Bearer ${getToken()}`;

    let res;
    try {
        res = await fetch(API + path, { method, headers, body: body ? JSON.stringify(body) : undefined });
    } catch {
        throw new ApiError(0, 'Tidak dapat terhubung ke server API.');
    }

    if (res.status === 204) return null;
    const data = await res.json().catch(() => null);

    if (!res.ok) {
        if (res.status === 401 && auth) {
            localStorage.removeItem('wb_api_token');
            renderAuthState();
        }
        throw new ApiError(res.status, data?.message ?? `HTTP ${res.status}`, data?.errors ?? {});
    }
    return data;
}

function handleError(err) {
    FIELDS.forEach((f) => { if ($(`err-${f}`)) $(`err-${f}`).textContent = ''; });
    if (err.status === 422) {
        Object.entries(err.errors).forEach(([f, msgs]) => {
            if ($(`err-${f}`)) $(`err-${f}`).textContent = msgs.join(' ');
        });
        showAlert('Validasi API gagal (HTTP 422). Periksa input Anda.', 'danger');
    } else if (err.status === 401 || err.status === 403) {
        showAlert(`Akses ditolak (${err.status}): ${err.message}`, 'danger');
    } else {
        showAlert(err.message, 'danger');
    }
}

async function loadCategories(page = 1) {
    const q = new URLSearchParams({ page });
    if ($('api-filter-cari').value.trim()) q.set('cari', $('api-filter-cari').value.trim());
    if ($('api-filter-domain').value) q.set('domain', $('api-filter-domain').value);

    try {
        const json = await apiRequest(`/skpi-categories?${q}`);
        halaman = json.meta.current_page;
        const canWrite = Boolean(getToken());

        $('api-tbody').innerHTML = json.data.map((c, idx) => `
            <tr>
                <td class="py-2.5 pr-3 font-mono">${(json.meta.from ?? 1) + idx}</td>
                <td class="py-2.5 pr-3 font-mono font-extrabold text-[#4C2500]">${esc(c.code)}</td>
                <td class="py-2.5 pr-3">
                    <p class="font-bold text-[#4C2500]">${esc(c.name_id)}</p>
                    <p class="text-[11px] italic text-[#4C2500]/70">${esc(c.name_en)}</p>
                </td>
                <td class="py-2.5 pr-3">${esc(c.competency_domain)} (${esc(c.level)})</td>
                <td class="py-2.5 pr-3 font-mono font-bold text-[#CD7000]">+${esc(c.default_points)}</td>
                <td class="py-2.5">
                    ${canWrite
                        ? `<button class="px-2.5 py-1 rounded-lg bg-[#F2AA00] text-[#4C2500] font-bold mr-1 cursor-pointer" data-aksi="ubah" data-id="${esc(c.id)}">Ubah</button>
                           <button class="px-2.5 py-1 rounded-lg bg-red-800 text-white font-bold cursor-pointer" data-aksi="hapus" data-id="${esc(c.id)}">Hapus</button>`
                        : '<span class="text-[#4C2500]/50">Login API untuk aksi tulis</span>'}
                </td>
            </tr>`).join('') || '<tr><td colspan="6" class="py-6 text-center">Belum ada data.</td></tr>';

        $('api-info-halaman').textContent = `Halaman ${json.meta.current_page} dari ${json.meta.last_page} (Total ${json.meta.total})`;
        $('api-btn-prev').disabled = json.links.prev === null;
        $('api-btn-next').disabled = json.links.next === null;
    } catch (err) {
        $('api-tbody').innerHTML = `<tr><td colspan="6" class="py-6 text-center text-red-700 font-bold">${esc(err.message)}</td></tr>`;
    }
}

function openForm(c = {}) {
    FIELDS.forEach((f) => { if ($(`err-${f}`)) $(`err-${f}`).textContent = ''; });
    $('cat-id').value = c.id ?? '';
    $('cat-code').value = c.code ?? '';
    $('cat-name_id').value = c.name_id ?? '';
    $('cat-name_en').value = c.name_en ?? '';
    $('cat-competency_domain').value = c.competency_domain ?? 'Technical';
    $('cat-default_points').value = c.default_points ?? 20;
    $('cat-level').value = c.level ?? 'Nasional';
    $('api-kartu-form').classList.remove('hidden');
}

async function saveCategory(e) {
    e.preventDefault();
    const id = $('cat-id').value;
    const body = {
        code: $('cat-code').value.trim(),
        name_id: $('cat-name_id').value.trim(),
        name_en: $('cat-name_en').value.trim(),
        competency_domain: $('cat-competency_domain').value,
        default_points: Number($('cat-default_points').value),
        level: $('cat-level').value,
        is_mandatory: false,
    };
    try {
        await apiRequest(id ? `/skpi-categories/${id}` : '/skpi-categories', {
            method: id ? 'PUT' : 'POST',
            body,
            auth: true,
        });
        showAlert(id ? 'Kategori diperbarui via API (HTTP 200).' : 'Kategori ditambahkan via API (HTTP 201).');
        $('api-kartu-form').classList.add('hidden');
        await loadCategories(id ? halaman : 1);
    } catch (err) {
        handleError(err);
    }
}

async function loginApi() {
    $('api-err-login').textContent = '';
    try {
        const d = await apiRequest('/login', {
            method: 'POST',
            body: { email: $('api-login-email').value, password: $('api-login-password').value },
        });
        localStorage.setItem('wb_api_token', d.token);
        localStorage.setItem('wb_api_name', d.user.name);
        localStorage.setItem('wb_api_role', d.user.role);
        renderAuthState();
        showAlert(`Login API berhasil! Bearer Token tersimpan untuk ${d.user.name}.`);
        await loadCategories(halaman);
    } catch (err) {
        $('api-err-login').textContent = err.message;
    }
}

async function logoutApi() {
    try { await apiRequest('/logout', { method: 'POST', auth: true }); } catch {}
    localStorage.removeItem('wb_api_token');
    localStorage.removeItem('wb_api_name');
    localStorage.removeItem('wb_api_role');
    renderAuthState();
    showAlert('Token API berhasil dicabut (Logout API).');
    await loadCategories(halaman);
}

document.addEventListener('DOMContentLoaded', async () => {
    $('api-btn-login').onclick = loginApi;
    $('api-btn-logout').onclick = logoutApi;
    $('api-btn-tambah').onclick = () => openForm();
    $('api-btn-batal').onclick = () => $('api-kartu-form').classList.add('hidden');
    $('api-form-cat').onsubmit = saveCategory;
    $('api-form-filter').onsubmit = (e) => { e.preventDefault(); loadCategories(1); };
    $('api-btn-prev').onclick = () => loadCategories(halaman - 1);
    $('api-btn-next').onclick = () => loadCategories(halaman + 1);

    $('api-tbody').onclick = async (e) => {
        const btn = e.target.closest('button[data-aksi]');
        if (!btn) return;
        const id = btn.dataset.id;
        if (btn.dataset.aksi === 'hapus') {
            if (!confirm('Hapus kategori via DELETE API?')) return;
            try {
                await apiRequest(`/skpi-categories/${id}`, { method: 'DELETE', auth: true });
                showAlert('Kategori dihapus via API (HTTP 204).');
                await loadCategories(halaman);
            } catch (err) { handleError(err); }
        } else {
            try {
                const res = await apiRequest(`/skpi-categories/${id}`);
                openForm(res.data);
            } catch (err) { handleError(err); }
        }
    };

    renderAuthState();
    await loadCategories(1);
});
