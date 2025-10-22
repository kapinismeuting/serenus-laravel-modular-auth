<?php
// Modules/Auth/Http/Requests/RegisterRequest.php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true; // Izinkan semua orang untuk mengakses request registrasi
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'], // Harus unik di tabel 'users'
            'password' => ['required', 'confirmed', Password::defaults()], // 'confirmed' membutuhkan field 'password_confirmation'
        ];
    }
}
