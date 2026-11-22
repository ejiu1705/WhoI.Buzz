<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminHubController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MitraDashboardController;
use App\Http\Controllers\ProgressReportController;
use App\Http\Controllers\PublicHubController;
use App\Http\Controllers\ReviewerController;
use App\Http\Controllers\SkpiCategoryController;
use App\Http\Controllers\StudentDashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Halaman Utama Publik: Beranda Tentang WhoI.Buzz (pilihan Login & Registrasi sebelum masuk)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::redirect('/beranda', '/');

// Autentikasi Khusus Tamu (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login-attempt')->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Verifikasi Kredensial QR, Portofolio Publik Opt-In, & Talent Pool
Route::middleware('throttle:verify-public')->group(function () {
    Route::get('/verify', [PublicHubController::class, 'verifyIndex'])->name('verify.index');
    Route::get('/verify/{registrationNumber}', [PublicHubController::class, 'verifyShow'])->name('verify.show');
    Route::get('/credentials/{registrationNumber}/document', [PublicHubController::class, 'credentialDocument'])->name('credentials.document');
});

Route::get('/@{slug}', [PublicHubController::class, 'portfolio'])
    ->where('slug', '[A-Za-z0-9\-_]+')
    ->name('portfolio.show');

Route::get('/talent-pool', [PublicHubController::class, 'talentPool'])->name('talent.index');

// Rute Terautentikasi (Semua Role Aktif)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => redirect(Auth::user()->dashboardRoute()))->name('dashboard');

    // Verifikasi Logout & Logout
    Route::get('/logout/verify', [AuthController::class, 'confirmLogout'])->name('logout.confirm');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Pengelolaan Info Akun Mandiri
    Route::prefix('akun')->name('account.')->group(function () {
        Route::get('/', [AccountController::class, 'show'])->name('show');
        Route::put('/', [AccountController::class, 'update'])->name('update');
        Route::put('/password', [AccountController::class, 'updatePassword'])->name('password');
        Route::post('/tokens', [AccountController::class, 'createApiToken'])->middleware('role:admin')->name('tokens.store');
        Route::delete('/tokens/{tokenId}', [AccountController::class, 'revokeApiToken'])->middleware('role:admin')->name('tokens.destroy');
        Route::delete('/', [AccountController::class, 'destroy'])->name('destroy');
    });

    // Pratinjau Berkas Privat (Signed URL 5 Menit + Policy)
    Route::get('/vault/preview/{activityFile}', [PublicHubController::class, 'vaultPreview'])
        ->middleware('signed')
        ->name('vault.preview');

    // Pengelolaan Kurikulum SKPI
    Route::resource('kurikulum-skpi', SkpiCategoryController::class)
        ->only(['create', 'store'])
        ->middleware('can:kelola-kurikulum-skpi');
    Route::resource('kurikulum-skpi', SkpiCategoryController::class)
        ->except(['create', 'store']);
});

// 1. Dashboard & Fitur Khusus Mahasiswa
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
    Route::post('/ai-parse', [StudentDashboardController::class, 'aiParse'])->middleware('throttle:certificate-upload')->name('ai-parse');
    Route::post('/activities', [StudentDashboardController::class, 'storeActivity'])->middleware('throttle:certificate-upload')->name('activities.store');
    Route::post('/activities/{activity}/submit', [StudentDashboardController::class, 'submitDraft'])->name('activities.submit');
    Route::post('/activities/{activity}/visibility', [StudentDashboardController::class, 'toggleVisibility'])->name('activities.visibility');
    Route::post('/privacy', [StudentDashboardController::class, 'updatePrivacy'])->name('privacy.update');
    Route::post('/skpi/request', [StudentDashboardController::class, 'requestSkpi'])->name('skpi.request');
    Route::get('/ats-cv', [StudentDashboardController::class, 'atsCv'])->name('ats-cv');
    Route::post('/notifications/read', [StudentDashboardController::class, 'markNotificationsRead'])->name('notifications.read');
});

// 2. Dashboard & Fitur Khusus Dosen PA
Route::middleware(['auth', 'role:dosen|admin'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/dashboard', [ReviewerController::class, 'index'])->name('dashboard');
    Route::post('/activities/{activity}/decide', [ReviewerController::class, 'decide'])->name('decide');
    Route::post('/bulk-approve', [ReviewerController::class, 'bulkApprove'])->name('bulk-approve');
});

// Laporan Progres SKPI (Khusus Admin & Dosen PA)
Route::middleware(['auth', 'role:admin|dosen', 'can:lihat-progres'])
    ->get('/laporan-progres', [ProgressReportController::class, 'index'])
    ->name('laporan.progres');

// 3. Dashboard & Fitur Khusus Admin Kemahasiswaan
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminHubController::class, 'index'])->name('dashboard');
    Route::post('/auto-claim', [AdminHubController::class, 'autoClaimBatch'])
        ->middleware('permission:terbitkan-autoclaim')
        ->name('auto-claim');
    Route::post('/credentials/{credential}/revoke', [AdminHubController::class, 'revokeCredential'])
        ->middleware('permission:cabut-kredensial')
        ->name('credentials.revoke');
    Route::view('/api-console', 'api-client.index')->name('api.console');

    Route::resource('users', AdminUserController::class)->middleware('can:kelola-pengguna');
});

// 4. Dashboard & Fitur Khusus Mitra Karier / HRD
Route::middleware(['auth', 'role:mitra|admin'])->prefix('mitra')->name('mitra.')->group(function () {
    Route::get('/dashboard', [MitraDashboardController::class, 'index'])->name('dashboard');
});
