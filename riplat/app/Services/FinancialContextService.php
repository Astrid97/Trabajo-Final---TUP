<?php

namespace App\Services;

use App\Models\Cuenta;
use InvalidArgumentException;

class FinancialContextService
{
    public function __construct(
        private DashboardService $dashboardService,
        private RuleEngineService $ruleEngineService
    ) {
    }

    public function evaluarGasto(
        int $userId,
        int $cuentaId,
        float $monto
    ): array {
        if ($monto <= 0) {
            throw new InvalidArgumentException(
                'El monto debe ser mayor a cero.'
            );
        }

        $cuenta = Cuenta::where('id', $cuentaId)
            ->where('user_id', $userId)
            ->first();

        if (!$cuenta) {
            throw new InvalidArgumentException(
                'La cuenta no pertenece al usuario indicado.'
            );
        }

        $saldoActual = $this->dashboardService
            ->calcularSaldo($cuentaId);

        $saldoPosterior = $saldoActual - $monto;

        $evaluacion = $this->ruleEngineService
            ->evaluarSaldoMinimo($userId, $saldoPosterior);

        return [
            'saldo_actual' => $saldoActual,
            'monto' => $monto,
            'saldo_posterior' => $saldoPosterior,
            'evaluacion' => $evaluacion,
        ];
    }
}