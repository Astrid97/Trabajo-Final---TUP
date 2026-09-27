<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinancialContextController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\MovimientoController;

Route::get(
    '/dashboard/{cuentaId}',
    [DashboardController::class, 'index']
)->name('dashboard');

Route::get(
    '/api/dashboard/{cuentaId}',
    [DashboardController::class, 'data']
)->name('dashboard.data');

Route::get(
    '/financial-context/{userId}/{cuentaId}/evaluar-gasto',
    [FinancialContextController::class, 'evaluarGasto']
);

Route::get(
    '/chat-ia/{cuentaId}',
    [AssistantController::class, 'index']
)->name('assistant.index');

Route::post('/api/assistant/chat', [AssistantController::class, 'chat'])
    ->name('assistant.chat');

// movimientossss
Route::get(
    '/movimientos/{cuentaId}', [MovimientoController::class, 'index'])->name('movimientos.index');
Route::post('/movimientos',[MovimientoController::class, 'store'])->name('movimientos.store');
Route::put('/movimientos/{id}',[MovimientoController::class, 'update'])->name('movimientos.update');