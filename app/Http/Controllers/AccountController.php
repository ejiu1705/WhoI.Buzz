<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAccountRequest;
use App\Services\AuditService;
use App\Services\SemanticTalentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load(['roles.permissions', 'studentProfile.advisor']);
        $permissions = $user->getAllPermissions()->pluck('name')->values();
        $apiTokens = $user->tokens()->latest()->get();

        return view('account.show', compact('user', 'permissions', 'apiTokens'));
    }

    public function update(UpdateAccountRequest $request, SemanticTalentService $semantic, AuditService $audit): RedirectResponse
    {
        $user = $request->user()->load('studentProfile');
        $data = $request->validated();

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'passkey_enabled' => $data['passkey_enabled'],
        ]);

        if ($user->studentProfile) {
            $user->studentProfile->update([
                'phone' => $data['phone'] ?? $user->studentProfile->phone,
                'headline' => $data['headline'] ?? $user->studentProfile->headline,
                'bio' => $data['bio'] ?? $user->studentProfile->bio,
            ]);
            $semantic->refreshStudentEmbedding($user->studentProfile);
        }

        $audit->log('account.updated', $user, ['email' => $user->email], $user);

        return back()->with('success', 'Informasi akun Anda berhasil diperbarui.');
    }

    public function updatePassword(Request $request, AuditService $audit): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'Kata sandi saat ini tidak cocok.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user = $request->user();
        $user->update(['password' => Hash::make($data['password'])]);
        $audit->log('account.password_changed', $user, [], $user);

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }

    public function createApiToken(Request $request, AuditService $audit): RedirectResponse
    {
        $data = $request->validate([
            'token_name' => ['required', 'string', 'min:2', 'max:60'],
        ]);

        $user = $request->user();
        $plain = $user->createToken($data['token_name'])->plainTextToken;
        $audit->log('account.api_token_created', $user, ['name' => $data['token_name']], $user);

        return back()
            ->with('success', 'Bearer Token API Sanctum berhasil dibuat! Salin sekarang karena hanya tampil satu kali.')
            ->with('new_api_token', $plain);
    }

    public function revokeApiToken(Request $request, int $tokenId, AuditService $audit): RedirectResponse
    {
        $user = $request->user();
        $user->tokens()->where('id', $tokenId)->delete();
        $audit->log('account.api_token_revoked', $user, ['token_id' => $tokenId], $user);

        return back()->with('success', 'Token API berhasil dicabut.');
    }

    public function destroy(Request $request, AuditService $audit): RedirectResponse
    {
        $request->validate([
            'delete_password' => ['required', 'current_password'],
        ], [
            'delete_password.current_password' => 'Kata sandi konfirmasi salah. Penghapusan akun dibatalkan.',
        ]);

        $user = $request->user();
        if ($user->hasRole('admin') && \App\Models\User::role('admin')->count() <= 1) {
            return back()->with('error', 'Tidak dapat menghapus satu-satunya akun Admin yang tersisa.');
        }

        $audit->log('account.deleted', $user, ['email' => $user->email], $user);

        Auth::logout();
        $user->tokens()->delete();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Akun Anda telah dihapus permanen dari database terpusat.');
    }
}
