<?

namespace Serenus\ModularAuth\Http\Controllers\Api;

use App\Http\Controllers\Controller
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Serenus\ModularAuth\Http\Requests\LoginRequest

class LoginController extends Controller
{
    /**
     * Tangani permintaan login pengguna.
     */
    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {

            return response()->json([
                'message' => 'Kredensial yang diberikan tidak cocok dengan catatan kami.'
            ], 401
        }

        $user = $request->user();

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
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout. Token telah dicabut.'
        ], 200);
    }
}
