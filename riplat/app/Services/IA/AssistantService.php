<?php

namespace App\Services\IA;

use App\Models\Categoria;
use App\Models\Cuenta;
use App\Services\FinancialContextService;
use App\Services\MovimientoService;
use App\Services\RuleEngineService;
use Carbon\Carbon;

class AssistantService
{
    public function __construct(
        private readonly GeminiService $gemini,
        private readonly MovimientoService $movimientoService,
        private readonly FinancialContextService $financialContextService,
        private readonly RuleEngineService $ruleEngineService
    ) {}

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
                            'descripcion' => ['type' => 'STRING'],
                        ],
                        'required' => ['tipo', 'monto', 'categoria'],
                    ],
                ],
                [
                    'name' => 'evaluar_gasto',
                    'description' => 'Evalúa si un gasto futuro es compatible con el saldo y las reglas financieras del usuario. No registra el gasto.',
                    'parameters' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'monto' => [
                                'type' => 'NUMBER',
                                'description' => 'Importe del gasto que el usuario está considerando.',
                            ],
                        ],
                        'required' => ['monto'],
                    ],
                ],
                [
                    'name' => 'configurar_saldo_minimo',
                    'description' => 'Crea o actualiza el saldo mínimo personal solo cuando el usuario pide explícitamente configurarlo o cambiarlo.',
                    'parameters' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'monto' => [
                                'type' => 'NUMBER',
                                'description' => 'Importe mínimo que el usuario quiere conservar en su cuenta.',
                            ],
                        ],
                        'required' => ['monto'],
                    ],
                ],
            ],
        ];

        $instruccionSistema = 'Sos el asistente financiero virtual de la app Riplat.'
            .'Hablá en argentino, de forma muy amigable, natural y concisa. '
            .'Si consulta por una compra futura o expresa intención de comprar (por ejemplo "me quiero comprar", '
            .'"estoy pensando comprar", "¿me alcanza?" o "¿puedo gastar?"), usá evaluar_gasto con el monto '
            .'mencionado; nunca registres un movimiento para una intención futura o una pregunta de viabilidad. '
            .'Si la evaluación indica que no tiene saldo mínimo configurado, ofrecé configurarlo y explicá '
            .'que puede decir, por ejemplo, "configurá mi saldo mínimo en 400000". '
            .'Usá configurar_saldo_minimo solo cuando el usuario pida explícitamente guardar o cambiar '
            .'ese monto; no lo configures por inferencia. '
            .'Usá registrar_movimiento solo si afirma que el gasto o ingreso ya ocurrió (por ejemplo "compré", '
            .'"ya gasté" o "pagué"). '
            .'Si te saluda o habla de temas fuera de las finanzas, respondé con calidez y recordale '
            .'sutilmente que estás para ayudarle con sus finanzas.';

        $response = $this->gemini->analyzeIntent($prompt, $tools, $instruccionSistema);
        $parts = $response['candidates'][0]['content']['parts'] ?? [];

        foreach ($parts as $part) {
            if (! isset($part['functionCall'])) {
                continue;
            }

            $functionCall = $part['functionCall'];
            $args = $functionCall['args'] ?? [];

            if (($functionCall['name'] ?? null) === 'registrar_movimiento') {
                return $this->ejecutarRegistro($args, $cuenta);
            }

            if (($functionCall['name'] ?? null) === 'evaluar_gasto') {
                return $this->evaluarGasto($args, $cuenta);
            }

            if (($functionCall['name'] ?? null) === 'configurar_saldo_minimo') {
                return $this->configurarSaldoMinimo($args, $cuenta);
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

    private function evaluarGasto(array $args, Cuenta $cuenta): array
    {
        $monto = $args['monto'] ?? null;

        if (! is_numeric($monto) || (float) $monto <= 0) {
            return [
                'status' => 'info',
                'message' => 'Decime el monto del gasto que querés evaluar y lo reviso con tu saldo y tus reglas.',
            ];
        }

        $resultado = $this->financialContextService->evaluarGasto(
            (int) $cuenta->user_id,
            (int) $cuenta->id,
            (float) $monto
        );

        return [
            'status' => 'success',
            'message' => $this->crearRespuestaEvaluacion($resultado),
            'evaluacion' => $resultado,
        ];
    }

    private function configurarSaldoMinimo(array $args, Cuenta $cuenta): array
    {
        $monto = $args['monto'] ?? null;

        if (! is_numeric($monto) || (float) $monto <= 0) {
            return [
                'status' => 'info',
                'message' => 'Decime qué monto querés conservar como saldo mínimo y lo guardo.',
            ];
        }

        $regla = $this->ruleEngineService->configurarSaldoMinimo(
            (int) $cuenta->user_id,
            (float) $monto
        );

        return [
            'status' => 'success',
            'message' => 'Listo, configuré tu saldo mínimo en '
                .$this->formatearDinero((float) $regla->valor)
                .'. Si necesitás cambiarlo, podés hacerlo desde Perfil.',
            'saldo_minimo' => (float) $regla->valor,
        ];
    }

    private function crearRespuestaEvaluacion(array $resultado): string
    {
        $saldoPosterior = (float) $resultado['saldo_posterior'];
        $monto = (float) $resultado['monto'];
        $evaluacion = $resultado['evaluacion'];
        $respuesta = 'Si hacés ese gasto de '
            .$this->formatearDinero($monto)
            .', tu saldo quedaría en '
            .$this->formatearDinero($saldoPosterior)
            .'.';

        if (! $evaluacion['aplica']) {
            return $respuesta
                .' Todavía no tenés un saldo mínimo configurado, así que no puedo compararlo con una regla. Si querés, decime "configurá mi saldo mínimo en 400000" y lo guardo.';
        }

        $saldoMinimo = (float) $evaluacion['saldo_minimo'];

        if ($evaluacion['cumple']) {
            return $respuesta
                .' Sí: respetás tu saldo mínimo de '
                .$this->formatearDinero($saldoMinimo)
                .' y quedarías '
                .$this->formatearDinero((float) $evaluacion['diferencia'])
                .' por encima.';
        }

        return $respuesta
            .' No: quedarías '
            .$this->formatearDinero(abs((float) $evaluacion['diferencia']))
            .' por debajo de tu saldo mínimo de '
            .$this->formatearDinero($saldoMinimo)
            .'.';
    }

    private function formatearDinero(float $monto): string
    {
        return '$ '.number_format($monto, 0, ',', '.');
    }

    private function ejecutarRegistro(array $args, Cuenta $cuenta)
    {
        // 1. Validamos el TIPO primero, porque las validaciones siguientes lo necesitan
        //    para armar mensajes de error coherentes (ej: listar categorías de ESE tipo).
        if (! in_array($args['tipo'], ['INGRESO', 'GASTO'], true)) {
            return [
                'status' => 'info',
                'message' => "No entendí si fue un ingreso o un gasto. ¿Podrías reformularlo? Por ejemplo: 'gasté 500 en comida' o 'cobré 1000 de sueldo'.",
            ];
        }

        // 2. Buscamos la categoría por nombre (es la columna única en tu tabla)
        $categoria = Categoria::where('nombre', $args['categoria'])->first();

        if (! $categoria) {
            // No existe esa categoría en absoluto: le mostramos las opciones válidas
            // PARA EL TIPO que ya confirmamos en el paso 1.
            $categoriasValidas = Categoria::where('tipo', $args['tipo'])->pluck('nombre');

            return [
                'status' => 'info',
                'message' => "No reconocí la categoría '{$args['categoria']}'. Las categorías disponibles para un {$args['tipo']} son: ".$categoriasValidas->implode(', ').'.',
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
        if (! is_numeric($args['monto']) || $args['monto'] <= 0) {
            return [
                'status' => 'info',
                'message' => 'No pude identificar un monto válido. ¿Podrías indicarme el importe exacto?',
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
