<?php

namespace App\Services\IA;

use App\Models\Cuenta;
use App\Models\Categoria;
use App\Services\IA\GeminiService;
use App\Services\MovimientoService;
use Carbon\Carbon;
use App\Models\Movimiento;

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

        $instruccionSistema = 'Sos el asistente financiero virtual de la app Riplat.'
            . 'Hablá en argentino, de forma muy amigable, natural y concisa. '
            . 'Si el usuario te saluda, te dice "te quiero" o habla de temas fuera de las finanzas, '
            . 'respondé con calidez y recordale sutilmente que estás para ayudarle a registrar sus movimientos.';

        $response = $this->gemini->analyzeIntent($prompt, $tools, $instruccionSistema);
        $parts = $response['candidates'][0]['content']['parts'] ?? [];
        
        foreach ($parts as $part) {
            if (isset($part['functionCall']) && $part['functionCall']['name'] === 'registrar_movimiento') {
                return $this->ejecutarRegistro($part['functionCall']['args'], $cuenta);
            }
        }
        // Si no hubo function call, buscamos si Gemini generó una respuesta en texto
        $textoGenerado = collect($parts)
            ->pluck('text')
            ->filter() // descarta valores null o vacíos
            ->implode(' ');
        
            return [
            'status' => 'info',
            'message' => $textoGenerado !== ''
            ? $textoGenerado
            : 'No comprendí la transacción. ¿Podrías decirme si fue un ingreso o un gasto, el monto, y en que concepto?',
        ];
    }

    private function ejecutarRegistro(array $args, Cuenta $cuenta)
    {
        // 1. Validamos el TIPO primero, porque las validaciones siguientes lo necesitan
        //    para armar mensajes de error coherentes (ej: listar categorías de ESE tipo).
        if (!in_array($args['tipo'], ['INGRESO', 'GASTO'], true)) {
            return [
                'status' => 'info',
                'message' => "No entendí si fue un ingreso o un gasto. ¿Podrías reformularlo? Por ejemplo: 'gasté 500 en comida' o 'cobré 1000 de sueldo'.",
            ];
        }

        // 2. Buscamos la categoría por nombre (es la columna única en tu tabla)
        $categoria = Categoria::where('nombre', $args['categoria'])->first();

        if (!$categoria) {
            // No existe esa categoría en absoluto: le mostramos las opciones válidas
            // PARA EL TIPO que ya confirmamos en el paso 1.
            $categoriasValidas = Categoria::where('tipo', $args['tipo'])->pluck('nombre');

            return [
                'status' => 'info',
                'message' => "No reconocí la categoría '{$args['categoria']}'. Las categorías disponibles para un {$args['tipo']} son: " . $categoriasValidas->implode(', ') . '.',
            ];
        }

        // 3. La categoría existe, pero puede no corresponder al tipo que interpretó la IA
        //    (ej: Gemini dice GASTO pero la categoría "Sueldo" es de tipo INGRESO)
        if ($categoria->tipo !== $args['tipo']) {
            return [
                'status' => 'info',
                'message' => "La categoría '{$categoria->nombre}' es de tipo {$categoria->tipo}, no {$args['tipo']}. ¿Podrías confirmar el movimiento?",
            ];
        }

        // 4. Validamos que el monto sea un número positivo
        if (!is_numeric($args['monto']) || $args['monto'] <= 0) {
            return [
                'status' => 'info',
                'message' => "No pude identificar un monto válido. ¿Podrías indicarme el importe exacto?",
            ];
        }

        // 5. Si llegamos hasta acá, todos los datos son válidos: registramos el movimiento
        $datosMovimiento = [
            'cuenta_id' => $cuenta->id,
            'categoria_id' => $categoria->id,
            'tipo' => $args['tipo'],
            'monto' => $args['monto'],
            'descripcion' => $args['descripcion'] ?? null,
            'fecha' => Carbon::now()->toDateString(),
            'estado' => 'CONFIRMADO',
        ];

        $movimiento = $this->movimientoService->registrarMovimiento($datosMovimiento);

        return [
            'status' => 'success',
            'message' => "¡Listo! Registré un {$args['tipo']} de \${$args['monto']} en la categoría {$categoria->nombre}.",
            'movimiento' => $movimiento,
        ];
    }
}