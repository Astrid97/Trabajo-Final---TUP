@extends('layouts.guest')

@section('title', 'Ingresar | Riplat')

@section('content')
<section class="auth-form-panel" aria-labelledby="auth-title">
    <p class="eyebrow">Tu espacio financiero</p>
    <h1 id="auth-title">Qué bueno verte</h1>
    <p class="auth-intro">Ingresá para continuar con tus finanzas.</p>

    <form class="auth-form" method="POST" action="{{ route('login.store') }}">
        @csrf

        <div class="auth-field">
            <label for="login">Correo o nickname</label>
            <input
                id="login"
                name="login"
                type="text"
                value="{{ old('login') }}"
                autocomplete="username"
                required
                autofocus
                aria-describedby="login-error"
            >
            @error('login')
                <p class="auth-error" id="login-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password">Contraseña</label>
            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
                aria-describedby="password-error"
            >
            @error('password')
                <p class="auth-error" id="password-error">{{ $message }}</p>
            @enderror
        </div>

        <label class="auth-remember" for="remember">
            <input id="remember" name="remember" type="checkbox" value="1">
            <span>Mantener sesión iniciada</span>
        </label>

        <button class="primary-button auth-submit" type="submit">Ingresar</button>
    </form>

    <p class="auth-switch">
        ¿Todavía no tenés cuenta?
        <a href="{{ route('register') }}">Crear cuenta</a>
    </p>
</section>
@endsection