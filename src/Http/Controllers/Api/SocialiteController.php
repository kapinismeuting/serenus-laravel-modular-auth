<?php

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
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Tangani callback dari Google dan lakukan login/registrasi.
     * Endpoint: GET /api/v1/auth/google/callback
     */
    public function handleProviderCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal otentikasi dengan Google.', 'error' => $e->getMessage()], 401);
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if (!$user) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                $user->update(['google_id' => $googleUser->getId(), 'avatar' => $googleUser->getAvatar()]);
            } else {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => Hash::make(uniqid()),
                    'email_verified_at' => now(),
                ]);

                $user->assignRole('user');
            }
        }

        $token = $user->createToken('GoogleAuthToken')->plainTextToken;


        $frontendUrl = $request->query('redirect_url')
            ?? env('FRONTEND_URL')
            ?? '/';

        if ($frontendUrl === '/') {
            return redirect("{$frontendUrl}?token={$token}&user_id={$user->id}");
        }

        return redirect()->away("{$frontendUrl}/auth/callback?token={$token}&user_id={$user->id}");
    }
}
