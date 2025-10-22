<?php

namespace Serenus\ModularAuth\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User; 
use Illuminate\Support\Facades\Hash;
use Serenus\ModularAuth\Http\Requests\RegisterRequest;

class RegisterController extends Controller
{
    /**
     * Tangani permintaan pendaftaran pengguna baru.
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), 
        ]);

        $user->sendEmailVerificationNotification();        
        $user->assignRole('user');
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
        ], 201); 
    }
}
