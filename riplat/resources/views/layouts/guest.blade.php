<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#11cec7">
    <meta name="application-name" content="Riplat">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('pwa-icon-192.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('pwa-icon-180.png') }}">
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
    @include('components.pwa-install')
    @stack('scripts')
</body>
</html>