<?php

namespace App\Http\Controllers;

use App\Services\FinancialContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class FinancialContextController extends Controller
{
    public function __construct(
        private FinancialContextService $financialContextService
    ) {
    }

    public function evaluarGasto(
        Request $request,
        int $userId,
        int $cuentaId
    ): JsonResponse {
        $datos = $request->validate([
            'monto' => ['required', 'numeric', 'gt:0'],
        ]);

        try {
            $resultado = $this->financialContextService->evaluarGasto(
                $userId,
                $cuentaId,
                (float) $datos['monto']
            );

            return response()->json($resultado);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}