<?php
// Modules/Auth/Http/Controllers/Api/ForgotPasswordController.php

namespace Serenus\ModularAuth\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRules;


class ForgotPasswordController extends Controller
{
    /**
     * Menangani permintaan untuk mengirim tautan reset kata sandi.
     */
    public function sendResetLinkEmail(Request $request)
    {
        // 1. Validasi email
        $request->validate(['email' => 'required|email']);

        // 2. Kirim tautan reset menggunakan Password Broker bawaan Laravel
        $status = Password::sendResetLink($request->only('email'));

        // 3. Periksa status dan berikan respons
        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Tautan reset kata sandi telah dikirim ke email Anda.'
            ], 200);
        }

        // Jika pengguna tidak ditemukan, statusnya adalah INVALID_USER
        return response()->json([
            'message' => 'Kami tidak dapat menemukan pengguna dengan alamat email tersebut.'
        ], 422);
    }

    /**
     * Mereset kata sandi pengguna.
     */
    public function reset(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRules::defaults()],
        ]);

        // 2. Coba reset kata sandi menggunakan Password Broker
        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, $password) {
            // Callback ini akan dieksekusi jika token dan email valid
            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(Str::random(60));

            $user->save();
        });

        // 3. Periksa status dan berikan respons
        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Kata sandi Anda telah berhasil direset.'
            ], 200);
        }

        // Jika token tidak valid
        return response()->json([
            'message' => 'Token reset kata sandi ini tidak valid.'
        ], 422);
    }
}
