<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'kelola pengguna',
            'lihat progres',
            'kelola-kurikulum-skpi',
            'validasi-kegiatan',
            'terbitkan-autoclaim',
            'cabut-kredensial',
            'ajukan-sertifikat',
            'cari-talenta',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $dosen = Role::firstOrCreate(['name' => 'dosen', 'guard_name' => 'web']);
        $mahasiswa = Role::firstOrCreate(['name' => 'mahasiswa', 'guard_name' => 'web']);
        $mitra = Role::firstOrCreate(['name' => 'mitra', 'guard_name' => 'web']);

        $admin->syncPermissions($permissions);

        $dosen->syncPermissions([
            'lihat progres',
            'kelola-kurikulum-skpi',
            'validasi-kegiatan',
        ]);

        $mahasiswa->syncPermissions([
            'ajukan-sertifikat',
        ]);

        $mitra->syncPermissions([
            'cari-talenta',
        ]);
    }
}
