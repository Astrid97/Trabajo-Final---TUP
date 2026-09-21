<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Cuenta;
use App\Models\Categoria;
use App\Models\ReglaFinanciera;
class Movimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'cuenta_id',
        'categoria_id',
        'tipo',
        'monto',
        'fecha',
        'descripcion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha' => 'datetime',
        ];
    }

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
}
