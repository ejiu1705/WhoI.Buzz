<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'sso_id', 'role_scope', 'two_factor_confirmed_at', 'passkey_enabled'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUlids, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'passkey_enabled' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class, 'user_id');
    }

    public function advisees(): HasMany
    {
        return $this->hasMany(StudentProfile::class, 'advisor_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'student_id');
    }

    public function issuedCredentials(): HasMany
    {
        return $this->hasMany(IssuedCredential::class, 'student_id');
    }

    public function hubNotifications(): HasMany
    {
        return $this->hasMany(HubNotification::class, 'user_id')->latest();
    }

    public function primaryRole(): string
    {
        if ($this->relationLoaded('roles') && $this->roles->isNotEmpty()) {
            return (string) $this->roles->first()->name;
        }

        return match ($this->role_scope) {
            'admin' => 'admin',
            'advisor', 'dosen' => 'dosen',
            'career_partner', 'mitra' => 'mitra',
            default => 'mahasiswa',
        };
    }

    public function isStudent(): bool
    {
        return $this->hasRole('mahasiswa') || $this->role_scope === 'student';
    }

    public function isAdvisor(): bool
    {
        return $this->hasRole('dosen') || $this->role_scope === 'advisor';
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->role_scope === 'admin';
    }

    public function isCareerPartner(): bool
    {
        return $this->hasRole('mitra') || $this->role_scope === 'career_partner';
    }

    public function roleLabel(): string
    {
        return match ($this->primaryRole()) {
            'admin' => 'Admin Kemahasiswaan',
            'dosen' => 'Dosen PA / Verifikator',
            'mahasiswa' => 'Mahasiswa',
            'mitra' => 'Mitra Karier / HRD',
            default => ucfirst($this->primaryRole()),
        };
    }

    public function dashboardRoute(): string
    {
        if ($this->hasRole('admin')) {
            return route('admin.dashboard');
        }
        if ($this->hasRole('dosen')) {
            return route('dosen.dashboard');
        }
        if ($this->hasRole('mitra')) {
            return route('mitra.dashboard');
        }

        return route('mahasiswa.dashboard');
    }
}
