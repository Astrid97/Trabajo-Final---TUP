<?php

namespace App\Services;

use App\Models\Movimiento;

class DashboardService
{
    public function calcularSaldo(int $cuentaId): float
    {
        $ingresos = Movimiento::where('cuenta_id', $cuentaId)
            ->where('tipo', 'INGRESO')
            ->where('estado', 'CONFIRMADO')
            ->sum('monto');

        $gastos = Movimiento::where('cuenta_id', $cuentaId)
            ->where('tipo', 'GASTO')
            ->where('estado', 'CONFIRMADO')
            ->sum('monto');

        return (float) $ingresos - (float) $gastos;
    }

    public function obtenerResumen(int $cuentaId): array
    {
        $ingresos = Movimiento::where('cuenta_id', $cuentaId)
            ->where('tipo', 'INGRESO')
            ->where('estado', 'CONFIRMADO')
            ->sum('monto');

        $gastos = Movimiento::where('cuenta_id', $cuentaId)
            ->where('tipo', 'GASTO')
            ->where('estado', 'CONFIRMADO')
            ->sum('monto');

        $ultimosMovimientos = Movimiento::where('cuenta_id', $cuentaId)
            ->where('estado', 'CONFIRMADO')
            ->with('categoria')
            ->orderByDesc('fecha')
            ->limit(5)
            ->get();

        return [
            'saldo' => (float) $ingresos - (float) $gastos,
            'total_ingresos' => (float) $ingresos,
            'total_gastos' => (float) $gastos,
            'gastos_por_categoria' => $this->obtenerGastosPorCategoria(
                $cuentaId
            ),
            'ultimos_movimientos' => $ultimosMovimientos,
        ];
    }

    public function obtenerGastosPorCategoria(int $cuentaId): array
    {
        $gastos = Movimiento::where('cuenta_id', $cuentaId)
            ->where('tipo', 'GASTO')
            ->where('estado', 'CONFIRMADO')
            ->with('categoria')
            ->get();

        $totalGastos = (float) $gastos->sum('monto');

        if ($totalGastos === 0.0) {
            return [];
        }

        return $gastos
            ->groupBy('categoria_id')
            ->map(function ($movimientos) use ($totalGastos) {

                $totalCategoria = (float) $movimientos->sum('monto');
                $categoria = $movimientos->first()->categoria;

                return [
                    'categoria' => $categoria->nombre,
                    'total' => $totalCategoria,
                    'porcentaje' => round(
                        ($totalCategoria / $totalGastos) * 100,
                        2
                    ),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->toArray();
    }
}

