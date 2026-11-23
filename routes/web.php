<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

// Registro de usuarios.
Route::get('/registro', [AuthController::class, 'showRegister'])
    ->name('register');

Route::post('/registro', [AuthController::class, 'register'])
    ->name('register.store');
