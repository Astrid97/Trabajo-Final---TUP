<?php

namespace App\Repositories\Contracts;

use App\Models\Movimiento;

interface MovimientoRepositoryInterface
{
    public function crear(array $datos): Movimiento;

    public function buscarPorId(int $id): ?Movimiento;

    public function actualizar(Movimiento $movimiento, array $datos): Movimiento;
}