<?php
// Modules/Auth/Http/Controllers/Api/UserController.php

namespace Serenus\ModularAuth\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Dapatkan detail pengguna yang sedang login.
     */
    public function show(Request $request)
    {
        // Karena route ini berada di bawah middleware 'auth:sanctum',
        // objek $request akan memiliki user yang sudah terotentikasi.

        return response()->json([
            'message' => 'Detail pengguna berhasil diambil.',
            'user' => $request->user(),
        ], 200);
    }
}
