<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {
    }

    public function index(int $cuentaId): View
    {
        $resumen = $this->dashboardService->obtenerResumen($cuentaId);

        return view('dashboard.index', [
            'resumen' => $resumen,
            'cuentaId' => $cuentaId,
        ]);
    }

    public function data(int $cuentaId): JsonResponse
    {
        return response()->json(
            $this->dashboardService->obtenerResumen($cuentaId)
        );
    }
}