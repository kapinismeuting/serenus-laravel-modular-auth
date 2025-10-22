<?php
// Modules/Auth/Routes/api.php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\RoleController;
use Modules\Auth\Http\Controllers\Api\UserController;
use Modules\Auth\Http\Controllers\Api\LoginController;
use Modules\Auth\Http\Controllers\Api\RegisterController;
use Modules\Auth\Http\Controllers\Api\SocialiteController;
use Modules\Auth\Http\Controllers\Api\PermissionController;
use Modules\Auth\Http\Controllers\Api\UserManagerController;
use Modules\Auth\Http\Controllers\Api\ForgotPasswordController;

// --- Rute Publik yang Dilindungi oleh Client Secret ---
Route::middleware('verify.client')->group(function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset']);
});

// SOCIALITE (GOOGLE LOGIN)
Route::group(['prefix' => 'auth/google'], function () {
    // 1. Endpoint untuk memulai alur otentikasi (Client memanggil ini)
    Route::get('/redirect', [SocialiteController::class, 'redirectToProvider']);

    // 2. Endpoint Callback (Google memanggil ini)
    Route::get('/callback', [SocialiteController::class, 'handleProviderCallback']);
});


// --- Route Terproteksi (Membutuhkan Sanctum Token & Verifikasi Email) ---
Route::middleware(['auth:api', 'verified'])->group(function () {

    // Endpoint untuk mendapatkan detail pengguna
    Route::get('/user', [UserController::class, 'show']);

    // Endpoint untuk Logout (menghapus token)
    Route::post('/logout', [LoginController::class, 'logout']);

    // Endpoint untuk Manajemen Roles (dilindungi oleh izin)
    Route::group(['middleware' => ['permission:manage-roles']], function () {
        Route::apiResource('roles', RoleController::class);
        Route::post('roles/{role}/permissions', [RoleController::class, 'assignPermission']);
    });

    // Endpoint untuk Manajemen Permissions (dilindungi oleh izin)
    Route::group(['middleware' => ['permission:manage-permissions']], function () {
        Route::get('permissions', [PermissionController::class, 'index']);
        Route::post('permissions/sync/{role}', [PermissionController::class, 'syncPermissionsToRole']);
    });

    // ====================================================================
    // USER MANAGEMENT CRUD (DILINDUNGI SPATIE)
    // ====================================================================
    Route::middleware('permission:manage-users')->group(function () {
        // GET /api/v1/users (index) dan GET /api/v1/users/{user} (show)
        Route::get('/users', [UserManagerController::class, 'index']);
        Route::get('/users/{user}', [UserManagerController::class, 'show']);
    });

    Route::post('/users', [UserManagerController::class, 'store'])->middleware('permission:create-users');
    Route::put('/users/{user}', [UserManagerController::class, 'update'])->middleware('permission:edit-users');
    Route::delete('/users/{user}', [UserManagerController::class, 'destroy'])->middleware('permission:delete-users');
});
