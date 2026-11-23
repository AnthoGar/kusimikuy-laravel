@extends('layouts.dashboard')

@section('title', 'Panel del Donante | KusiMikuy')

@section('content')

<div class="dashboard-heading">
    <h1>¡Bienvenido, {{ auth()->user()->name }}!</h1>

    <p>
        Gestiona tus alimentos y ayuda a generar impacto
        en tu comunidad.
    </p>
</div>

<section class="dashboard-stats">

    <article class="dashboard-stat-card">
        <span class="dashboard-stat-label">Lotes publicados</span>
        <strong>{{ $totalLotes }}</strong>
        <span class="dashboard-stat-description">
            Total de lotes registrados
        </span>
    </article>

    <article class="dashboard-stat-card">
        <span class="dashboard-stat-label">Solicitudes pendientes</span>
        <strong>{{ $solicitudesPendientes }}</strong>
        <span class="dashboard-stat-description">
            Esperando tu respuesta
        </span>
    </article>

    <article class="dashboard-stat-card">
        <span class="dashboard-stat-label">Entregas completadas</span>
        <strong>{{ $entregasCompletadas }}</strong>
        <span class="dashboard-stat-description">
            Alimentos entregados
        </span>
    </article>

    <article class="dashboard-stat-card">
        <span class="dashboard-stat-label">Kilogramos rescatados</span>
        <strong>{{ number_format($kgRescatados, 2) }} kg</strong>
        <span class="dashboard-stat-description">
            Cantidad efectivamente recibida
        </span>
    </article>

</section>

@endsection