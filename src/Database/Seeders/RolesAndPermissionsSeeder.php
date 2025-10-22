<?php
// database/seeders/RolesAndPermissionsSeeder.php

namespace Serenus\ModularAuth\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Jalankan database seeders.
     */
    public function run(): void
    {
        // 1. Reset cache roles dan permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Buat Permissions Dasar (Contoh)
        $permissions = [
            'manage-roles',
            'manage-permissions',
            'view-admin-dashboard',
            'manage-users',
            'view-user-dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Tambahkan Wildcard Permission untuk Administrator
        Permission::firstOrCreate(['name' => '*']);

        // 3. Buat Roles
        $administratorRole = Role::firstOrCreate(['name' => 'administrator']);
        $adminRole         = Role::firstOrCreate(['name' => 'admin']);
        $userRole          = Role::firstOrCreate(['name' => 'user']);

        // 4. Assign Permissions ke Roles

        // Administrator: Mendapatkan semua permissions, termasuk wildcard '*'
        $administratorRole->givePermissionTo(Permission::all());

        // Admin: Dapatkan beberapa permissions spesifik
        $adminPermissions = Permission::whereIn('name', [
            'manage-roles',
            'manage-permissions',
            'view-admin-dashboard',
            'manage-users',
            'create-users',
            'edit-users',
            'delete-users',
            'view-user-dashboard',
        ])->get();
        $adminRole->givePermissionTo($adminPermissions);

        // User: Hanya izin dasar
        $userPermission = Permission::where('name', 'view-user-dashboard')->first();
        if ($userPermission) {
            $userRole->givePermissionTo($userPermission);
        }

        // 5. Buat atau Update User Default

        // --- User 1: Administrator ---
        $administratorUser = User::firstOrCreate(
            ['email' => 'aswal.awaludin@gmail.com'],
            [
                'name' => 'Aswal Awaludin (Administrator)',
                'password' => Hash::make('ZXasqw12!@'),
            ]
        );
        $administratorUser->assignRole('administrator');

        // --- User 2: Admin ---
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@alifcommunity.com'],
            [
                'name' => 'Admin Alif Community',
                'password' => Hash::make('admin312897'), // Ganti dengan password kuat sesuai kebutuhan
            ]
        );
        // Pastikan role lama dicabut sebelum memberikan role baru (opsional, tapi aman)
        $adminUser->syncRoles(['admin']);

        // --- User 3: User Biasa (Fake User) ---
        $normalUser = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Fake User',
                'password' => Hash::make('user312897'),
            ]
        );
        $normalUser->syncRoles(['user']);

        // 6. Terapkan Gate untuk Administrator (Wajib!)
        // Ini memastikan Role 'administrator' dapat melewati semua pengecekan Gate.
        // Logika ini dipindahkan ke AuthServiceProvider.
    }
}
