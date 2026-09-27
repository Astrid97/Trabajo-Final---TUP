@php
    $cuentaId = auth()->user()->cuenta?->id;
    abort_unless($cuentaId, 403);
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
                href="{{ route('dashboard.account', $cuentaId) }}"
                class="nav-item {{ request()->routeIs('dashboard.account') ? 'active' : '' }}"
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

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="nav-item" type="submit">
                    <i class="nav-icon" data-lucide="log-out" aria-hidden="true"></i>
                    <span>Cerrar sesión</span>
                </button>
            </form>

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
        href="{{ route('dashboard.account', $cuentaId) }}"
        class="mobile-nav-item {{ request()->routeIs('dashboard.account') ? 'active' : '' }}"
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

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="mobile-nav-item" type="submit" aria-label="Cerrar sesión">
            <i class="nav-icon" data-lucide="log-out" aria-hidden="true"></i>
            <small>Salir</small>
        </button>
    </form>

</nav>
@stack('scripts')
</body>
</html>