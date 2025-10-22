<?php
// Modules/Auth/Http/Controllers/Api/LoginController.php

namespace Serenus\ModularAuth\Http\Controllers\Api;

use App\Http\Controllers\Controller; // Gunakan base controller Anda
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Serenus\ModularAuth\Http\Requests\LoginRequest; // Import request validasi

class LoginController extends Controller
{
    /**
     * Tangani permintaan login pengguna.
     */
    public function login(LoginRequest $request)
    {
        // Kredensial telah divalidasi oleh LoginRequest

        if (!Auth::attempt($request->only('email', 'password'))) {
            // Gagal otentikasi
            return response()->json([
                'message' => 'Kredensial yang diberikan tidak cocok dengan catatan kami.'
            ], 401); // 401 Unauthorized
        }

        // Otentikasi Berhasil
        $user = $request->user();

        // **Membuat Token Sanctum**
        // 'AuthToken' adalah nama token, Anda bisa menggunakan nama lain
        $token = $user->createToken('AuthToken')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    public function logout(Request $request)
    {
        // Hapus token API saat ini yang digunakan untuk permintaan.
        // Hanya token yang sedang digunakan (currentAccessToken) yang dihapus.
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout. Token telah dicabut.'
        ], 200);
    }
}
