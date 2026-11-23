<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ColaboradorDashboardController;
use App\Http\Controllers\ComedorDashboardController;
use App\Http\Controllers\DonanteDashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoteAlimentoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Autenticación
Route::middleware('guest')->group(function () {
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// DONANTE
Route::middleware(['auth', 'role:donante'])
    ->prefix('donante')
    ->name('donante.')
    ->group(function () {
        Route::get('/panel', [DonanteDashboardController::class, 'index'])->name('dashboard');

        Route::get('/lotes', [LoteAlimentoController::class, 'index'])->name('lotes.index');
        Route::get('/lotes/crear', [LoteAlimentoController::class, 'create'])->name('lotes.create');
        Route::post('/lotes', [LoteAlimentoController::class, 'store'])->name('lotes.store');
        Route::get('/lotes/{lote}/editar', [LoteAlimentoController::class, 'edit'])->name('lotes.edit');
        Route::put('/lotes/{lote}', [LoteAlimentoController::class, 'update'])->name('lotes.update');
        Route::patch('/lotes/{lote}/pausar', [LoteAlimentoController::class, 'pausar'])->name('lotes.pausar');
        Route::patch('/lotes/{lote}/reactivar', [LoteAlimentoController::class, 'reactivar'])->name('lotes.reactivar');

        Route::get('/solicitudes', [DonanteDashboardController::class, 'solicitudes'])->name('solicitudes.index');
        Route::post('/solicitudes/{solicitud}/aceptar', [DonanteDashboardController::class, 'aceptarSolicitud'])->name('solicitudes.aceptar');
        Route::post('/solicitudes/{solicitud}/rechazar', [DonanteDashboardController::class, 'rechazarSolicitud'])->name('solicitudes.rechazar');
    });

// COMEDOR POPULAR
Route::middleware(['auth', 'role:comedor'])
    ->prefix('comedor')
    ->name('comedor.')
    ->group(function () {
        Route::get('/panel', [ComedorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/perfil', [ComedorDashboardController::class, 'perfil'])->name('perfil');
        Route::post('/perfil', [ComedorDashboardController::class, 'guardarPerfil'])->name('perfil.store');
        Route::get('/catalogo', [ComedorDashboardController::class, 'catalogo'])->name('catalogo');
        Route::post('/lotes/{lote}/solicitar', [ComedorDashboardController::class, 'solicitarRescate'])->name('lotes.solicitar');
        Route::get('/solicitudes', [ComedorDashboardController::class, 'misSolicitudes'])->name('solicitudes.index');
        Route::post('/traslados/{traslado}/confirmar-entrega', [ComedorDashboardController::class, 'confirmarEntrega'])->name('entregas.store');
    });

// COLABORADOR
Route::middleware(['auth', 'role:colaborador'])
    ->prefix('colaborador')
    ->name('colaborador.')
    ->group(function () {
        Route::get('/panel', [ColaboradorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/traslados', [ColaboradorDashboardController::class, 'traslados'])->name('traslados.index');
        Route::post('/traslados/{traslado}/asignarme', [ColaboradorDashboardController::class, 'asignarme'])->name('traslados.asignar');
        Route::post('/traslados/{traslado}/iniciar', [ColaboradorDashboardController::class, 'iniciar'])->name('traslados.iniciar');
    });

// ADMINISTRADOR
Route::middleware(['auth', 'role:administrador'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/panel', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/usuarios', [AdminDashboardController::class, 'usuarios'])->name('usuarios.index');
        Route::patch('/usuarios/{user}/toggle-estado', [AdminDashboardController::class, 'toggleUsuario'])->name('usuarios.toggle');
        Route::get('/traslados', [AdminDashboardController::class, 'traslados'])->name('traslados.index');
        Route::post('/traslados/{traslado}/asignar', [AdminDashboardController::class, 'asignarColaborador'])->name('traslados.asignar');
    });
