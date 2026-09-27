<?php

namespace Tests\Feature;

use App\Models\Cuenta;
use App\Models\ReglaFinanciera;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MinimumBalanceSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_set_and_edit_their_minimum_balance(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $cuenta = $this->crearCuenta($user);

        $this->actingAs($user)
            ->putJson(route('financial-context.minimum-balance.update', $cuenta->id), [
                'monto' => 500,
            ])
            ->assertOk()
            ->assertJsonPath('saldo_minimo', 500);

        $reglaId = ReglaFinanciera::sole()->id;

        $this->putJson(route('financial-context.minimum-balance.update', $cuenta->id), [
            'monto' => 750.50,
        ])
            ->assertOk()
            ->assertJsonPath('saldo_minimo', 750.5);

        $this->assertDatabaseCount('reglas_financieras', 1);
        $this->assertDatabaseHas('reglas_financieras', [
            'id' => $reglaId,
            'user_id' => $user->id,
            'tipo' => 'SALDO_MINIMO',
            'valor' => 750.50,
            'activa' => true,
        ]);

        $this->get(route('profile', $cuenta->id))
            ->assertOk()
            ->assertViewHas('saldoMinimo', 750.5);

        $this->getJson(route('financial-context.evaluate', [
            'cuentaId' => $cuenta->id,
            'monto' => 100,
        ]))
            ->assertOk()
            ->assertJsonPath('evaluacion.saldo_minimo', 750.5)
            ->assertJsonPath('evaluacion.cumple', false);
    }

    public function test_user_cannot_configure_another_users_minimum_balance(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        User::factory()->create();
        $otherUser = User::query()->whereKeyNot($user->id)->firstOrFail();
        $otherCuenta = $this->crearCuenta($otherUser);

        $this->actingAs($user)
            ->putJson(route('financial-context.minimum-balance.update', $otherCuenta->id), [
                'monto' => 500,
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('reglas_financieras', 0);
    }

    public function test_profile_form_saves_the_rule_and_returns_confirmation(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $cuenta = $this->crearCuenta($user);

        $this->actingAs($user)
            ->from(route('profile', $cuenta->id))
            ->put(route('financial-context.minimum-balance.update', $cuenta->id), [
                'monto' => 125000,
            ])
            ->assertRedirect(route('profile', $cuenta->id))
            ->assertSessionHas('status', 'Tu saldo mínimo quedó actualizado.');

        $this->get(route('profile', $cuenta->id))
            ->assertOk()
            ->assertViewHas('saldoMinimo', 125000.0);
    }

    public function test_minimum_balance_requires_a_positive_amount(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $cuenta = $this->crearCuenta($user);

        $this->actingAs($user)
            ->putJson(route('financial-context.minimum-balance.update', $cuenta->id), [
                'monto' => 0,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('monto');

        $this->assertDatabaseCount('reglas_financieras', 0);
    }

    private function crearCuenta(User $user): Cuenta
    {
        return $user->cuenta()->create([
            'nombre' => 'Cuenta principal',
            'moneda' => 'ARS',
        ]);
    }
}
