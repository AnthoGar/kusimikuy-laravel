<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DonanteDashboardController;

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

// Registro de usuarios.
Route::get('/registro', [AuthController::class, 'showRegister'])
    ->name('register');

Route::post('/registro', [AuthController::class, 'register'])
    ->name('register.store');

// Inicio de sesión
Route::get('/login', [AuthController::class, 'showLogin'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('guest')
    ->name('login.store');

// Cierre de sesión
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:donante'])->group(function () {
    Route::get('/donante/panel', [DonanteDashboardController::class, 'index'])
        ->name('donante.dashboard');
});
