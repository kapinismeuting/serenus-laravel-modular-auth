<?php

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
        
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

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

        Permission::firstOrCreate(['name' => '*']);

        
        $administratorRole = Role::firstOrCreate(['name' => 'administrator']);
        $adminRole         = Role::firstOrCreate(['name' => 'admin']);
        $userRole          = Role::firstOrCreate(['name' => 'user']);

        $administratorRole->givePermissionTo(Permission::all());

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
        
        $userPermission = Permission::where('name', 'view-user-dashboard')->first();
        if ($userPermission) {
            $userRole->givePermissionTo($userPermission);
        }

        $administratorUser = User::firstOrCreate(
            ['email' => 'aswal.awaludin@gmail.com'],
            [
                'name' => 'Aswal Awaludin (Administrator)',
                'password' => Hash::make('ZXasqw12!@'),
            ]
        );
        $administratorUser->assignRole('administrator');

        
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@alifcommunity.com'],
            [
                'name' => 'Admin Alif Community',
                'password' => Hash::make('admin312897'), 
            ]
        );
        
        $adminUser->syncRoles(['admin']);

        
        $normalUser = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Fake User',
                'password' => Hash::make('user312897'),
            ]
        );
        $normalUser->syncRoles(['user']);
    }
}
