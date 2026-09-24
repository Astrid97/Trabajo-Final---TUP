<?php

namespace App\Repositories\Eloquent;

use App\Models\Movimiento;
use App\Repositories\Contracts\MovimientoRepositoryInterface;

class MovimientoRepository implements MovimientoRepositoryInterface
{
    public function crear(array $datos): Movimiento
    {
        return Movimiento::create($datos);
    }

    public function buscarPorId(int $id): ?Movimiento
    {
        return Movimiento::find($id);
    }

    public function actualizar(Movimiento $movimiento, array $datos): Movimiento
    {
        $movimiento->update($datos);
        return $movimiento->fresh();
    }
}