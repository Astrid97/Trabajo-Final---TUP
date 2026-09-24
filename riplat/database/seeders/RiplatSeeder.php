<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Cuenta;
use App\Models\Categoria;
use App\Models\Movimiento;
use App\Models\ReglaFinanciera;

class RiplatSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario de prueba
        $user = User::create([
            'username' => 'carina',
            'email' => 'carina@riplat.test',
            'password' => Hash::make('12345678'),
        ]);

        // Cuenta principal
        $cuenta = Cuenta::create([
            'user_id' => $user->id,
            'nombre' => 'Cuenta principal',
            'moneda' => 'ARS',
        ]);

        // Categorías de ingreso
        $sueldo = Categoria::create([
            'nombre' => 'SUELDO',
            'tipo' => 'INGRESO',
        ]);

        $freelance = Categoria::create([
            'nombre' => 'FREELANCE',
            'tipo' => 'INGRESO',
        ]);

        // Categorías de gasto
        $alimentacion = Categoria::create([
            'nombre' => 'ALIMENTACION',
            'tipo' => 'GASTO',
        ]);

        $servicios = Categoria::create([
            'nombre' => 'SERVICIOS',
            'tipo' => 'GASTO',
        ]);

        $transporte = Categoria::create([
            'nombre' => 'TRANSPORTE',
            'tipo' => 'GASTO',
        ]);

        $ocio = Categoria::create([
            'nombre' => 'OCIO',
            'tipo' => 'GASTO',
        ]);

        // Ingreso principal
        Movimiento::create([
            'cuenta_id' => $cuenta->id,
            'categoria_id' => $sueldo->id,
            'tipo' => 'INGRESO',
            'monto' => 500000,
            'fecha' => now()->subDays(10),
            'descripcion' => 'Sueldo mensual',
            'estado' => 'CONFIRMADO',
        ]);

        // Ingreso adicional
        Movimiento::create([
            'cuenta_id' => $cuenta->id,
            'categoria_id' => $freelance->id,
            'tipo' => 'INGRESO',
            'monto' => 50000,
            'fecha' => now()->subDays(8),
            'descripcion' => 'Trabajo freelance',
            'estado' => 'CONFIRMADO',
        ]);

        // Gastos
        Movimiento::create([
            'cuenta_id' => $cuenta->id,
            'categoria_id' => $alimentacion->id,
            'tipo' => 'GASTO',
            'monto' => 30000,
            'fecha' => now()->subDays(6),
            'descripcion' => 'Supermercado',
            'estado' => 'CONFIRMADO',
        ]);

        Movimiento::create([
            'cuenta_id' => $cuenta->id,
            'categoria_id' => $servicios->id,
            'tipo' => 'GASTO',
            'monto' => 25000,
            'fecha' => now()->subDays(4),
            'descripcion' => 'Factura de luz',
            'estado' => 'CONFIRMADO',
        ]);

        Movimiento::create([
            'cuenta_id' => $cuenta->id,
            'categoria_id' => $transporte->id,
            'tipo' => 'GASTO',
            'monto' => 15000,
            'fecha' => now()->subDays(2),
            'descripcion' => 'Nafta',
            'estado' => 'CONFIRMADO',
        ]);

        // Movimiento anulado para comprobar que el dashboard lo ignore
        Movimiento::create([
            'cuenta_id' => $cuenta->id,
            'categoria_id' => $ocio->id,
            'tipo' => 'GASTO',
            'monto' => 100000,
            'fecha' => now()->subDay(),
            'descripcion' => 'Compra anulada',
            'estado' => 'ANULADO',
        ]);

        // Regla para usar después en contexto financiero
        ReglaFinanciera::create([
            'user_id' => $user->id,
            'categoria_id' => null,
            'tipo' => 'SALDO_MINIMO',
            'valor' => 400000,
            'activa' => true,
        ]);
    }
}