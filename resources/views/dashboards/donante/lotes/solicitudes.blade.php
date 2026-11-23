@extends('layouts.dashboard')

@section('title', 'Solicitudes recibidas | KusiMikuy')

@section('content')
<div class="dashboard-heading">
    <div>
        <h1>Solicitudes de rescate</h1>
        <p>Revisa y responde las solicitudes de los comedores populares.</p>
    </div>
</div>

@if (session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert-error" style="color:#991b1b; background:#fee2e2; padding:1rem; border-radius:8px; margin-bottom:1rem;">
        {{ session('error') }}
    </div>
@endif

<section class="dashboard-section">
    @forelse ($solicitudes as $solicitud)
        <article class="lote-card">
            <div class="lote-information">
                <div class="lote-title-row">
                    <h3>{{ $solicitud->lote->nombre }} — {{ number_format($solicitud->cantidad_kg, 2) }} kg</h3>
                    <span class="lote-status lote-status-{{ $solicitud->estado }}">
                        {{ ucfirst($solicitud->estado) }}
                    </span>
                </div>

                <p class="lote-category">
                    Comedor solicitante: <strong>{{ $solicitud->comedor->nombre }}</strong> ({{ $solicitud->comedor->distrito }})
                </p>
                <p>Dirección de entrega: {{ $solicitud->comedor->direccion }} | Raciones diarias: {{ $solicitud->comedor->raciones_diarias }}</p>

                @if ($solicitud->traslado)
                    <p style="margin-top:0.5rem;">
                        <strong>Estado del traslado:</strong> {{ ucfirst(str_replace('_', ' ', $solicitud->traslado->estado)) }}
                        @if($solicitud->traslado->colaboradores->isNotEmpty())
                            (Colaborador: {{ $solicitud->traslado->colaboradores->pluck('name')->join(', ') }})
                        @endif
                    </p>
                @endif

                @if ($solicitud->estado === 'pendiente')
                    <div class="lote-actions">
                        <form action="{{ route('donante.solicitudes.aceptar', $solicitud) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-primary">Aceptar y programar traslado</button>
                        </form>

                        <form action="{{ route('donante.solicitudes.rechazar', $solicitud) }}" method="POST" onsubmit="return confirm('¿Rechazar esta solicitud?')">
                            @csrf
                            <button type="submit" class="btn-secondary">Rechazar</button>
                        </form>
                    </div>
                @endif
            </div>
        </article>
    @empty
        <div class="lote-empty">
            <h3>Aún no tienes solicitudes de rescate</h3>
            <p>Cuando un comedor solicite alguno de tus lotes disponibles aparecerá aquí.</p>
        </div>
    @endforelse

    <div class="lote-pagination">{{ $solicitudes->links() }}</div>
</section>
@endsection
