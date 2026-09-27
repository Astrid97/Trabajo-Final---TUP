@extends('layouts.guest')

@section('title', 'Crear cuenta | Riplat')

@section('content')
<section class="auth-form-panel" aria-labelledby="auth-title">
    <p class="eyebrow">Un comienzo más claro</p>
    <h1 id="auth-title">Creá tu cuenta</h1>
    <p class="auth-intro">Solo necesitamos tu nickname, correo y contraseña.</p>

    <form class="auth-form" method="POST" action="{{ route('register.store') }}">
        @csrf

        <div class="auth-field">
            <label for="username">Nickname</label>
            <input
                id="username"
                name="username"
                type="text"
                value="{{ old('username') }}"
                maxlength="40"
                autocomplete="username"
                required
                autofocus
                aria-describedby="username-error"
            >
            @error('username')
                <p class="auth-error" id="username-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-field">
            <label for="email">Correo electrónico</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                autocomplete="email"
                required
                aria-describedby="email-error"
            >
            @error('email')
                <p class="auth-error" id="email-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password">Contraseña</label>
            <input
                id="password"
                name="password"
                type="password"
                minlength="8"
                autocomplete="new-password"
                required
                aria-describedby="password-error"
            >
            @error('password')
                <p class="auth-error" id="password-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password_confirmation">Repetir contraseña</label>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                minlength="8"
                autocomplete="new-password"
                required
            >
        </div>

        <button class="primary-button auth-submit" type="submit">Crear cuenta</button>
    </form>

    <p class="auth-switch">
        ¿Ya tenés cuenta?
        <a href="{{ route('login') }}">Ingresar</a>
    </p>
</section>
@endsection