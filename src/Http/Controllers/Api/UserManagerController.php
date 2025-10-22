<?php

namespace Serenus\ModularAuth\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class UserManagerController extends Controller
{
    /**
     * Tampilkan daftar User (READ: List)
     */
    public function index(Request $request)
    {
        $users = User::paginate(15);

        return response()->json($users);
    }

    /**
     * Simpan User baru (CREATE)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()],
            'role' => ['nullable', 'string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->filled('role')) {
            $user->assignRole($request->role);
        } else {
            $user->assignRole('user');
        }

        return response()->json(['message' => 'User berhasil dibuat.', 'user' => $user], 201);
    }

    /**
     * Tampilkan detail User (READ: Detail)
     */
    public function show(User $user)
    {
        $user->load('roles', 'permissions');

        return response()->json($user);
    }

    /**
     * Update User yang ditentukan (UPDATE)
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', Password::defaults()],
            'role' => ['nullable', 'string', 'exists:roles,name'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
        ]);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return response()->json(['message' => 'User berhasil diperbarui.', 'user' => $user]);
    }

    /**
     * Hapus User yang ditentukan (DELETE)
     */
    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id || $user->hasRole('administrator')) {
            return response()->json(['message' => 'Tidak diizinkan menghapus user ini.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus.'], 204); // 204 No Content
    }
}
