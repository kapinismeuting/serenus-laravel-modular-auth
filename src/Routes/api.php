<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\RoleController;
use Modules\Auth\Http\Controllers\Api\UserController;
use Modules\Auth\Http\Controllers\Api\LoginController;
use Modules\Auth\Http\Controllers\Api\RegisterController;
use Modules\Auth\Http\Controllers\Api\SocialiteController;
use Modules\Auth\Http\Controllers\Api\PermissionController;
use Modules\Auth\Http\Controllers\Api\UserManagerController;
use Modules\Auth\Http\Controllers\Api\ForgotPasswordController;

Route::middleware('verify.client')->group(function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset']);
});
Route::group(['prefix' => 'auth/google'], function () {
    Route::get('/redirect', [SocialiteController::class, 'redirectToProvider']);
    Route::get('/callback', [SocialiteController::class, 'handleProviderCallback']);
});

Route::middleware(['auth:api', 'verified'])->group(function () {
    Route::get('/user', [UserController::class, 'show']);    
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::group(['middleware' => ['permission:manage-roles']], function () {
        Route::apiResource('roles', RoleController::class);
        Route::post('roles/{role}/permissions', [RoleController::class, 'assignPermission']);
    });

    Route::group(['middleware' => ['permission:manage-permissions']], function () {
        Route::get('permissions', [PermissionController::class, 'index']);
        Route::post('permissions/sync/{role}', [PermissionController::class, 'syncPermissionsToRole']);
    });

    Route::middleware('permission:manage-users')->group(function () {
        Route::get('/users', [UserManagerController::class, 'index']);
        Route::get('/users/{user}', [UserManagerController::class, 'show']);
    });

    Route::post('/users', [UserManagerController::class, 'store'])->middleware('permission:create-users');
    Route::put('/users/{user}', [UserManagerController::class, 'update'])->middleware('permission:edit-users');
    Route::delete('/users/{user}', [UserManagerController::class, 'destroy'])->middleware('permission:delete-users');
});
