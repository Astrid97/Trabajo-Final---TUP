<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Ingresá tu correo o nickname.',
            'password.required' => 'Ingresá tu contraseña.',
        ]);

        $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';

        if (! Auth::attempt([
            $field => $credentials['login'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            return back()
                ->withErrors(['login' => 'El correo, nickname o contraseña no son correctos.'])
                ->onlyInput('login');
        }

        $request->session()->regenerate();

        $cuenta = $request->user()->cuenta;

        if (! $cuenta) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['login' => 'La cuenta financiera de este usuario no está configurada.'])
                ->onlyInput('login');
        }

        return redirect()->intended(
            route('dashboard.account', $cuenta->id)
        );
    }

    public function showRegistration(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'username' => ['required', 'string', 'max:40', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'username.required' => 'Elegí un nickname.',
            'username.alpha_dash' => 'El nickname solo puede contener letras, números, guiones y guiones bajos.',
            'username.unique' => 'Ese nickname ya está en uso.',
            'email.required' => 'Ingresá tu correo electrónico.',
            'email.email' => 'Ingresá un correo electrónico válido.',
            'email.unique' => 'Ya existe una cuenta con ese correo.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        [$user, $cuenta] = DB::transaction(function () use ($datos): array {
            $user = User::create([
                'username' => $datos['username'],
                'email' => $datos['email'],
                'password' => $datos['password'],
            ]);

            $cuenta = $user->cuenta()->create([
                'nombre' => 'Cuenta principal',
                'moneda' => 'ARS',
            ]);

            return [$user, $cuenta];
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard.account', $cuenta->id);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
