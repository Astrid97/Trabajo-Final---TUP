<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;


class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            // Gastos
            ['nombre' => 'Comida', 'tipo' => 'GASTO'],
            ['nombre' => 'Transporte', 'tipo' => 'GASTO'],
            ['nombre' => 'Ropa', 'tipo' => 'GASTO'],
            ['nombre' => 'Servicios', 'tipo' => 'GASTO'],
            ['nombre' => 'Entretenimiento', 'tipo' => 'GASTO'],
            ['nombre' => 'Salud', 'tipo' => 'GASTO'],
            ['nombre' => 'Otros gastos', 'tipo' => 'GASTO'],

            // Ingresos
            ['nombre' => 'Sueldo', 'tipo' => 'INGRESO'],
            ['nombre' => 'Freelance', 'tipo' => 'INGRESO'],
            ['nombre' => 'Venta', 'tipo' => 'INGRESO'],
            ['nombre' => 'Otros ingresos', 'tipo' => 'INGRESO'],
        ];

        foreach ($categorias as $categoria) {
            // firstOrCreate busca por 'nombre' (la parte única real acá)
            // y crea la fila completa (con 'tipo' incluido) si no existe
            Categoria::firstOrCreate(
                ['nombre' => $categoria['nombre']],
                ['tipo' => $categoria['tipo']]
            );
        }
    }
}
