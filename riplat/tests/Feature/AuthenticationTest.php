<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_user_and_individual_account_and_logs_in(): void
    {
        $response = $this->post(route('register.store'), [
            'username' => 'nueva_persona',
            'email' => 'persona@example.test',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
        ]);

        $user = User::where('email', 'persona@example.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('secure-password-123', $user->password));
        $this->assertDatabaseHas('cuentas', [
            'user_id' => $user->id,
            'nombre' => 'Cuenta principal',
            'moneda' => 'ARS',
        ]);
        $response->assertRedirect(route('dashboard.account', $user->cuenta->id));
    }

    public function test_registration_rejects_duplicate_identity_and_mismatched_passwords(): void
    {
        User::factory()->create([
            'username' => 'ocupado',
            'email' => 'ocupado@example.test',
        ]);

        $response = $this->from(route('register'))->post(route('register.store'), [
            'username' => 'ocupado',
            'email' => 'ocupado@example.test',
            'password' => 'secure-password-123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['username', 'email', 'password']);
    }

    public function test_user_can_login_by_nickname_or_email_and_logout(): void
    {
        $user = User::factory()->create([
            'username' => 'carina_test',
            'email' => 'carina@example.test',
            'password' => 'secure-password-123',
        ]);
        $account = $user->cuenta()->create([
            'nombre' => 'Cuenta principal',
            'moneda' => 'ARS',
        ]);

        $this->post(route('login.store'), [
            'login' => 'carina_test',
            'password' => 'secure-password-123',
        ])->assertRedirect(route('dashboard.account', $account->id));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();

        $this->post(route('login.store'), [
            'login' => 'carina@example.test',
            'password' => 'secure-password-123',
        ])->assertRedirect(route('dashboard.account', $account->id));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_login_returns_a_generic_error(): void
    {
        $response = $this->from(route('login'))->post(route('login.store'), [
            'login' => 'unknown@example.test',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_authenticated_user_opening_login_is_redirected_to_their_home(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->cuenta()->create([
            'nombre' => 'Cuenta principal',
            'moneda' => 'ARS',
        ]);

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('home'));
    }
}
