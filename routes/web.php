<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DonanteDashboardController;
use App\Http\Controllers\LoteAlimentoController;

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

    Route::get('/donante/lotes', [LoteAlimentoController::class, 'index'])
        ->name('donante.lotes.index');

    Route::get('/donante/lotes/crear', [LoteAlimentoController::class, 'create'])
        ->name('donante.lotes.create');

    Route::post('/donante/lotes', [LoteAlimentoController::class, 'store'])
        ->name('donante.lotes.store');

    Route::get('/donante/lotes/{lote}/editar', [LoteAlimentoController::class, 'edit'])
    ->name('donante.lotes.edit');

    Route::put('/donante/lotes/{lote}', [LoteAlimentoController::class, 'update'])
    ->name('donante.lotes.update');
});
