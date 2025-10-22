<?php
// Modules/Auth/Http/Controllers/Api/RegisterController.php

namespace Serenus\ModularAuth\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User; // <-- Pastikan Anda mengimport model User default Anda
use Illuminate\Support\Facades\Hash;
use Serenus\ModularAuth\Http\Requests\RegisterRequest; // <-- Import request validasi

class RegisterController extends Controller
{
    /**
     * Tangani permintaan pendaftaran pengguna baru.
     */
    public function register(RegisterRequest $request)
    {
        // Data telah divalidasi oleh RegisterRequest

        // 1. Buat User di database
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Enkripsi password sebelum disimpan
        ]);

        // 2. Kirim email verifikasi
        $user->sendEmailVerificationNotification();

        // 2.1 Assign Default Role
        $user->assignRole('user');

        // 3. Berikan token Sanctum
        $token = $user->createToken('AuthToken')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil. Silakan periksa email Anda untuk verifikasi.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201); // 201 Created
    }
}
