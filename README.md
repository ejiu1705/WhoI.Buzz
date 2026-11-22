<p align="center">
  <img src="public/images/whoibuzz-logo.png" alt="WhoI.Buzz Logo" width="220" />
</p>

<h1 align="center">WhoI.Buzz — Portal Kredensial & E-Portfolio Kampus</h1>

<p align="center">
  Platform terpusat pengelolaan capaian mahasiswa, pencatatan poin SKPI dwibahasa, validasi berjenjang Dosen PA, verifikasi QR berkriptografi, dan direktori talenta karier kampus.
</p>

<p align="center">
  <a href="https://whoi-buzz.vercel.app"><strong>Buka Aplikasi Live (Vercel)</strong></a> •
  <a href="https://whoi-buzz.vercel.app/verify"><strong>Portal Verifikasi QR</strong></a> •
  <a href="https://whoi-buzz.vercel.app/talent-pool"><strong>Direktori Talenta</strong></a>
</p>

---

## Ringkasan Proyek

**WhoI.Buzz** adalah aplikasi web berbasis **Laravel 13** yang menghubungkan seluruh ekosistem kredensial kampus dalam satu basis data terpusat:
- **Mahasiswa** mengunggah sertifikat & prestasi, menyusun portofolio digital opt-in (patuh UU PDP), memantau poin SKPI, hingga mencetak CV ramah ATS dan SKPI dwibahasa.
- **Dosen Pembimbing Akademik (PA)** memvalidasi pengajuan mahasiswa bimbingannya melalui pratinjau berkas privat aman (*Temporary Signed URL*), menyetujui tunggal/massal, atau meminta revisi.
- **Admin Kemahasiswaan** mengelola pengguna, kurikulum SKPI, penerbitan massal **Auto-Claim Tier 1 (Gold Badge)**, pencabutan kredensial (*revocation list*), serta integrasi **REST API v1** via Sanctum Bearer Token.
- **Mitra Karier / HRD & Publik** memverifikasi keaslian sertifikat/SKPI lewat nomor registrasi & QR kriptografis (`Ed25519 + HMAC-SHA256`) serta mencari kandidat di **Direktori Talenta**.

---

## Akun Login Web WhoI.Buzz

Semua akun di bawah ini sudah tersedia baik di **server production** (https://whoi-buzz.vercel.app/login) maupun setelah menjalankan seeder lokal (`php artisan db:seed`).

> **Kata Sandi Semua Akun Demo:** `password123`

| Role | Nama Pengguna | Email | Dashboard Tujuan | Keterangan / Data Sampel |
| :--- | :--- | :--- | :--- | :--- |
| **Admin Kemahasiswaan** | Siti Rahmawati, M.Kom. | `admin.kemahasiswaan@campus.ac.id` | `/admin/dashboard` | Akses penuh: Manajemen Pengguna, Auto-Claim Tier 1, Pencabutan Kredensial, Progres Kampus, Token & REST API |
| **Dosen PA 1** | Dr. Hendrawan Kusuma, M.T. | `dr.hendrawan@campus.ac.id` | `/dosen/dashboard` | Dosen PA untuk Nadia & Bima; memiliki antrean validasi aktif & laporan progres bimbingan |
| **Dosen PA 2** | Dr. Lestari Wulandari, M.Kom. | `dosen2@campus.ac.id` | `/dosen/dashboard` | Dosen PA untuk Raka; dapat digunakan untuk menguji isolasi Anti-IDOR antar dosen |
| **Mahasiswa 1** | Nadia Putri Mahardika | `nadia.putri@campus.ac.id` | `/mahasiswa/dashboard` | NIM `2201010018` • 130 poin SKPI (memenuhi syarat SKPI) • Portofolio **Publik** (`/@nadia-putri`) |
| **Mahasiswa 2** | Bima Santoso Wijaya | `bima.santoso@campus.ac.id` | `/mahasiswa/dashboard` | NIM `2201010042` • 55 poin SKPI + 1 pengajuan menunggu validasi • Portofolio **Publik** (`/@bima-santoso`) |
| **Mahasiswa 3** | Raka Mahendra Putra | `raka.mahendra@campus.ac.id` | `/mahasiswa/dashboard` | NIM `2302050091` • Portofolio **Privat / Opt-Out UU PDP** (`/@raka-mahendra`) |
| **Mitra Karier / HRD** | Rina Oktavia (TechCorp HRD) | `hrd.mitra@techcorp.id` | `/mitra/dashboard` | Pencarian semantik talenta publik, cek verifikasi QR, dan baca katalog kurikulum SKPI |

### Tautan Sampel Publik (Tanpa Login)
- **Beranda Utama**: https://whoi-buzz.vercel.app/
- **Verifikasi SKPI Sah (Valid)**: https://whoi-buzz.vercel.app/verify/WB-SKPI-2026-NADIA1
- **Dokumen Cetak SKPI Dwibahasa**: https://whoi-buzz.vercel.app/credentials/WB-SKPI-2026-NADIA1/document
- **Verifikasi Kredensial Dicabut (Revoked)**: https://whoi-buzz.vercel.app/verify/WB-REVOKED-2026-999
- **Portofolio Publik Opt-In**: https://whoi-buzz.vercel.app/@nadia-putri
- **Portofolio Privat Opt-Out (UU PDP)**: https://whoi-buzz.vercel.app/@raka-mahendra
- **Direktori Talenta Semantik**: https://whoi-buzz.vercel.app/talent-pool

---

## Matriks Hak Akses per Role

| Fitur / Menu | Tamu | Mahasiswa | Dosen PA | Mitra / HRD | Admin |
| :--- | :---: | :---: | :---: | :---: | :---: |
| Beranda, Verifikasi QR, Talent Pool | Ya | Ya | Ya | Ya | Ya |
| Registrasi Mandiri & Login / Verifikasi Logout | Ya | Ya | Ya | Ya | Ya |
| Pengelolaan Info Akun Mandiri (`/akun`) | - | Ya | Ya | Ya | Ya |
| Dashboard Mahasiswa, Upload Sertifikat, ATS CV, Ajukan SKPI | - | Ya | - | - | - |
| Validasi Kegiatan & Pratinjau Vault Mahasiswa Bimbingan | - | - | Ya (Bimbingan) | - | Ya |
| Laporan Progres SKPI (`/laporan-progres`) | - | - | Ya (Bimbingan) | - | Ya (Semua) |
| Kurikulum SKPI — Lihat & Filter | - | Read-Only | Ya | Read-Only | Ya |
| Kurikulum SKPI — Tambah / Ubah / Hapus | - | - | Milik Sendiri | - | Semua |
| Dashboard Mitra & Pencarian Talenta | - | - | - | Ya | Ya |
| Manajemen Pengguna, Auto-Claim Tier 1, Cabut Kredensial | - | - | - | - | Ya |
| Token Sanctum & REST API `/api/v1/*` (Postman) | - | - | - | - | Ya |

---

## Fitur Utama & Keamanan

- **Autentikasi & RBAC Ketat**: Menggunakan `spatie/laravel-permission` (4 role, 8 permission) dan Laravel Policies/Gates untuk mencegah akses lintas peran (BFLA) maupun lintas pemilik data (Anti-IDOR / BOLA).
- **Verifikasi Logout**: Halaman konfirmasi keluar (`/logout/verify`) yang menginvalidasi sesi & CSRF token lalu mengarahkan pengguna kembali ke beranda (`/`).
- **3-Tier Trust Model**:
  - **Tier 1 (Gold Badge)**: Diterbitkan langsung oleh Admin melalui *Auto-Claim Batch*.
  - **Tier 2 (Verified External)**: Terverifikasi tautan lembaga penerbit eksternal (AWS, Dicoding, BNSP, Puspresnas).
  - **Tier 3 (Admin/Advisor Reviewed)**: Melalui pemeriksaan manual Dosen PA.
- **Private Vault & Anti-Duplikasi SHA-256**: Validasi *magic bytes* file unggahan (PDF/JPG/PNG ≤ 2 MB), re-encode gambar via GD untuk menghapus metadata EXIF/polyglot, pencegahan duplikasi berbasis hash SHA-256, dan pratinjau berkas khusus lewat *Temporary Signed URL* (5 menit).
- **Kriptografi & Pencabutan Real-Time**: Setiap kredensial ditandatangani `Ed25519 + HMAC-SHA256` dengan kode QR SVG; pencabutan oleh Admin langsung mengubah status verifikasi publik menjadi `DICABUT / TIDAK BERLAKU`.
- **Privasi UU PDP (Privacy-by-Default)**: Profil publik mahasiswa nonaktif secara bawaan hingga diaktifkan sendiri, nomor telepon terenkripsi di database, dan data privat tidak bocor ke publik atau user lain.
- **Antarmuka Modern**: Tailwind CSS 4, ikon SVG kustom, dan *Expandable Floating Pill Navbar* dengan palet warna Honey-Gold-Espresso.

---

## Teknologi yang Digunakan

- **Backend**: PHP 8.5, Laravel 13, Spatie Laravel-Permission, Laravel Sanctum, `chillerlan/php-qrcode` v6
- **Frontend**: Blade, Tailwind CSS 4, Vite 7
- **Database**: PostgreSQL 17 (**Supabase** dengan Transaction Pooler & Row Level Security) / MySQL 8 (Lokal)
- **Deployment**: **Vercel** (`vercel-php@0.9.0` serverless runtime) + GitHub Auto-Deploy

---

## Menjalankan Secara Lokal

### Prasyarat
- PHP ≥ 8.3 (ekstensi `pdo_mysql` / `pdo_pgsql`, `gd`, `sodium`, `fileinfo`, `openssl`)
- Composer, Node.js ≥ 20 & npm
- MySQL / PostgreSQL (mis. Laragon) atau SQLite

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/ejiu1705/WhoI.Buzz.git
cd WhoI.Buzz

# 2. Pasang dependensi PHP & JavaScript
composer install
npm install

# 3. Salin konfigurasi environment & buat APP_KEY
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi database di `.env` (contoh MySQL lokal):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=WhoI.Buzz
DB_USERNAME=root
DB_PASSWORD=
```

Lalu jalankan migrasi, seeder, build aset, dan server lokal:

```bash
# 4. Migrasi & isi data awal (termasuk 7 akun demo)
php artisan migrate:fresh --seed

# 5. Build aset Tailwind CSS & JS
npm run build

# 6. Jalankan server pengembangan
php artisan serve
```

Buka http://127.0.0.1:8000 di browser.

### Menjalankan Pengujian Otomatis

```bash
php artisan test
```

Suite pengujian mencakup `15` test (`370 assertions`) yang memverifikasi seluruh menu, fitur, alur login/registrasi/logout, isolasi RBAC 403, Anti-IDOR antar mahasiswa/dosen, validasi kriptografi QR, dan REST API Sanctum.

---

## REST API v1 (Khusus Admin via Postman)

Menu API sengaja tidak ditampilkan di antarmuka publik/navbar demi keamanan. Seluruh endpoint `/api/v1/*` hanya dapat diakses menggunakan **akun Admin** dan **Bearer Token Sanctum** (misalnya via Postman).

| Method | Endpoint | Autentikasi | Deskripsi |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/login` | Email & Password Admin | Mendapatkan Bearer Token (`403` untuk selain Admin) |
| `GET` | `/api/v1/me` | `Bearer <token>` (Admin) | Info akun Admin yang sedang terautentikasi |
| `GET` | `/api/v1/skpi-categories` | `Bearer <token>` (Admin) | Daftar & filter kategori kurikulum SKPI |
| `POST` | `/api/v1/skpi-categories` | `Bearer <token>` (Admin) | Tambah kategori SKPI baru |
| `PUT` | `/api/v1/skpi-categories/{id}` | `Bearer <token>` (Admin) | Ubah kategori SKPI |
| `DELETE` | `/api/v1/skpi-categories/{id}` | `Bearer <token>` (Admin) | Hapus kategori SKPI |
| `GET` | `/api/v1/siakad/students` | `Bearer <token>` (Admin) | Ekspor sinkronisasi SIAKAD format `JSON:API 1.1` |
| `POST` | `/api/v1/logout` | `Bearer <token>` (Admin) | Cabut Bearer Token aktif |

Contoh request login di Postman:

```http
POST https://whoi-buzz.vercel.app/api/v1/login
Accept: application/json
Content-Type: application/json

{
  "email": "admin.kemahasiswaan@campus.ac.id",
  "password": "password123"
}
```

---

## Deployment (Supabase + Vercel)

Konfigurasi serverless sudah tersedia di [vercel.json](vercel.json), [api/index.php](api/index.php), dan [bootstrap/app.php](bootstrap/app.php). Untuk men-deploy ulang pada akun Vercel lain, cukup set Environment Variables berikut di Vercel Project Settings (gunakan **Supabase Transaction Pooler** port `6543` agar kompatibel IPv4):

```env
APP_KEY=base64:<generate_with_php_artisan_key_generate>
APP_URL=https://<domain-vercel-anda>.vercel.app
DB_CONNECTION=pgsql
DB_HOST=<aws-x-region>.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.<project_ref>
DB_PASSWORD=<password_database_supabase_anda>
DB_SSLMODE=require
```

---

## Lisensi

Proyek ini dirilis di bawah lisensi [MIT](https://opensource.org/licenses/MIT).
