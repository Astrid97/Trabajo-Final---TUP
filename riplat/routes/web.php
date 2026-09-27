<?php

use App\Http\Controllers\AssistantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinancialContextController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = Auth::user();

    if (! $user) {
        return redirect()->route('login');
    }

    $cuenta = $user->cuenta;

    abort_unless($cuenta, 403);

    return redirect()->route('dashboard.account', $cuenta->id);
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.store');

    Route::get('/registro', [AuthController::class, 'showRegistration'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/perfil/{cuentaId}', [ProfileController::class, 'index'])
        ->name('profile');

    Route::get(
        '/dashboard/{cuentaId}',
        [DashboardController::class, 'index']
    )->name('dashboard.account');

    Route::get(
        '/api/dashboard/{cuentaId}',
        [DashboardController::class, 'data']
    )->name('dashboard.data');

    Route::get(
        '/financial-context/{cuentaId}/evaluar-gasto',
        [FinancialContextController::class, 'evaluarGasto']
    )->name('financial-context.evaluate');
    Route::put(
        '/financial-context/{cuentaId}/saldo-minimo',
        [FinancialContextController::class, 'actualizarSaldoMinimo']
    )->name('financial-context.minimum-balance.update');

    Route::get(
        '/chat-ia/{cuentaId}',
        [AssistantController::class, 'index']
    )->name('assistant.index');

    Route::post('/api/assistant/chat', [AssistantController::class, 'chat'])
        ->name('assistant.chat');

    Route::get(
        '/movimientos/{cuentaId}',
        [MovimientoController::class, 'index']
    )->name('movimientos.index');
    Route::post('/movimientos', [MovimientoController::class, 'store'])
        ->name('movimientos.store');
    Route::put('/movimientos/{id}', [MovimientoController::class, 'update'])
        ->name('movimientos.update');
});
