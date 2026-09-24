<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cuenta_id'    => ['required', 'integer', 'exists:cuentas,id'],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias,id'],
            'tipo'         => ['required', 'string', 'in:ingreso,gasto'],
            'monto'        => ['required', 'numeric', 'min:0.01'],
            'fecha'        => ['nullable', 'date'],
            'descripcion'  => ['nullable', 'string', 'max:255'],
            'estado'       => ['nullable', 'string', 'in:pendiente,confirmado,anulado'],
        ];
    }
}