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
            'categoria_id' => [
                'required',
                'integer',
                'exists:categorias,id',
            ],

            'tipo' => [
                'required',
                'string',
                'in:INGRESO,GASTO',
            ],

            'monto' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'fecha' => [
                'nullable',
                'date',
            ],

            'descripcion' => [
                'nullable',
                'string',
                'max:255',
            ],

            'estado' => [
                'nullable',
                'string',
                'in:CONFIRMADO,ANULADO',
            ],
        ];
    }
}
