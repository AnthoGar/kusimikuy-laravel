<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Panel de gestión de KusiMikuy.">

    <title>@yield('title', 'Panel | KusiMikuy')</title>

    <link rel="icon" href="{{ asset('img/logo.jpg') }}" type="image/jpeg">
    <link rel="stylesheet" href="{{ asset('css/kusimikuy.css') }}">

    @stack('styles')
</head>

<body class="dashboard-body">

    <div class="dashboard-layout">

        {{-- Barra lateral --}}
        <aside class="dashboard-sidebar">

            <a href="{{ route('home') }}" class="dashboard-brand">
                <img src="{{ asset('img/logo.jpg') }}" alt="KusiMikuy">
                <span>KusiMikuy</span>
            </a>

            <div class="dashboard-user">
                <span class="dashboard-user-name">
                    {{ auth()->user()->name }}
                </span>

                <span class="dashboard-user-role">
                    {{ auth()->user()->role?->name }}
                </span>
            </div>

            <nav class="dashboard-navigation">

                <span class="dashboard-nav-title">MENÚ PRINCIPAL</span>

                @if (auth()->user()->role?->slug === 'donante')
                <a href="{{ route('donante.dashboard') }}"
                    class="dashboard-nav-link active">
                    <span>⌂</span>
                    Mi panel
                </a>
                @endif

            </nav>

            <div class="dashboard-sidebar-footer">
                <a href="{{ route('home') }}" class="dashboard-nav-link">
                    Volver al sitio web
                </a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dashboard-logout">
                        Cerrar sesión
                    </button>
                </form>
            </div>

        </aside>

        {{-- Contenido principal --}}
        <div class="dashboard-main">

            <header class="dashboard-topbar">
                <span>Plataforma de gestión</span>
                <span>{{ auth()->user()->name }}</span>
            </header>

            <main class="dashboard-content">
                @yield('content')
            </main>

        </div>

    </div>

    <script src="{{ asset('js/kusimikuy.js') }}" defer></script>
    @stack('scripts')

</body>

</html>