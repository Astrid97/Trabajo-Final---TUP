<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index(Request $request, int $cuentaId): View
    {
        $cuenta = $request->user()->cuenta;
        abort_unless($cuenta && $cuenta->id === $cuentaId, 404);

        $resumen = $this->dashboardService->obtenerResumen($cuentaId);

        return view('dashboard.index', [
            'resumen' => $resumen,
            'cuentaId' => $cuentaId,
        ]);
    }

    public function data(Request $request, int $cuentaId): JsonResponse
    {
        $cuenta = $request->user()->cuenta;
        abort_unless($cuenta && $cuenta->id === $cuentaId, 404);

        return response()->json(
            $this->dashboardService->obtenerResumen($cuentaId)
        );
    }
}
