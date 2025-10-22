<?php
// Modules/Auth/Http/Controllers/Web/AuthController.php

namespace Modules\Auth\Http\Controllers\Web; // <-- Namespace Web

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class AuthController extends Controller
{
    // ------------------------------------------
    // 1. Socialite Session-Based
    // ------------------------------------------

    public function redirectToGoogle()
    {
        // PENTING: JANGAN gunakan stateless()
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Exception $e) {
            // Jika gagal, redirect ke halaman login Livewire/Web
            return redirect('/login')->withErrors(['google' => 'Gagal otentikasi Google.']);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            // Registrasi User Baru
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(uniqid()),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('user');
        }

        // Login berbasis Sesi
        Auth::login($user);

        // Redirect ke URL tujuan (misalnya dashboard Livewire)
        return redirect()->intended('/dashboard');
    }

    // ------------------------------------------
    // 2. Logout Session-Based
    // ------------------------------------------

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/'); // Redirect ke halaman welcome
    }
}
