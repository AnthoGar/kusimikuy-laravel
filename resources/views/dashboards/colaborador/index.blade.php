@extends('layouts.dashboard')

@section('title', 'Panel del Colaborador | KusiMikuy')

@section('content')
<div class="dashboard-heading">
    <div>
        <h1>¡Bienvenido, {{ auth()->user()->name }}!</h1>
        <p>Ayuda a conectar donantes con comedores populares coordinando traslados.</p>
    </div>
    <a href="{{ route('colaborador.traslados.index') }}" class="btn-primary">Ver traslados disponibles</a>
</div>

<section class="dashboard-stats">
    <article class="dashboard-stat-card">
        <span class="dashboard-stat-label">Traslados pendientes</span>
        <strong>{{ $disponibles }}</strong>
        <span class="dashboard-stat-description">Esperando voluntarios</span>
    </article>

    <article class="dashboard-stat-card">
        <span class="dashboard-stat-label">Mis traslados activos</span>
        <strong>{{ $misActivos }}</strong>
        <span class="dashboard-stat-description">Asignados o en camino</span>
    </article>

    <article class="dashboard-stat-card">
        <span class="dashboard-stat-label">Traslados completados</span>
        <strong>{{ $completados }}</strong>
        <span class="dashboard-stat-description">Entregas exitosas</span>
    </article>
</section>
@endsection
