<?php

namespace Serenus\ModularAuth\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{

    /**
     * Menampilkan daftar semua role.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    /**
     * Menyimpan role baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        $role = Role::create(['name' => $request->name]);

        return response()->json([
            'message' => 'Role berhasil dibuat.',
            'role' => $role,
        ], 201);
    }

    /**
     * Menampilkan detail satu role.
     */
    public function show(Role $role)
    {
        return response()->json($role->load('permissions'));
    }

    /**
     * Memperbarui role yang ada.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ]);

        $role->update(['name' => $request->name]);

        return response()->json([
            'message' => 'Role berhasil diperbarui.',
            'role' => $role,
        ]);
    }

    /**
     * Menghapus role.
     */
    public function destroy(Role $role)
    {
        
        if ($role->name === 'administrator') {
            return response()->json(['message' => 'Role Administrator tidak dapat dihapus.'], 403);
        }

        $role->delete();

        return response()->json(['message' => 'Role berhasil dihapus.']);
    }

    /**
     * Memberikan izin ke role.
     */
    public function assignPermission(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name', 
        ]);

        $permissions = Permission::whereIn('name', $request->permissions)->get();
        $role->givePermissionTo($permissions);

        return response()->json([
            'message' => 'Izin berhasil diberikan.',
            'role' => $role->load('permissions'),
        ]);
    }
}
