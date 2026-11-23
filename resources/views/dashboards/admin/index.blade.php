@extends('layouts.dashboard')

@section('title', 'Panel Administrador | KusiMikuy')

@section('content')
<div class="dashboard-heading">
    <div>
        <h1>Supervisión e Impacto Global</h1>
        <p>Monitorea las operaciones, entregas reales y transparencia de KusiMikuy.</p>
    </div>
</div>

<section class="dashboard-stats">
    <article class="dashboard-stat-card">
        <span class="dashboard-stat-label">Kilogramos rescatados</span>
        <strong>{{ number_format($impacto['kg_rescatados'], 2) }} kg</strong>
        <span class="dashboard-stat-description">Alimentos entregados con éxito</span>
    </article>

    <article class="dashboard-stat-card">
        <span class="dashboard-stat-label">Entregas completadas</span>
        <strong>{{ $impacto['entregas_completadas'] }}</strong>
        <span class="dashboard-stat-description">Traslados finalizados</span>
    </article>

    <article class="dashboard-stat-card">
        <span class="dashboard-stat-label">Comedores beneficiados</span>
        <strong>{{ $impacto['comedores_beneficiados'] }}</strong>
        <span class="dashboard-stat-description">Comedores con recepción activa</span>
    </article>

    <article class="dashboard-stat-card">
        <span class="dashboard-stat-label">Usuarios / Lotes activos</span>
        <strong>{{ $totalUsuarios }} / {{ $lotesActivos }}</strong>
        <span class="dashboard-stat-description">Comunidad registrada y lotes vigentes</span>
    </article>
</section>
@endsection
