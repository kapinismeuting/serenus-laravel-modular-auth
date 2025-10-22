<?php

use Illuminate\Support\Facades\Route;
use Serenus\ModularAuth\Http\Controllers\Web\AuthController;

// Jika Anda menggunakan Livewire Components,
// Anda bisa membuat route ini langsung ke component
// Route::get('/login', Login::class)->name('login');

// Socialite Web
Route::get('auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('google.callback');

// Logout Web
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
