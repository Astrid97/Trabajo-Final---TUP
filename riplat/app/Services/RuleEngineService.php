<?php

namespace App\Services;

use App\Models\ReglaFinanciera;
use Illuminate\Support\Facades\DB;

class RuleEngineService
{
    public function obtenerSaldoMinimo(int $userId): ?float
    {
        $regla = ReglaFinanciera::where('user_id', $userId)
            ->where('tipo', 'SALDO_MINIMO')
            ->whereNull('categoria_id')
            ->where('activa', true)
            ->orderByDesc('id')
            ->first();

        return $regla ? (float) $regla->valor : null;
    }

    public function configurarSaldoMinimo(int $userId, float $valor): ReglaFinanciera
    {
        return DB::transaction(function () use ($userId, $valor): ReglaFinanciera {
            $reglas = ReglaFinanciera::where('user_id', $userId)
                ->where('tipo', 'SALDO_MINIMO')
                ->whereNull('categoria_id');

            $regla = (clone $reglas)->orderByDesc('id')->first();

            if ($regla) {
                $regla->valor = $valor;
                $regla->activa = true;
                $regla->save();
            } else {
                $regla = ReglaFinanciera::create([
                    'user_id' => $userId,
                    'categoria_id' => null,
                    'tipo' => 'SALDO_MINIMO',
                    'valor' => $valor,
                    'activa' => true,
                ]);
            }

            $reglas->where('id', '!=', $regla->id)
                ->update(['activa' => false]);

            return $regla;
        });
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
