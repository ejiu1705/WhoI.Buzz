<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // WhoI.Buzz Campus Core Personas
            [
                'email' => 'admin.kemahasiswaan@campus.ac.id',
                'name' => 'Siti Rahmawati, M.Kom.',
                'sso_id' => 'SSO-ADM-001',
                'role_scope' => 'admin',
                'role' => 'admin',
                'passkey_enabled' => true,
                'password' => 'password123',
            ],
            [
                'email' => 'dr.hendrawan@campus.ac.id',
                'name' => 'Dr. Hendrawan Kusuma, M.T.',
                'sso_id' => 'SSO-DSN-042',
                'role_scope' => 'advisor',
                'role' => 'dosen',
                'passkey_enabled' => true,
                'password' => 'password123',
            ],
            [
                'email' => 'dosen2@campus.ac.id',
                'name' => 'Dr. Lestari Wulandari, M.Kom.',
                'sso_id' => 'SSO-DSN-043',
                'role_scope' => 'advisor',
                'role' => 'dosen',
                'passkey_enabled' => true,
                'password' => 'password123',
            ],
            [
                'email' => 'nadia.putri@campus.ac.id',
                'name' => 'Nadia Putri Mahardika',
                'sso_id' => 'SSO-MHS-220101',
                'role_scope' => 'student',
                'role' => 'mahasiswa',
                'passkey_enabled' => false,
                'password' => 'password123',
            ],
            [
                'email' => 'bima.santoso@campus.ac.id',
                'name' => 'Bima Santoso Wijaya',
                'sso_id' => 'SSO-MHS-220102',
                'role_scope' => 'student',
                'role' => 'mahasiswa',
                'passkey_enabled' => false,
                'password' => 'password123',
            ],
            [
                'email' => 'raka.mahendra@campus.ac.id',
                'name' => 'Raka Mahendra Putra',
                'sso_id' => 'SSO-MHS-230205',
                'role_scope' => 'student',
                'role' => 'mahasiswa',
                'passkey_enabled' => false,
                'password' => 'password123',
            ],
            [
                'email' => 'hrd.mitra@techcorp.id',
                'name' => 'Rina Oktavia (TechCorp HRD)',
                'sso_id' => 'PARTNER-HR-88',
                'role_scope' => 'career_partner',
                'role' => 'mitra',
                'passkey_enabled' => false,
                'password' => 'password123',
            ],
        ];

        foreach ($accounts as $acc) {
            $user = User::firstOrCreate(
                ['email' => $acc['email']],
                [
                    'name' => $acc['name'],
                    'sso_id' => $acc['sso_id'],
                    'role_scope' => $acc['role_scope'],
                    'email_verified_at' => now(),
                    'two_factor_confirmed_at' => $acc['passkey_enabled'] ? now() : null,
                    'passkey_enabled' => $acc['passkey_enabled'],
                    'password' => Hash::make($acc['password']),
                ]
            );
            $user->syncRoles([$acc['role']]);
        }
    }
}
