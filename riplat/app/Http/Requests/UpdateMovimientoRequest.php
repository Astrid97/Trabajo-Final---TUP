<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMovimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cuenta_id'    => ['sometimes', 'integer', 'exists:cuentas,id'],
            'categoria_id' => ['sometimes', 'nullable', 'integer', 'exists:categorias,id'],
            'tipo'         => ['sometimes', 'string', 'in:ingreso,gasto'],
            'monto'        => ['sometimes', 'numeric', 'min:0.01'],
            'fecha'        => ['sometimes', 'date'],
            'descripcion'  => ['sometimes', 'nullable', 'string', 'max:255'],
            'estado'       => ['sometimes', 'string', 'in:pendiente,confirmado,anulado'],
        ];
    }
}