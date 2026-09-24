<?php

namespace App\Services;

use App\Models\Movimiento;
use App\Repositories\Contracts\MovimientoRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class MovimientoService
{
    public function __construct(
        protected MovimientoRepositoryInterface $movimientoRepository
    ) {}

    public function registrarMovimiento(array $datos): Movimiento
    {
        return DB::transaction(function () use ($datos) {

            $datos['fecha'] = $datos['fecha'] ?? now()->toDateString();
            
            $datos['estado'] = $datos['estado'] ?? 'confirmado';

            return $this->movimientoRepository->crear($datos);
        });
    }

    public function corregirMovimiento(int $id, array $datos): Movimiento
    {
        return DB::transaction(function () use ($id, $datos) {
            $movimiento = $this->movimientoRepository->buscarPorId($id);

            if (!$movimiento) {
                throw new ModelNotFoundException("El movimiento con ID {$id} no fue encontrado.");
            }

            return $this->movimientoRepository->actualizar($movimiento, $datos);
        });
    }
}