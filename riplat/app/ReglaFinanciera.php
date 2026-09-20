<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Categoria;

class ReglaFinanciera extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'categoria_id',
        'tipo',
        'valor',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'activa' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
}