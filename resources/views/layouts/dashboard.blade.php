<nav class="dashboard-navigation">
    <span class="dashboard-nav-title">MENÚ PRINCIPAL</span>

    @php $slug = auth()->user()->role?->slug; @endphp

    @if ($slug === 'donante')
        <a href="{{ route('donante.dashboard') }}" class="dashboard-nav-link {{ request()->routeIs('donante.dashboard') ? 'active' : '' }}">
            <span>⌂</span> Mi panel
        </a>
        <a href="{{ route('donante.lotes.index') }}" class="dashboard-nav-link {{ request()->routeIs('donante.lotes.*') ? 'active' : '' }}">
            <span>▤</span> Mis lotes
        </a>
        <a href="{{ route('donante.solicitudes.index') }}" class="dashboard-nav-link {{ request()->routeIs('donante.solicitudes.*') ? 'active' : '' }}">
            <span>✓</span> Solicitudes recibidas
        </a>
    @elseif ($slug === 'comedor')
        <a href="{{ route('comedor.dashboard') }}" class="dashboard-nav-link {{ request()->routeIs('comedor.dashboard') ? 'active' : '' }}">
            <span>⌂</span> Mi panel
        </a>
        <a href="{{ route('comedor.catalogo') }}" class="dashboard-nav-link {{ request()->routeIs('comedor.catalogo') ? 'active' : '' }}">
            <span>◈</span> Alimentos disponibles
        </a>
        <a href="{{ route('comedor.solicitudes.index') }}" class="dashboard-nav-link {{ request()->routeIs('comedor.solicitudes.*') ? 'active' : '' }}">
            <span>✓</span> Mis rescates y entregas
        </a>
        <a href="{{ route('comedor.perfil') }}" class="dashboard-nav-link {{ request()->routeIs('comedor.perfil') ? 'active' : '' }}">
            <span>⚙</span> Datos del comedor
        </a>
    @elseif ($slug === 'colaborador')
        <a href="{{ route('colaborador.dashboard') }}" class="dashboard-nav-link {{ request()->routeIs('colaborador.dashboard') ? 'active' : '' }}">
            <span>⌂</span> Mi panel
        </a>
        <a href="{{ route('colaborador.traslados.index') }}" class="dashboard-nav-link {{ request()->routeIs('colaborador.traslados.*') ? 'active' : '' }}">
            <span>⇄</span> Traslados y rutas
        </a>
    @elseif ($slug === 'administrador')
        <a href="{{ route('admin.dashboard') }}" class="dashboard-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <span>⌂</span> Resumen general
        </a>
        <a href="{{ route('admin.usuarios.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">
            <span>♟</span> Usuarios y roles
        </a>
        <a href="{{ route('admin.traslados.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.traslados.*') ? 'active' : '' }}">
            <span>⇄</span> Supervisar traslados
        </a>
    @endif
</nav>
