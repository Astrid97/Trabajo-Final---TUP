<?php

namespace App\Services;

use App\Models\ReglaFinanciera;

class RuleEngineService
{
    public function obtenerSaldoMinimo(int $userId): ?float
    {
        $regla = ReglaFinanciera::where('user_id', $userId)
            ->where('tipo', 'SALDO_MINIMO')
            ->where('activa', true)
            ->first();

        return $regla ? (float) $regla->valor : null;
    }

    public function evaluarSaldoMinimo(
        int $userId,
        float $saldoPosterior
    ): array {
        $saldoMinimo = $this->obtenerSaldoMinimo($userId);

        if ($saldoMinimo === null) {
            return [
                'aplica' => false,
                'saldo_minimo' => null,
                'cumple' => null,
                'diferencia' => null,
            ];
        }

        $diferencia = $saldoPosterior - $saldoMinimo;

        return [
            'aplica' => true,
            'saldo_minimo' => $saldoMinimo,
            'cumple' => $saldoPosterior >= $saldoMinimo,
            'diferencia' => $diferencia,
        ];
    }
}