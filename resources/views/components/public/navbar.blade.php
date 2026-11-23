<header class="site-header">
    <nav class="navbar container" aria-label="Navegación principal">

        <a href="{{ url('/') }}" class="brand">
            <img src="{{ asset('img/logo.jpg') }}" alt="Logo de KusiMikuy">
            <span>KusiMikuy</span>
        </a>

        <button
            class="menu-toggle"
            id="menu-toggle"
            type="button"
            aria-label="Abrir menú"
            aria-expanded="false"
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
                <span class="nav-user">
                    Hola, {{ auth()->user()->name }}
                </span>

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