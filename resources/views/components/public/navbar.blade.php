<header class="site-header">
    <nav class="navbar container" aria-label="Navegación principal">

        <a href="{{ url('/') }}" class="brand">
            <img src="{{ asset('img/logo.jpg') }}" alt="Logo de KusiMikuy">
            <span>KusiMikuy</span>
        </a>

        <button class="menu-toggle" id="menu-toggle" type="button" aria-label="Abrir menú" aria-expanded="false"
            aria-controls="main-menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div class="nav-content" id="main-menu">

            <div class="nav-links">
                <a href="{{ url('/#proposito') }}">Nuestro propósito</a>
                <a href="{{ url('/#como-funciona') }}">Cómo funciona</a>
                <a href="{{ url('/#comunidad') }}">Comunidad</a>
                <a href="{{ url('/#impacto') }}">Impacto</a>
            </div>

            <div class="nav-actions">

                @guest
                    {{-- Opciones para visitantes --}}
                    <a href="{{ route('login') }}" class="nav-login">
                        Iniciar sesión
                    </a>

                    <a href="{{ route('register') }}" class="btn btn-primary">
                        Únete ahora
                    </a>
                @endguest


                @auth
                    {{-- Opciones para usuarios autenticados --}}
                    @php
                        $dashboardRoute = match (auth()->user()->role?->slug) {
                            'donante' => 'donante.dashboard',
                            'comedor' => 'comedor.dashboard',
                            'colaborador' => 'colaborador.dashboard',
                            'administrador' => 'admin.dashboard',
                            default => null,
                        };
                    @endphp

                    <span class="nav-user">
                        Hola, {{ auth()->user()->name }}
                    </span>

                    @if ($dashboardRoute)
                        <a href="{{ route($dashboardRoute) }}" class="btn btn-primary">
                            Mi panel
                        </a>
                    @endif

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-login">
                            Cerrar sesión
                        </button>
                    </form>
                @endauth


            </div>
        </div>

    </nav>
</header>