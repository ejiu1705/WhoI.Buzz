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

// Halaman Utama Publik: Dashboard Tentang WhoI.Buzz (pilihan Login & Registrasi sebelum masuk)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::redirect('/beranda', '/');

// Guest-Only Authentication Routes (Modul 5: middleware guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login-attempt')->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Quick Demo SSO Role Switcher (optional testing shortcut)
Route::post('/demo-switch', [AuthController::class, 'demoSwitch'])->name('demo.switch');

// Public QR Verification, Opt-In Portfolio & Talent Pool (for HRD / external verification links)
Route::middleware('throttle:verify-public')->group(function () {
    Route::get('/verify', [PublicHubController::class, 'verifyIndex'])->name('verify.index');
    Route::get('/verify/{registrationNumber}', [PublicHubController::class, 'verifyShow'])->name('verify.show');
    Route::get('/credentials/{registrationNumber}/document', [PublicHubController::class, 'credentialDocument'])->name('credentials.document');
});

Route::get('/@{slug}', [PublicHubController::class, 'portfolio'])
    ->where('slug', '[A-Za-z0-9\-_]+')
    ->name('portfolio.show');

Route::get('/talent-pool', [PublicHubController::class, 'talentPool'])->name('talent.index');
Route::view('/api-client', 'api-client.index')->name('api.client');

// Authenticated Routes (All Logged-In Users)
Route::middleware('auth')->group(function () {
    // Central Dashboard Dispatcher -> directs each role to its own exclusive dashboard
    Route::get('/dashboard', fn () => redirect(Auth::user()->dashboardRoute()))->name('dashboard');

    // Verifikasi Logout & Logout Execution
    Route::get('/logout/verify', [AuthController::class, 'confirmLogout'])->name('logout.confirm');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Info Akun & Self-Service CRUD (Read, Update, Password, API Token, Delete Account)
    Route::prefix('akun')->name('account.')->group(function () {
        Route::get('/', [AccountController::class, 'show'])->name('show');
        Route::put('/', [AccountController::class, 'update'])->name('update');
        Route::put('/password', [AccountController::class, 'updatePassword'])->name('password');
        Route::post('/tokens', [AccountController::class, 'createApiToken'])->name('tokens.store');
        Route::delete('/tokens/{tokenId}', [AccountController::class, 'revokeApiToken'])->name('tokens.destroy');
        Route::delete('/', [AccountController::class, 'destroy'])->name('destroy');
    });

    // Zero-Trust Private Vault Preview (Temporary Signed URL + Policy)
    Route::get('/vault/preview/{activityFile}', [PublicHubController::class, 'vaultPreview'])
        ->middleware('signed')
        ->name('vault.preview');

    // Modul 3 & 6: Resource CRUD Kurikulum SKPI (create/store gated by can:kelola-kurikulum-skpi; registered first before {kurikulum_skpi})
    Route::resource('kurikulum-skpi', SkpiCategoryController::class)
        ->only(['create', 'store'])
        ->middleware('can:kelola-kurikulum-skpi');
    Route::resource('kurikulum-skpi', SkpiCategoryController::class)
        ->except(['create', 'store']);
});

// 1. DASHBOARD & FITUR KHUSUS MAHASISWA (role:mahasiswa)
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

// 2. DASHBOARD & FITUR KHUSUS DOSEN PA / VERIFIKATOR (role:dosen|admin)
Route::middleware(['auth', 'role:dosen|admin'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/dashboard', [ReviewerController::class, 'index'])->name('dashboard');
    Route::post('/activities/{activity}/decide', [ReviewerController::class, 'decide'])->name('decide');
    Route::post('/bulk-approve', [ReviewerController::class, 'bulkApprove'])->name('bulk-approve');
});

// Modul 5 Multiple Role Route (Admin ATAU Dosen)
Route::middleware(['auth', 'role:admin|dosen', 'can:lihat-progres'])
    ->get('/laporan-progres', [ProgressReportController::class, 'index'])
    ->name('laporan.progres');

// 3. DASHBOARD & FITUR KHUSUS ADMIN KEMAHASISWAAN (role:admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminHubController::class, 'index'])->name('dashboard');
    Route::post('/auto-claim', [AdminHubController::class, 'autoClaimBatch'])->name('auto-claim');
    Route::post('/categories', [AdminHubController::class, 'storeCategory'])->name('categories.store');
    Route::post('/credentials/{credential}/revoke', [AdminHubController::class, 'revokeCredential'])->name('credentials.revoke');

    // Admin CRUD Manajemen Akun & Role (7-Method Resource Controller)
    Route::resource('users', AdminUserController::class)->middleware('can:kelola-pengguna');
});

// 4. DASHBOARD & FITUR KHUSUS MITRA KARIER / HRD (role:mitra|admin)
Route::middleware(['auth', 'role:mitra|admin'])->prefix('mitra')->name('mitra.')->group(function () {
    Route::get('/dashboard', [MitraDashboardController::class, 'index'])->name('dashboard');
});
