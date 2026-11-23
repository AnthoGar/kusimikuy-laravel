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
                <a href="#proposito">Nuestro propósito</a>
                <a href="#como-funciona">Cómo funciona</a>
                <a href="#comunidad">Comunidad</a>
                <a href="#impacto">Impacto</a>
            </div>

            <div class="nav-actions">
                <a href="#comunidad" class="nav-login">
                    Iniciar sesión
                </a>

                <a href="#comunidad" class="btn btn-primary">
                    Únete ahora
                </a>
            </div>
        </div>

    </nav>
</header>