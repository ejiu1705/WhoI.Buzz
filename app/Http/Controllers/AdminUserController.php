<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\AuditService;
use App\Services\SemanticTalentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with(['roles', 'studentProfile'])
            ->withCount(['activities', 'issuedCredentials'])
            ->when($request->filled('cari'), function ($query) use ($request) {
                $q = trim((string) $request->query('cari'));
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('sso_id', 'like', "%{$q}%");
                });
            })
            ->when($request->filled('role'), fn ($q) => $q->role((string) $request->query('role')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'user' => new User,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function store(StoreUserRequest $request, SemanticTalentService $semantic, AuditService $audit): RedirectResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data, $semantic) {
            $roleScope = match ($data['role']) {
                'admin' => 'admin',
                'dosen' => 'advisor',
                'mitra' => 'career_partner',
                default => 'student',
            };

            $user = User::create([
                'name' => $data['name'],
                'email' => Str::lower($data['email']),
                'sso_id' => 'ADM-'.strtoupper(Str::random(7)),
                'role_scope' => $roleScope,
                'passkey_enabled' => (bool) ($data['passkey_enabled'] ?? false),
                'password' => Hash::make($data['password']),
            ]);
            $user->syncRoles([$data['role']]);

            if ($data['role'] === 'mahasiswa') {
                $advisor = User::role('dosen')->first();
                $profile = StudentProfile::create([
                    'user_id' => $user->id,
                    'nim' => $data['nim'],
                    'prodi_name' => $data['prodi_name'] ?: 'Sistem Informasi',
                    'faculty_name' => 'Fakultas Ilmu Komputer',
                    'semester' => (int) ($data['semester'] ?? 1),
                    'advisor_id' => $advisor?->id,
                    'public_slug' => Str::slug($data['name']).'-'.strtolower(Str::random(4)),
                    'is_public_enabled' => false,
                    'skills_json' => [],
                ]);
                $semantic->refreshStudentEmbedding($profile);
            }

            return $user;
        });

        $audit->log('admin.user_created', $user, ['role' => $data['role'], 'email' => $user->email]);

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$user->name} (Role: {$data['role']}) berhasil ditambahkan.");
    }

    public function show(User $user): View
    {
        $user->load(['roles.permissions', 'studentProfile.advisor', 'activities.category', 'issuedCredentials']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $user->load(['roles', 'studentProfile']);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, AuditService $audit): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($user, $data) {
            $roleScope = match ($data['role']) {
                'admin' => 'admin',
                'dosen' => 'advisor',
                'mitra' => 'career_partner',
                default => 'student',
            };

            $payload = [
                'name' => $data['name'],
                'email' => Str::lower($data['email']),
                'role_scope' => $roleScope,
                'passkey_enabled' => (bool) ($data['passkey_enabled'] ?? false),
            ];
            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->update($payload);
            $user->syncRoles([$data['role']]);

            if ($data['role'] === 'mahasiswa') {
                $advisor = User::role('dosen')->first();
                $user->studentProfile()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nim' => $data['nim'],
                        'prodi_name' => $data['prodi_name'] ?: 'Sistem Informasi',
                        'faculty_name' => 'Fakultas Ilmu Komputer',
                        'semester' => (int) ($data['semester'] ?? 1),
                        'advisor_id' => $user->studentProfile?->advisor_id ?? $advisor?->id,
                        'public_slug' => $user->studentProfile?->public_slug ?? (Str::slug($data['name']).'-'.strtolower(Str::random(4))),
                    ]
                );
            }
        });

        $audit->log('admin.user_updated', $user, ['role' => $data['role'], 'email' => $user->email]);

        return redirect()->route('admin.users.index')
            ->with('success', "Data akun {$user->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user, AuditService $audit): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan login.');
        }

        $email = $user->email;
        $user->tokens()->delete();
        $user->delete();

        $audit->log('admin.user_deleted', null, ['deleted_email' => $email]);

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$email} berhasil dihapus dari database.");
    }
}
