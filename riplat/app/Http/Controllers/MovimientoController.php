<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovimientoRequest;
use App\Http\Requests\UpdateMovimientoRequest;
use App\Models\Categoria;
use App\Services\MovimientoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class MovimientoController extends Controller
{
    public function __construct(
        private MovimientoService $movimientoService
    ) {}

    public function index(Request $request, int $cuentaId): View
    {
        $cuenta = $request->user()->cuenta;
        abort_unless($cuenta && $cuenta->id === $cuentaId, 404);

        $movimientos = $cuenta->movimientos()
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
        $cuenta = $request->user()->cuenta;
        abort_unless($cuenta, 403);

        $movimiento = $this->movimientoService
            ->registrarMovimiento(
                array_merge($request->validated(), [
                    'cuenta_id' => $cuenta->id,
                ])
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
        $cuenta = $request->user()->cuenta;
        abort_unless($cuenta, 403);
        $cuenta->movimientos()->findOrFail($id);

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
