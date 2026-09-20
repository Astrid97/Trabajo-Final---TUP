<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cuenta_id')
                ->constrained('cuentas')
                ->cascadeOnDelete();

            $table->foreignId('categoria_id')
                ->constrained('categorias');

            $table->enum('tipo', [
                'INGRESO',
                'GASTO'
            ]);

            $table->decimal('monto', 15, 2);

            $table->dateTime('fecha');

            $table->string('descripcion')->nullable();

            $table->enum('estado', [
                'CONFIRMADO',
                'ANULADO'
            ])->default('CONFIRMADO');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
