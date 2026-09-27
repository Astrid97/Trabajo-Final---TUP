<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovimientoRequest;
use App\Http\Requests\UpdateMovimientoRequest;
use App\Models\Categoria;
use App\Models\Movimiento;
use App\Services\MovimientoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class MovimientoController extends Controller
{
    public function __construct(
        private MovimientoService $movimientoService
    ) {
    }

    public function index(int $cuentaId): View
    {
        $movimientos = Movimiento::where('cuenta_id', $cuentaId)
            ->with('categoria')
            ->orderByDesc('fecha')
            ->get();

        $categorias = Categoria::orderBy('nombre')
            ->get();

        return view('movimientos.index', [
            'movimientos' => $movimientos,
            'categorias' => $categorias,
            'cuentaId' => $cuentaId,
        ]);
    }

    public function store(StoreMovimientoRequest $request)
    {
        $movimiento = $this->movimientoService
            ->registrarMovimiento(
                $request->validated()
            );

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Movimiento registrado con éxito.',
                'data' => $movimiento,
            ], Response::HTTP_CREATED);
        }

        return redirect()
            ->route(
                'movimientos.index',
                $movimiento->cuenta_id
            )
            ->with(
                'success',
                'Movimiento registrado con éxito.'
            );
    }

    public function update(
        UpdateMovimientoRequest $request,
        int $id
    ): JsonResponse {
        $movimiento = $this->movimientoService
            ->corregirMovimiento(
                $id,
                $request->validated()
            );

        return response()->json([
            'status' => 'success',
            'message' => 'Movimiento actualizado con éxito.',
            'data' => $movimiento,
        ]);
    }
}