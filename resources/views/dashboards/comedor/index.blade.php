@extends('layouts.dashboard')

@section('title', 'Panel del Comedor | KusiMikuy')

@section('content')
<div class="dashboard-heading">
    <div>
        <h1>¡Bienvenido, {{ $comedor->nombre }}!</h1>
        <p>Solicita alimentos disponibles y gestiona tus entregas para la comunidad.</p>
    </div>
    <a href="{{ route('comedor.catalogo') }}" class="btn-primary">+ Explorar alimentos</a>
</div>

@if (session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

<section class="dashboard-stats">
    <article class="dashboard-stat-card">
        <span class="dashboard-stat-label">Solicitudes activas</span>
        <strong>{{ $solicitudesActivas }}</strong>
        <span class="dashboard-stat-description">Pendientes o en traslado</span>
    </article>

    <article class="dashboard-stat-card">
        <span class="dashboard-stat-label">Entregas recibidas</span>
        <strong>{{ $entregasRecibidas }}</strong>
        <span class="dashboard-stat-description">Rescates completados</span>
    </article>

    <article class="dashboard-stat-card">
        <span class="dashboard-stat-label">Kilogramos recibidos</span>
        <strong>{{ number_format($kgRecibidos, 2) }} kg</strong>
        <span class="dashboard-stat-description">Total rescatado para raciones</span>
    </article>

    <article class="dashboard-stat-card">
        <span class="dashboard-stat-label">Capacidad diaria</span>
        <strong>{{ $comedor->raciones_diarias }}</strong>
        <span class="dashboard-stat-description">Raciones en {{ $comedor->distrito }}</span>
    </article>
</section>
@endsection
