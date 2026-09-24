<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>@yield('title', 'Riplat')</title>

    @vite(['resources/css/app.css'])
    
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

            <a href="#" class="nav-item active">
                <span class="nav-icon">⌂</span>
                <span>Inicio</span>
            </a>

            <a href="#" class="nav-item">
                <span class="nav-icon">↕</span>
                <span>Movimientos</span>
            </a>

            <a href="#" class="nav-item">
                <span class="nav-icon">✦</span>
                <span>Chat IA</span>
            </a>

            <a href="#" class="nav-item">
                <span class="nav-icon">○</span>
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

    <a href="#" class="mobile-nav-item active">
        <span>⌂</span>
        <small>Inicio</small>
    </a>

    <a href="#" class="mobile-nav-item">
        <span>↕</span>
        <small>Movimientos</small>
    </a>

    <a href="#" class="mobile-nav-item">
        <span>✦</span>
        <small>Chat IA</small>
    </a>

    <a href="#" class="mobile-nav-item">
        <span>○</span>
        <small>Perfil</small>
    </a>

</nav>

</body>
</html>