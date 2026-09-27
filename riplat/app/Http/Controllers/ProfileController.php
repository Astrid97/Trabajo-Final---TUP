<?php

namespace App\Http\Controllers;

use App\Services\RuleEngineService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly RuleEngineService $ruleEngineService
    ) {}

    public function index(Request $request, int $cuentaId): View
    {
        $user = $request->user();
        $cuenta = $user->cuenta;
        abort_unless($cuenta && $cuenta->id === $cuentaId, 404);

        return view('profile.index', [
            'cuentaId' => $cuenta->id,
            'saldoMinimo' => $this->ruleEngineService->obtenerSaldoMinimo($user->id),
        ]);
    }
}
