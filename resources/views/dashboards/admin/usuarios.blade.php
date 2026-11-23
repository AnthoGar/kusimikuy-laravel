@extends('layouts.dashboard')

@section('title', 'Usuarios y Roles | KusiMikuy')

@section('content')
<div class="dashboard-heading">
    <div>
        <h1>Gestión de usuarios</h1>
        <p>Supervisa las cuentas activas por rol dentro de la plataforma.</p>
    </div>
</div>

@if (session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

<section class="dashboard-section">
    @foreach ($usuarios as $usuario)
        <article class="lote-card">
            <div class="lote-information">
                <div class="lote-title-row">
                    <h3>{{ $usuario->name }} ({{ $usuario->email }})</h3>
                    <span class="lote-status {{ $usuario->is_active ? 'lote-status-disponible' : 'lote-status-pausado' }}">
                        {{ $usuario->is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
                <p class="lote-category">Rol: <strong>{{ $usuario->role?->name ?? 'Sin rol' }}</strong></p>

                <div class="lote-actions">
                    <form action="{{ route('admin.usuarios.toggle', $usuario) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-secondary">
                            {{ $usuario->is_active ? 'Desactivar cuenta' : 'Activar cuenta' }}
                        </button>
                    </form>
                </div>
            </div>
        </article>
    @endforeach

    <div class="lote-pagination">{{ $usuarios->links() }}</div>
</section>
@endsection
