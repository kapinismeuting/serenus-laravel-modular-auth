<?php
// Modules/Auth/AuthServiceProvider.php

namespace Serenus\ModularAuth;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Daftarkan layanan modul.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrapping layanan modul.
     */
    public function boot(): void
    {
        // Panggil metode untuk memuat route
        $this->mapApiRoutes();
        $this->mapWebRoutes();

        // Terapkan Gate untuk Administrator (Wajib!)
        // Ini memastikan Role 'administrator' dapat melewati semua pengecekan Gate.
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('administrator')) {
                return true;
            }
        });
    }

    /**
     * Definisikan route 'api' untuk modul.
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api') // Prefix URL: /api/...
            ->middleware('api') // Gunakan middleware API
            ->namespace('Serenus\ModularAuth\Http\Controllers\Api') // Namespace Controller
            ->group(__DIR__ . '/Routes/api.php'); // Lokasi file route kita
    }

    /**
     * Definisikan route 'web' untuk modul.
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web') // Gunakan middleware Web
            ->namespace('Serenus\ModularAuth\Http\Controllers\Web') // Namespace Controller Web
            ->group(__DIR__ . '/Routes/web.php'); // Lokasi file route kita
    }
}
