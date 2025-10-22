<?php

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
        $this->mapApiRoutes();
        $this->mapWebRoutes();

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
        Route::prefix('api')
            ->middleware('api')
            ->namespace('Serenus\ModularAuth\Http\Controllers\Api')
            ->group(__DIR__ . '/Routes/api.php');
    }

    /**
     * Definisikan route 'web' untuk modul.
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace('Serenus\ModularAuth\Http\Controllers\Web')
            ->group(__DIR__ . '/Routes/web.php');
    }
}
