# Serenus Laravel Modular Auth

Modul Otentikasi dan Otorisasi yang dapat digunakan kembali untuk Laravel. Modul ini menyediakan fungsionalitas lengkap untuk manajemen pengguna, peran, izin, serta integrasi Socialite (Google Login) dalam arsitektur modular.

## Daftar Isi
1.  [Pendahuluan](#1-pendahuluan)
2.  [Fitur](#2-fitur)
3.  [Instalasi](#3-instalasi)
4.  [Konfigurasi](#4-konfigurasi)
5.  [Rute API](#5-rute-api)
    *   [Rute Publik (Dilindungi Client Secret)](#51-rute-publik-dilindungi-client-secret)
    *   [Socialite (Google Login) API](#52-socialite-google-login-api)
    *   [Rute Terproteksi (Membutuhkan Sanctum Token & Verifikasi Email)](#53-rute-terproteksi-membutuhkan-sanctum-token--verifikasi-email)
    *   [Manajemen Roles](#54-manajemen-roles)
    *   [Manajemen Permissions](#55-manajemen-permissions)
    *   [Manajemen Pengguna (CRUD)](#56-manajemen-pengguna-crud)
6.  [Rute Web](#6-rute-web)
    *   [Socialite (Google Login) Web](#61-socialite-google-login-web)
    *   [Logout Web](#62-logout-web)
7.  [Gate Administrator](#7-gate-administrator)
8.  [Lisensi](#8-lisensi)

---

## 1. Pendahuluan
`serenus-laravel-modular-auth` adalah modul Laravel yang dirancang untuk menyediakan solusi otentikasi dan otorisasi yang komprehensif dan dapat digunakan kembali. Modul ini mengintegrasikan paket-paket populer seperti Laravel Sanctum untuk otentikasi API, Spatie Laravel Permission untuk manajemen peran dan izin, serta Laravel Socialite untuk otentikasi pihak ketiga (saat ini Google).

## 2. Fitur
*   **Registrasi Pengguna**: Pendaftaran pengguna baru dengan validasi.
*   **Login Pengguna**: Otentikasi pengguna dan pembuatan token Sanctum.
*   **Lupa Kata Sandi**: Alur reset kata sandi melalui email.
*   **Verifikasi Email**: Memastikan pengguna memverifikasi alamat email mereka.
*   **Google Socialite**: Integrasi login menggunakan akun Google.
*   **Manajemen Peran & Izin**: Mengelola peran dan izin pengguna menggunakan Spatie Laravel Permission.
*   **Manajemen Pengguna (CRUD)**: Endpoint API untuk membuat, membaca, memperbarui, dan menghapus pengguna.
*   **Gate Administrator**: Mekanisme untuk memberikan akses penuh kepada pengguna dengan peran 'administrator' ke semua Gate.
*   **Struktur Modular**: Dirancang sebagai modul yang dapat dipasang dan digunakan kembali di berbagai proyek Laravel.

## 3. Instalasi
1.  **Tambahkan Modul ke `composer.json` Proyek Utama Anda**:
    Jika Anda mengunggah modul ini ke Packagist, Anda dapat menambahkannya sebagai dependensi biasa. Jika tidak, Anda mungkin perlu menambahkannya sebagai repositori Path atau VCS di `composer.json` proyek utama Anda.

    ```json
    // composer.json proyek utama Anda
    {
        "repositories": [
            {
                "type": "path",
                "url": "./modules/serenus-laravel-modular-auth" // Sesuaikan path jika modul berada di lokasi lain
            }
        ],
        "require": {
            "serenus/serenus-laravel-modular-auth": "@dev" // Atau versi stabil jika sudah ada
        }
    }
    ```

2.  **Jalankan `composer update`**:
    ```bash
    composer update
    ```

3.  **Publikasikan Migrasi dan Konfigurasi (jika diperlukan)**:
    Modul ini bergantung pada `laravel/sanctum`, `spatie/laravel-permission`, dan `laravel/socialite`. Pastikan Anda telah mempublikasikan dan menjalankan migrasi untuk paket-paket ini di proyek utama Anda.

    *   **Sanctum**:
        ```bash
        php artisan vendor:publish --tag="sanctum-migrations"
        ```
    *   **Spatie Laravel Permission**:
        ```bash
        php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="permission-migrations"
        php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="permission-config"
        ```
    *   **Socialite**:
        ```bash
        php artisan vendor:publish --provider="Laravel\Socialite\SocialiteServiceProvider"
        ```

4.  **Jalankan Migrasi**:
    ```bash
    php artisan migrate
    ```

## 4. Konfigurasi

### 4.1. Konfigurasi `.env`
Pastikan Anda memiliki konfigurasi berikut di file `.env` proyek utama Anda:

```dotenv
# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1

# Socialite Google
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback # Sesuaikan dengan URL callback API Anda
GOOGLE_REDIRECT_URI_WEB=http://localhost:8000/auth/google/callback # Sesuaikan dengan URL callback Web Anda

# Client Secret (untuk rute publik yang dilindungi)
CLIENT_SECRET=your-secure-client-secret # Gunakan string acak yang kuat
```

### 4.2. Konfigurasi `config/auth.php`
Pastikan guard `sanctum` dan provider `users` dikonfigurasi dengan benar.

### 4.3. Konfigurasi `config/services.php`
Tambahkan konfigurasi untuk Google Socialite:

```php
// config/services.php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],
```

### 4.4. Middleware `verify.client`
Modul ini menggunakan middleware `verify.client` untuk melindungi rute publik tertentu. Anda perlu mendaftarkan middleware ini di `app/Http/Kernel.php` proyek utama Anda:

```php
// app/Http/Kernel.php

protected $middlewareAliases = [
    // ...
    'verify.client' => \App\Http\Middleware\VerifyClientSecret::class, // Buat middleware ini di proyek utama Anda
];
```

Contoh implementasi `App\Http\Middleware\VerifyClientSecret`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyClientSecret
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clientSecret = $request->header('X-Client-Secret'); // Atau dari body/query parameter

        if (!$clientSecret || $clientSecret !== env('CLIENT_SECRET')) {
            return response()->json(['message' => 'Unauthorized: Invalid Client Secret'], 401);
        }

        return $next($request);
    }
}
```

## 5. Rute API
Semua rute API di-prefix dengan `/api` oleh `AuthServiceProvider`.

### 5.1. Rute Publik (Dilindungi Client Secret)
Middleware: `verify.client` (membutuhkan header `X-Client-Secret` yang cocok dengan `CLIENT_SECRET` di `.env`)

*   **`POST /api/register`**
    *   **Controller**: `RegisterController@register`
    *   **Deskripsi**: Mendaftarkan pengguna baru.
    *   **Parameter Body**:
        *   `name` (string, required)
        *   `email` (string, required, unique, email)
        *   `password` (string, required, min:8, confirmed)
        *   `password_confirmation` (string, required)
    *   **Respons**: Token Sanctum dan detail pengguna.

*   **`POST /api/login`**
    *   **Controller**: `LoginController@login`
    *   **Deskripsi**: Mengotentikasi pengguna dan mengembalikan token Sanctum.
    *   **Parameter Body**:
        *   `email` (string, required, email)
        *   `password` (string, required)
    *   **Respons**: Token Sanctum dan detail pengguna.

*   **`POST /api/forgot-password`**
    *   **Controller**: `ForgotPasswordController@sendResetLinkEmail`
    *   **Deskripsi**: Mengirim tautan reset kata sandi ke email pengguna.
    *   **Parameter Body**:
        *   `email` (string, required, email)
    *   **Respons**: Pesan sukses.

*   **`POST /api/reset-password`**
    *   **Controller**: `ForgotPasswordController@reset`
    *   **Deskripsi**: Mereset kata sandi pengguna menggunakan token reset.
    *   **Parameter Body**:
        *   `token` (string, required)
        *   `email` (string, required, email)
        *   `password` (string, required, min:8, confirmed)
        *   `password_confirmation` (string, required)
    *   **Respons**: Pesan sukses.

### 5.2. Socialite (Google Login) API
Prefix: `/api/auth/google`

*   **`GET /api/auth/google/redirect`**
    *   **Controller**: `SocialiteController@redirectToProvider`
    *   **Deskripsi**: Mengarahkan pengguna ke halaman login Google.
    *   **Respons**: Redirect ke Google.

*   **`GET /api/auth/google/callback`**
    *   **Controller**: `SocialiteController@handleProviderCallback`
    *   **Deskripsi**: Callback dari Google setelah otentikasi. Membuat/login pengguna dan mengembalikan token Sanctum.
    *   **Respons**: Token Sanctum dan detail pengguna.

### 5.3. Rute Terproteksi (Membutuhkan Sanctum Token & Verifikasi Email)
Middleware: `auth:api`, `verified`

*   **`GET /api/user`**
    *   **Controller**: `UserController@show`
    *   **Deskripsi**: Mendapatkan detail pengguna yang sedang login.
    *   **Respons**: Detail pengguna.

*   **`POST /api/logout`**
    *   **Controller**: `LoginController@logout`
    *   **Deskripsi**: Menghapus token Sanctum pengguna yang sedang login.
    *   **Respons**: Pesan sukses.

### 5.4. Manajemen Roles
Middleware: `auth:api`, `verified`, `permission:manage-roles`
Prefix: `/api/roles`

*   **`GET /api/roles`**
    *   **Controller**: `RoleController@index`
    *   **Deskripsi**: Mendapatkan daftar semua peran.
    *   **Respons**: Daftar peran.

*   **`POST /api/roles`**
    *   **Controller**: `RoleController@store`
    *   **Deskripsi**: Membuat peran baru.
    *   **Parameter Body**:
        *   `name` (string, required, unique)
    *   **Respons**: Peran yang baru dibuat.

*   **`GET /api/roles/{role}`**
    *   **Controller**: `RoleController@show`
    *   **Deskripsi**: Mendapatkan detail peran tertentu.
    *   **Respons**: Detail peran.

*   **`PUT /api/roles/{role}`**
    *   **Controller**: `RoleController@update`
    *   **Deskripsi**: Memperbarui peran tertentu.
    *   **Parameter Body**:
        *   `name` (string, required, unique)
    *   **Respons**: Peran yang diperbarui.

*   **`DELETE /api/roles/{role}`**
    *   **Controller**: `RoleController@destroy`
    *   **Deskripsi**: Menghapus peran tertentu.
    *   **Respons**: Pesan sukses.

*   **`POST /api/roles/{role}/permissions`**
    *   **Controller**: `RoleController@assignPermission`
    *   **Deskripsi**: Menetapkan izin ke peran.
    *   **Parameter Body**:
        *   `permissions` (array, required): Array nama izin.
    *   **Respons**: Peran dengan izin yang diperbarui.

### 5.5. Manajemen Permissions
Middleware: `auth:api`, `verified`, `permission:manage-permissions`

*   **`GET /api/permissions`**
    *   **Controller**: `PermissionController@index`
    *   **Deskripsi**: Mendapatkan daftar semua izin.
    *   **Respons**: Daftar izin.

*   **`POST /api/permissions/sync/{role}`**
    *   **Controller**: `PermissionController@syncPermissionsToRole`
    *   **Deskripsi**: Menyinkronkan izin ke peran tertentu.
    *   **Parameter Body**:
        *   `permissions` (array, required): Array nama izin.
    *   **Respons**: Peran dengan izin yang disinkronkan.

### 5.6. Manajemen Pengguna (CRUD)
Middleware: `auth:api`, `verified`
*Catatan: Setiap operasi CRUD pengguna memiliki izin spesifik yang diperlukan.*

*   **`GET /api/users`**
    *   **Controller**: `UserManagerController@index`
    *   **Middleware**: `permission:manage-users`
    *   **Deskripsi**: Mendapatkan daftar semua pengguna.
    *   **Respons**: Daftar pengguna.

*   **`GET /api/users/{user}`**
    *   **Controller**: `UserManagerController@show`
    *   **Middleware**: `permission:manage-users`
    *   **Deskripsi**: Mendapatkan detail pengguna tertentu.
    *   **Respons**: Detail pengguna.

*   **`POST /api/users`**
    *   **Controller**: `UserManagerController@store`
    *   **Middleware**: `permission:create-users`
    *   **Deskripsi**: Membuat pengguna baru.
    *   **Parameter Body**:
        *   `name` (string, required)
        *   `email` (string, required, unique, email)
        *   `password` (string, required, min:8, confirmed)
        *   `password_confirmation` (string, required)
        *   `roles` (array, optional): Array nama peran untuk ditetapkan.
    *   **Respons**: Pengguna yang baru dibuat.

*   **`PUT /api/users/{user}`**
    *   **Controller**: `UserManagerController@update`
    *   **Middleware**: `permission:edit-users`
    *   **Deskripsi**: Memperbarui detail pengguna tertentu.
    *   **Parameter Body**:
        *   `name` (string, optional)
        *   `email` (string, optional, unique, email)
        *   `password` (string, optional, min:8, confirmed)
        *   `password_confirmation` (string, optional)
        *   `roles` (array, optional): Array nama peran untuk ditetapkan/disinkronkan.
    *   **Respons**: Pengguna yang diperbarui.

*   **`DELETE /api/users/{user}`**
    *   **Controller**: `UserManagerController@destroy`
    *   **Middleware**: `permission:delete-users`
    *   **Deskripsi**: Menghapus pengguna tertentu.
    *   **Respons**: Pesan sukses.

## 6. Rute Web
### 6.1. Socialite (Google Login) Web
*   **`GET /auth/google/redirect`**
    *   **Controller**: `AuthController@redirectToGoogle`
    *   **Nama Rute**: `google.redirect`
    *   **Deskripsi**: Mengarahkan pengguna ke halaman login Google untuk otentikasi berbasis web.
    *   **Respons**: Redirect ke Google.

*   **`GET /auth/google/callback`**
    *   **Controller**: `AuthController@handleGoogleCallback`
    *   **Nama Rute**: `google.callback`
    *   **Deskripsi**: Callback dari Google setelah otentikasi web. Membuat/login pengguna dan mengarahkan kembali ke aplikasi.
    *   **Respons**: Redirect ke halaman yang ditentukan setelah login.

### 6.2. Logout Web
*   **`POST /logout`**
    *   **Controller**: `AuthController@logout`
    *   **Middleware**: `auth`
    *   **Nama Rute**: `logout`
    *   **Deskripsi**: Mengeluarkan pengguna dari sesi web.
    *   **Respons**: Redirect ke halaman login atau beranda.

## 7. Gate Administrator
Modul ini mengimplementasikan Gate `before` di `AuthServiceProvider` yang secara otomatis memberikan akses `true` untuk setiap kemampuan (ability) kepada pengguna yang memiliki peran `administrator`.

```php
Gate::before(function (User $user, string $ability) {
    if ($user->hasRole('administrator')) {
        return true;
    }
});
```
Ini berarti setiap pengguna dengan peran `administrator` akan melewati semua pengecekan Gate/Permission di aplikasi Anda. Pastikan Anda memiliki peran 'administrator' yang dibuat dan ditetapkan kepada pengguna yang sesuai.

## 8. Lisensi
Modul ini dilisensikan di bawah Lisensi MIT. Lihat file `LICENSE` untuk detail lebih lanjut.
