<?php

namespace App\Http\Controllers;

use App\Services\FinancialContextService;
use App\Services\RuleEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class FinancialContextController extends Controller
{
    public function __construct(
        private FinancialContextService $financialContextService,
        private RuleEngineService $ruleEngineService
    ) {}

    public function evaluarGasto(
        Request $request,
        int $cuentaId
    ): JsonResponse {
        $user = $request->user();
        $cuenta = $user->cuenta;
        abort_unless($cuenta && $cuenta->id === $cuentaId, 404);

        $datos = $request->validate([
            'monto' => ['required', 'numeric', 'gt:0'],
        ]);

        try {
            $resultado = $this->financialContextService->evaluarGasto(
                $user->id,
                $cuenta->id,
                (float) $datos['monto']
            );

            return response()->json($resultado);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function actualizarSaldoMinimo(
        Request $request,
        int $cuentaId
    ): JsonResponse|RedirectResponse {
        $user = $request->user();
        $cuenta = $user->cuenta;
        abort_unless($cuenta && $cuenta->id === $cuentaId, 404);

        $datos = $request->validate([
            'monto' => ['required', 'numeric', 'gt:0'],
        ]);

        $regla = $this->ruleEngineService->configurarSaldoMinimo(
            $user->id,
            (float) $datos['monto']
        );

        if (! $request->expectsJson()) {
            return redirect()
                ->route('profile', $cuenta->id)
                ->with('status', 'Tu saldo mínimo quedó actualizado.');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Tu saldo mínimo quedó actualizado.',
            'saldo_minimo' => (float) $regla->valor,
        ]);
    }
}
