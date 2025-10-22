<?php


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
        return response()->json([
            'message' => 'Detail pengguna berhasil diambil.',
            'user' => $request->user(),
        ], 200);
    }
}
