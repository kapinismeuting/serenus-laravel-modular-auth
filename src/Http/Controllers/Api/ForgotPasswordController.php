<?

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

        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));


        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Tautan reset kata sandi telah dikirim ke email Anda.'
            ], 200);
        }

        return response()->json([
            'message' => 'Kami tidak dapat menemukan pengguna dengan alamat email tersebut.'
        ], 422);
    }

    /**
     * Mereset kata sandi pengguna.
     */
    public function reset(Request $request)
    {

        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRules::defaults()],
        ]);

        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, $password) {

            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(Str::random(60));

            $user->save();
        });

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Kata sandi Anda telah berhasil direset.'
            ], 200);
        }

        return response()->json([
            'message' => 'Token reset kata sandi ini tidak valid.'
        ], 422);
    }
}
