<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Movimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_financial_pages_or_apis(): void
    {
        $user = User::factory()->create();
        $account = $user->cuenta()->create([
            'nombre' => 'Cuenta principal',
            'moneda' => 'ARS',
        ]);

        $this->get(route('dashboard.account', $account->id))
            ->assertRedirect(route('login'));
        $this->get(route('movimientos.index', $account->id))
            ->assertRedirect(route('login'));
        $this->get(route('assistant.index', $account->id))
            ->assertRedirect(route('login'));
        $this->post(route('assistant.chat'), ['mensaje' => 'Hola'])
            ->assertRedirect(route('login'));
    }

    public function test_user_cannot_read_another_users_dashboard_movements_or_expense_context(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        $ownerAccount = $owner->cuenta()->create([
            'nombre' => 'Cuenta dueña',
            'moneda' => 'ARS',
        ]);
        $otherUser = User::factory()->create();
        $otherAccount = $otherUser->cuenta()->create([
            'nombre' => 'Cuenta ajena',
            'moneda' => 'ARS',
        ]);

        $this->actingAs($owner)
            ->get(route('dashboard.account', $otherAccount->id))
            ->assertNotFound();
        $this->get(route('movimientos.index', $otherAccount->id))
            ->assertNotFound();
        $this->get(route('financial-context.evaluate', [
            'cuentaId' => $otherAccount->id,
            'monto' => 100,
        ]))->assertNotFound();

        $this->get(route('dashboard.account', $ownerAccount->id))
            ->assertOk();
    }

    public function test_movement_creation_uses_authenticated_account_even_if_client_spoofs_account_id(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        $ownerAccount = $owner->cuenta()->create([
            'nombre' => 'Cuenta propia',
            'moneda' => 'ARS',
        ]);
        $otherUser = User::factory()->create();
        $otherAccount = $otherUser->cuenta()->create([
            'nombre' => 'Cuenta ajena',
            'moneda' => 'ARS',
        ]);
        $category = Categoria::create([
            'nombre' => 'GASTOS TEST',
            'tipo' => 'GASTO',
        ]);

        $this->actingAs($owner)
            ->post(route('movimientos.store'), [
                'cuenta_id' => $otherAccount->id,
                'categoria_id' => $category->id,
                'tipo' => 'GASTO',
                'monto' => 1250,
                'fecha' => now()->toDateString(),
                'descripcion' => 'Compra de prueba',
            ])
            ->assertRedirect(route('movimientos.index', $ownerAccount->id));

        $this->assertDatabaseHas('movimientos', [
            'cuenta_id' => $ownerAccount->id,
            'categoria_id' => $category->id,
            'descripcion' => 'Compra de prueba',
        ]);
        $this->assertDatabaseMissing('movimientos', [
            'cuenta_id' => $otherAccount->id,
            'descripcion' => 'Compra de prueba',
        ]);
    }

    public function test_user_cannot_update_another_accounts_movement(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        $owner->cuenta()->create([
            'nombre' => 'Cuenta propia',
            'moneda' => 'ARS',
        ]);
        $otherUser = User::factory()->create();
        $otherAccount = $otherUser->cuenta()->create([
            'nombre' => 'Cuenta ajena',
            'moneda' => 'ARS',
        ]);
        $category = Categoria::create([
            'nombre' => 'GASTO TEST',
            'tipo' => 'GASTO',
        ]);
        $movement = Movimiento::create([
            'cuenta_id' => $otherAccount->id,
            'categoria_id' => $category->id,
            'tipo' => 'GASTO',
            'monto' => 100,
            'fecha' => now()->toDateString(),
            'descripcion' => 'Ajeno',
            'estado' => 'CONFIRMADO',
        ]);

        $this->actingAs($owner)
            ->put(route('movimientos.update', $movement->id), [
                'descripcion' => 'Intento de edición',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('movimientos', [
            'id' => $movement->id,
            'descripcion' => 'Ajeno',
        ]);
    }
}
