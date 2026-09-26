<?php

namespace App\Services\IA;

use App\Models\Cuenta;
use App\Models\Categoria;
use App\Services\IA\GeminiService;
use App\Services\MovimientoService;
use Carbon\Carbon;
use Exception;

class AssistantService
{
    public function __construct(
        private readonly GeminiService $gemini, 
        private readonly MovimientoService $movimientoService)
    {
        
    }

    public function processChat(string $prompt, Cuenta $cuenta)
    {
        $categoriasValidas = Categoria::pluck('nombre')->toArray();

        $tools = [
            'functionDeclarations' => [
                [
                    'name' => 'registrar_movimiento',
                    'description' => 'Registra un ingreso o gasto financiero del usuario.',
                    'parameters' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'tipo' => ['type' => 'STRING', 'enum' => ['INGRESO', 'GASTO']],
                            'monto' => ['type' => 'INTEGER'],
                            'categoria' => ['type' => 'STRING', 'enum' => $categoriasValidas],
                            'descripcion' => ['type' => 'STRING']
                        ],
                        'required' => ['tipo', 'monto', 'categoria']
                    ]
                ]
            ]
        ];

        $response = $this->gemini->analyzeIntent($prompt, $tools);
        $parts = $response['candidates'][0]['content']['parts'] ?? [];
        
        foreach ($parts as $part) {
            if (isset($part['functionCall']) && $part['functionCall']['name'] === 'registrar_movimiento') {
                return $this->ejecutarRegistro($part['functionCall']['args'], $cuenta);
            }
        }

        return [
            'status' => 'info',
            'message' => 'No comprendí la transacción. ¿Podrías especificar el monto y en qué lo gastaste?'
        ];
    }

    private function ejecutarRegistro(array $args, Cuenta $cuenta)
    {
        $categoria = Categoria::where('nombre', $args['categoria'])->first();

        if (!$categoria) {
            throw new Exception("La IA seleccionó una categoría inválida.");
        }

        if (!in_array($args['tipo'], ['INGRESO', 'GASTO'], true)) {
            throw new Exception("La IA devolvió un tipo de movimiento inválido: {$args['tipo']}");
        }

        if (!is_numeric($args['monto']) || $args['monto'] <= 0) {
            throw new Exception("La IA devolvió un monto inválido.");
        }

        
    
        $datosMovimiento = [
            'cuenta_id' => $cuenta->id,
            'categoria_id' => $categoria->id,
            'tipo' => $args['tipo'],
            'monto' => $args['monto'],
            'descripcion' => $args['descripcion'] ?? null,
            'fecha' => Carbon::now()->toDateString(),
            'estado' => 'CONFIRMADO'
        ];

        
        $movimiento = $this->movimientoService->registrarMovimiento($datosMovimiento);

        return [
            'status' => 'success',
            'message' => "¡Listo! Registré un {$args['tipo']} de \${$args['monto']} en la categoría {$categoria->nombre}.",
            'movimiento' => $movimiento
        ];
    }
}