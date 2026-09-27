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
            'categoria_id' => [
                'sometimes',
                'integer',
                'exists:categorias,id',
            ],

            'tipo' => [
                'sometimes',
                'string',
                'in:INGRESO,GASTO',
            ],

            'monto' => [
                'sometimes',
                'numeric',
                'gt:0',
            ],

            'fecha' => [
                'sometimes',
                'date',
            ],

            'descripcion' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'estado' => [
                'sometimes',
                'string',
                'in:CONFIRMADO,ANULADO',
            ],
        ];
    }
}