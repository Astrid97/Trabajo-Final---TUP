<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cuenta;
use App\Models\Movimiento;
use App\Models\ReglaFinanciera;
use App\Models\User;
use App\Services\IA\GeminiApiException;
use App\Services\IA\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AssistantExpenseEvaluationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Cuenta $cuenta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->cuenta = $this->user->cuenta()->create([
            'nombre' => 'Cuenta principal',
            'moneda' => 'ARS',
        ]);

        $categoriaIngreso = Categoria::create([
            'nombre' => 'INGRESO TEST',
            'tipo' => 'INGRESO',
        ]);

        Movimiento::create([
            'cuenta_id' => $this->cuenta->id,
            'categoria_id' => $categoriaIngreso->id,
            'tipo' => 'INGRESO',
            'monto' => 1000,
            'fecha' => now()->toDateString(),
            'descripcion' => 'Saldo de prueba',
            'estado' => 'CONFIRMADO',
        ]);
    }

    public function test_chat_says_spend_respects_the_minimum_balance_rule(): void
    {
        $this->crearSaldoMinimo(500);
        $this->prepararToolDeEvaluacion(400);

        $this->actingAs($this->user)
            ->postJson(route('assistant.chat'), ['mensaje' => '¿Puedo gastar 400?'])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('evaluacion.saldo_posterior', 600)
            ->assertJsonPath('evaluacion.evaluacion.cumple', true)
            ->assertJsonPath('message', 'Si hacés ese gasto de $ 400, tu saldo quedaría en $ 600. Sí: respetás tu saldo mínimo de $ 500 y quedarías $ 100 por encima.');

        $this->assertDatabaseCount('movimientos', 1);
    }

    public function test_chat_warns_when_a_spend_would_break_the_minimum_balance_rule(): void
    {
        $this->crearSaldoMinimo(500);
        $this->prepararToolDeEvaluacion(600);

        $this->actingAs($this->user)
            ->postJson(route('assistant.chat'), ['mensaje' => '¿Me alcanza para gastar 600?'])
            ->assertOk()
            ->assertJsonPath('evaluacion.saldo_posterior', 400)
            ->assertJsonPath('evaluacion.evaluacion.cumple', false)
            ->assertJsonPath('message', 'Si hacés ese gasto de $ 600, tu saldo quedaría en $ 400. No: quedarías $ 100 por debajo de tu saldo mínimo de $ 500.');

        $this->assertDatabaseCount('movimientos', 1);
    }

    public function test_chat_explains_that_no_minimum_balance_rule_is_configured(): void
    {
        $this->prepararToolDeEvaluacion(250);

        $this->actingAs($this->user)
            ->postJson(route('assistant.chat'), ['mensaje' => '¿Puedo comprar algo de 250?'])
            ->assertOk()
            ->assertJsonPath('evaluacion.saldo_posterior', 750)
            ->assertJsonPath('evaluacion.evaluacion.aplica', false)
            ->assertJsonPath('message', 'Si hacés ese gasto de $ 250, tu saldo quedaría en $ 750. Todavía no tenés un saldo mínimo configurado, así que no puedo compararlo con una regla. Si querés, decime "configurá mi saldo mínimo en 400000" y lo guardo.');

        $this->assertDatabaseCount('movimientos', 1);
    }

    public function test_user_can_configure_minimum_balance_in_chat_when_explicitly_requested(): void
    {
        $this->prepararTool('configurar_saldo_minimo', 600);

        $this->actingAs($this->user)
            ->postJson(route('assistant.chat'), [
                'mensaje' => 'Configurá mi saldo mínimo en 600',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('saldo_minimo', 600)
            ->assertJsonPath('message', 'Listo, configuré tu saldo mínimo en $ 600. Si necesitás cambiarlo, podés hacerlo desde Perfil.');

        $this->assertDatabaseHas('reglas_financieras', [
            'user_id' => $this->user->id,
            'tipo' => 'SALDO_MINIMO',
            'valor' => 600,
            'activa' => true,
        ]);
        $this->assertDatabaseCount('movimientos', 1);
    }

    private function crearSaldoMinimo(float $valor): void
    {
        ReglaFinanciera::create([
            'user_id' => $this->user->id,
            'categoria_id' => null,
            'tipo' => 'SALDO_MINIMO',
            'valor' => $valor,
            'activa' => true,
        ]);
    }

    private function prepararToolDeEvaluacion(float $monto): void
    {
        $this->prepararTool('evaluar_gasto', $monto);
    }

    private function prepararTool(string $toolName, float $monto): void
    {
        $gemini = Mockery::mock(GeminiService::class);
        $gemini->shouldReceive('analyzeIntent')
            ->once()
            ->withArgs(function (string $prompt, array $tools, ?string $instruction): bool {
                $declaraciones = $tools['functionDeclarations'] ?? [];
                $nombres = array_column($declaraciones, 'name');

                return in_array('evaluar_gasto', $nombres, true)
                    && in_array('registrar_movimiento', $nombres, true)
                    && in_array('configurar_saldo_minimo', $nombres, true)
                    && $instruction !== null
                    && str_contains($instruction, 'me quiero comprar')
                    && str_contains($instruction, 'ya gasté');
            })
            ->andReturn([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'functionCall' => [
                                'name' => $toolName,
                                'args' => ['monto' => $monto],
                            ],
                        ]],
                    ],
                ]],
            ]);

        $this->app->instance(GeminiService::class, $gemini);
    }

    public function test_chat_returns_a_specific_quota_message_when_gemini_is_rate_limited(): void
    {
        $gemini = Mockery::mock(GeminiService::class);
        $gemini->shouldReceive('analyzeIntent')
            ->once()
            ->andThrow(new GeminiApiException('Se alcanzó la cuota disponible de Gemini.', 429));
        $this->app->instance(GeminiService::class, $gemini);

        $this->actingAs($this->user)
            ->postJson(route('assistant.chat'), [
                'mensaje' => 'Me quiero comprar unas zapatillas por 95000',
            ])
            ->assertStatus(429)
            ->assertJsonPath('message', 'Se alcanzó la cuota disponible de Gemini.');

        $this->assertDatabaseCount('movimientos', 1);
    }
}
