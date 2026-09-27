<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Riplat')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <main class="auth-shell">
        <a class="auth-brand" href="{{ route('login') }}" aria-label="Riplat, inicio">
            <span class="auth-brand-mark">
                <img src="{{ asset('images/riplat-logo-light.jpg') }}" alt="">
            </span>
            <span>Riplat</span>
        </a>

        @yield('content')

        <p class="auth-privacy">Tus finanzas son personales. Tu espacio también.</p>
    </main>
</body>
</html>