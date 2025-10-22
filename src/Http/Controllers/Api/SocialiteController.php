<?php
// Modules/Auth/Http/Controllers/Api/SocialiteController.php

namespace Serenus\ModularAuth\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialiteController extends Controller
{
    /**
     * Redirect pengguna ke halaman otentikasi Google.
     * Endpoint: GET /api/v1/auth/google/redirect
     */
    public function redirectToProvider(Request $request)
    {
        // Socialite secara otomatis akan membaca konfigurasi 'google' dari config/services.php
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Tangani callback dari Google dan lakukan login/registrasi.
     * Endpoint: GET /api/v1/auth/google/callback
     */
    public function handleProviderCallback(Request $request)
    {
        try {
            // Menggunakan stateless() karena ini adalah API
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (Exception $e) {
            // Gagal otentikasi dari Google (misalnya, pengguna menolak izin)
            return response()->json(['message' => 'Gagal otentikasi dengan Google.', 'error' => $e->getMessage()], 401);
        }

        // Alur Kerja Baru:
        // 1. Cari user berdasarkan google_id
        $user = User::where('google_id', $googleUser->getId())->first();

        if (!$user) {
            // 2. Jika tidak ada, cari berdasarkan email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // 3. Jika ditemukan via email, update akunnya dengan google_id
                $user->update(['google_id' => $googleUser->getId(), 'avatar' => $googleUser->getAvatar()]);
            } else {
                // 4. Jika tidak ada sama sekali, buat user baru
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => Hash::make(uniqid()), // Berikan password acak yang tidak akan digunakan
                    'email_verified_at' => now(), // Verifikasi email otomatis dari Google
                ]);

                // Berikan role default 'user'
                $user->assignRole('user');
            }
        }

        // Terbitkan Sanctum Token
        $token = $user->createToken('GoogleAuthToken')->plainTextToken;

        // KARENA INI API, KITA TIDAK BISA MELAKUKAN REDIRECT KE FRONTEND DENGAN TOKEN DI HEADER.
        // Kita akan menggunakan redirect ke URL frontend yang telah ditentukan
        // dan mengirimkan token sebagai parameter kueri.

        $frontendUrl = $request->query('redirect_url')
            ?? env('FRONTEND_URL')
            ?? '/';

        if ($frontendUrl === '/') {
            // Redirect relatif ke root path Laravel, token/user_id bisa diabaikan atau ditambahkan di fragment (#)
            // Kami akan menggunakan query string agar mudah diakses di browser, meski ini bukan praktik terbaik untuk token.
            return redirect("{$frontendUrl}?token={$token}&user_id={$user->id}");
        }

        // Redirect ke URL frontend dengan token dan user_id di parameter kueri
        return redirect()->away("{$frontendUrl}/auth/callback?token={$token}&user_id={$user->id}");
    }
}
