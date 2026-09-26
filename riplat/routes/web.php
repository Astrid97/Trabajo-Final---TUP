<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinancialContextController;
use App\Http\Controllers\AssistantController;

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

Route::post('/api/assistant/chat', [AssistantController::class, 'chat'])
    ->middleware('auth')
    ->name('assistant.chat');