<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovimientoRequest;
use App\Http\Requests\UpdateMovimientoRequest;
use App\Services\MovimientoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class MovimientoController extends Controller
{
    public function __construct(
        protected MovimientoService $movimientoService
    ) {}

    public function store(StoreMovimientoRequest $request): JsonResponse
    {
        $movimiento = $this->movimientoService->registrarMovimiento($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Movimiento registrado con éxito.',
            'data'    => $movimiento,
        ], Response::HTTP_CREATED);
    }

    public function update(UpdateMovimientoRequest $request, int $id): JsonResponse
    {
        $movimiento = $this->movimientoService->corregirMovimiento($id, $request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Movimiento actualizado con éxito.',
            'data'    => $movimiento,
        ], Response::HTTP_OK);
    }
}