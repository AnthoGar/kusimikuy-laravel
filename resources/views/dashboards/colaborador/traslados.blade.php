@extends('layouts.dashboard')

@section('title', 'Traslados y Rutas | KusiMikuy')

@section('content')
<div class="dashboard-heading">
    <div>
        <h1>Traslados de alimentos</h1>
        <p>Postúlate a un traslado pendiente e inicia el recorrido cuando recojas el lote.</p>
    </div>
</div>

@if (session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div style="color:#991b1b; background:#fee2e2; padding:1rem; border-radius:8px; margin-bottom:1rem;">
        {{ session('error') }}
    </div>
@endif

<section class="dashboard-section">
    @forelse ($traslados as $traslado)
        @php
            $estoyAsignado = $traslado->colaboradores->contains('id', auth()->id());
        @endphp

        <article class="lote-card">
            <div class="lote-information">
                <div class="lote-title-row">
                    <h3>
                        {{ $traslado->solicitud->lote->nombre }}
                        ({{ number_format($traslado->solicitud->cantidad_kg, 2) }} kg)
                    </h3>
                    <span class="lote-status lote-status-{{ $traslado->estado }}">
                        {{ ucfirst(str_replace('_', ' ', $traslado->estado)) }}
                    </span>
                </div>

                <p class="lote-category">
                    <strong>Origen (Donante):</strong> {{ $traslado->solicitud->lote->donor->name }}
                </p>
                <p>
                    <strong>Destino (Comedor):</strong> {{ $traslado->solicitud->comedor->nombre }} —
                    {{ $traslado->solicitud->comedor->direccion }} ({{ $traslado->solicitud->comedor->distrito }})
                </p>
                <p>
                    <strong>Recojo máximo:</strong> {{ $traslado->solicitud->lote->recojo_maximo->format('d/m/Y H:i') }}
                </p>

                @if ($traslado->colaboradores->isNotEmpty())
                    <p style="margin-top:0.5rem;">
                        <strong>Voluntarios asignados:</strong> {{ $traslado->colaboradores->pluck('name')->join(', ') }}
                    </p>
                @endif

                <div class="lote-actions">
                    @if ($traslado->estado === 'pendiente' && !$estoyAsignado)
                        <form action="{{ route('colaborador.traslados.asignar', $traslado) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-primary">Asignarme este traslado</button>
                        </form>
                    @endif

                    @if ($traslado->estado === 'pendiente' && $estoyAsignado)
                        <form action="{{ route('colaborador.traslados.iniciar', $traslado) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-primary">Ya recogí el lote — Iniciar traslado</button>
                        </form>
                    @endif

                    @if ($traslado->estado === 'en_camino' && $estoyAsignado)
                        <span style="color:#166534; font-weight:600;">
                            En camino hacia el comedor. El comedor confirmará al recibir.
                        </span>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <div class="lote-empty">
            <h3>No hay traslados registrados</h3>
            <p>Cuando un donante acepte una solicitud de rescate aparecerá aquí.</p>
        </div>
    @endforelse

    <div class="lote-pagination">{{ $traslados->links() }}</div>
</section>
@endsection
