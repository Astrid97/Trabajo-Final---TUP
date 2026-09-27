@php
    $cuentaId = auth()->user()?->cuenta?->id ?? 1;
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>@yield('title', 'Riplat')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
</head>

<body>

<div class="app-shell">

    <aside class="sidebar">

        <div class="brand">
            <div class="brand-symbol">
                <img
                    src="{{ asset('images/riplat-logo-light.jpg') }}"
                    alt=""
                >
            </div>

            <span>Riplat</span>
        </div>

        <nav class="sidebar-nav">

            <a
                href="{{ route('dashboard', $cuentaId) }}"
                class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
            >
                <i class="nav-icon" data-lucide="house" aria-hidden="true"></i>
                <span>Inicio</span>
            </a>

            <a
                href="{{ route('movimientos.index', $cuentaId) }}"
                class="nav-item {{ request()->routeIs('movimientos.index') ? 'active' : '' }}"
            >
                <i class="nav-icon" data-lucide="arrow-left-right" aria-hidden="true"></i>
                <span>Movimientos</span>
            </a>

            <a
                href="{{ route('assistant.index', $cuentaId) }}"
                class="nav-item {{ request()->routeIs('assistant.index') ? 'active' : '' }}"
            >
                <i class="nav-icon" data-lucide="sparkles" aria-hidden="true"></i>
                <span>Chat IA</span>
            </a>

            <a href="#" class="nav-item">
                <i class="nav-icon" data-lucide="circle-user-round" aria-hidden="true"></i>
                <span>Perfil</span>
            </a>

        </nav>

        <div class="sidebar-slogan">
            Entendé tu dinero.<br>
            Decidí tu futuro.
        </div>

    </aside>

    <main class="main-content">
        @yield('content')
    </main>

</div>

<nav class="mobile-nav">

    <a
        href="{{ route('dashboard', $cuentaId) }}"
        class="mobile-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
    >
        <i class="nav-icon" data-lucide="house" aria-hidden="true"></i>
        <small>Inicio</small>
    </a>

    <a
        href="{{ route('movimientos.index', $cuentaId) }}"
        class="mobile-nav-item {{ request()->routeIs('movimientos.index') ? 'active' : '' }}"
    >
        <i class="nav-icon" data-lucide="arrow-left-right" aria-hidden="true"></i>
        <small>Movimientos</small>
    </a>

    <a
        href="{{ route('assistant.index', $cuentaId) }}"
        class="mobile-nav-item {{ request()->routeIs('assistant.index') ? 'active' : '' }}"
    >
        <i class="nav-icon" data-lucide="sparkles" aria-hidden="true"></i>
        <small>Chat IA</small>
    </a>

    <a href="#" class="mobile-nav-item">
        <i class="nav-icon" data-lucide="circle-user-round" aria-hidden="true"></i>
        <small>Perfil</small>
    </a>

</nav>
@stack('scripts')
</body>
</html>