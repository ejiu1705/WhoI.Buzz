<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\AuditService;
use App\Services\SemanticTalentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        $demoAccounts = User::with(['roles', 'studentProfile'])
            ->whereIn('email', [
                'admin.kemahasiswaan@campus.ac.id',
                'dr.hendrawan@campus.ac.id',
                'nadia.putri@campus.ac.id',
                'hrd.mitra@techcorp.id',
            ])
            ->get();

        return view('auth.login', compact('demoAccounts'));
    }

    public function login(LoginRequest $request, AuditService $audit): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user()->load('roles');
        $audit->log('auth.login', $user, ['method' => 'session_guard', 'role' => $user->primaryRole()], $user);

        return redirect()->intended($user->dashboardRoute())
            ->with('success', "Selamat datang, {$user->name}! Anda masuk sebagai {$user->roleLabel()}.");
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request, SemanticTalentService $semantic, AuditService $audit): RedirectResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data, $semantic) {
            $role = $data['role'];
            $roleScope = $role === 'mitra' ? 'career_partner' : 'student';

            $user = User::create([
                'name' => $data['name'],
                'email' => Str::lower($data['email']),
                'sso_id' => 'REG-'.strtoupper(Str::random(8)),
                'role_scope' => $roleScope,
                'password' => Hash::make($data['password']),
                'passkey_enabled' => false,
            ]);
            $user->assignRole($role);

            if ($role === 'mahasiswa') {
                $defaultAdvisor = User::role('dosen')->first();
                $baseSlug = Str::slug($data['name']) ?: 'mahasiswa';
                $slug = $baseSlug.'-'.strtolower(Str::random(4));

                $profile = StudentProfile::create([
                    'user_id' => $user->id,
                    'nim' => $data['nim'],
                    'prodi_name' => $data['prodi_name'],
                    'faculty_name' => ($data['faculty_name'] ?? null) ?: 'Fakultas Ilmu Komputer',
                    'semester' => (int) ($data['semester'] ?? 1),
                    'advisor_id' => $defaultAdvisor?->id,
                    'public_slug' => $slug,
                    'is_public_enabled' => false, // UU PDP Privacy-by-Default
                    'headline' => "Mahasiswa {$data['prodi_name']} — WhoI.Buzz Verified",
                    'bio' => 'Akun baru terdaftar pada Campus Verified Credential & E-Portfolio Hub.',
                    'skills_json' => [],
                    'yudisium_status' => 'active',
                ]);

                $semantic->refreshStudentEmbedding($profile);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        $user->load('roles');

        $audit->log('auth.registered', $user, ['role' => $user->primaryRole()], $user);

        return redirect($user->dashboardRoute())
            ->with('success', "Registrasi berhasil! Selamat datang di WhoI.Buzz, {$user->name} ({$user->roleLabel()}).");
    }

    /**
     * Dedicated Logout Verification / Confirmation Screen (Verifikasi Logout).
     */
    public function confirmLogout(Request $request): View
    {
        $user = $request->user()->load('roles');
        $activeTokensCount = $user->tokens()->count();

        return view('auth.logout-confirm', compact('user', 'activeTokensCount'));
    }

    public function logout(Request $request, AuditService $audit): RedirectResponse
    {
        $user = $request->user();
        if ($user) {
            if ($request->boolean('revoke_api_tokens')) {
                $user->tokens()->delete();
            }
            $audit->log('auth.logout', $user, [
                'revoked_api_tokens' => $request->boolean('revoke_api_tokens'),
            ], $user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Verifikasi logout berhasil. Sesi Anda telah ditutup dengan aman.');
    }

    /**
     * Quick Demo SSO switch across seeded roles.
     */
    public function demoSwitch(Request $request, AuditService $audit): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::with('roles')->where('email', $data['email'])->firstOrFail();
        Auth::login($user, true);
        $request->session()->regenerate();

        $audit->log('auth.sso_demo_switch', $user, ['role' => $user->primaryRole()], $user);

        return redirect($user->dashboardRoute())
            ->with('success', "Masuk sebagai {$user->name} ({$user->roleLabel()}).");
    }
}
