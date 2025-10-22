<?php

namespace Serenus\ModularAuth\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{

    /**
     * Menampilkan daftar semua permission.
     */
    public function index()
    {
        
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('-', $permission->name)[0];
        });

        return response()->json($permissions);
    }

    /**
     * Sinkronisasi permissions untuk sebuah role.
     */
    public function syncPermissionsToRole(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name', 
        ]);

        
        if ($role->name === 'administrator') {
            return response()->json(['message' => 'Izin untuk Administrator tidak dapat diubah.'], 403);
        }

        $role->syncPermissions($request->permissions);

        return response()->json([
            'message' => 'Izin untuk role ' . $role->name . ' telah disinkronkan.',
            'role' => $role->load('permissions'),
        ]);
    }
}
