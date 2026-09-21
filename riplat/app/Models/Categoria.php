<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Movimiento;
use App\Models\ReglaFinanciera;
class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'tipo',
    ];

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    public function reglasFinancieras(): HasMany
    {
        return $this->hasMany(ReglaFinanciera::class);
    }
}
