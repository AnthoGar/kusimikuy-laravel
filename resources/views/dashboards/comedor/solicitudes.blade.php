@extends('layouts.dashboard')

@section('title', 'Mis Rescates y Entregas | KusiMikuy')

@section('content')
<div class="dashboard-heading">
    <div>
        <h1>Mis solicitudes de rescate</h1>
        <p>Haz seguimiento a tus pedidos y confirma la recepción cuando lleguen a tu comedor.</p>
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
    @forelse ($solicitudes as $solicitud)
        <article class="lote-card">
            <div class="lote-information">
                <div class="lote-title-row">
                    <h3>{{ $solicitud->lote->nombre }} ({{ number_format($solicitud->cantidad_kg, 2) }} kg)</h3>
                    <span class="lote-status lote-status-{{ $solicitud->estado }}">
                        Solicitud: {{ ucfirst($solicitud->estado) }}
                    </span>
                </div>

                <p class="lote-category">Donante: {{ $solicitud->lote->donor->name }}</p>

                @if ($solicitud->traslado)
                    <p>
                        <strong>Estado de traslado:</strong> {{ ucfirst(str_replace('_', ' ', $solicitud->traslado->estado)) }}
                        @if($solicitud->traslado->colaboradores->isNotEmpty())
                            • Trasladado por: {{ $solicitud->traslado->colaboradores->pluck('name')->join(', ') }}
                        @endif
                    </p>

                    {{-- Si el traslado está en camino, mostrar formulario para confirmar entrega --}}
                    @if ($solicitud->traslado->estado === 'en_camino')
                        <form action="{{ route('comedor.entregas.store', $solicitud->traslado) }}" method="POST" style="margin-top:1rem; padding:1rem; background:#f8f7f2; border-radius:8px; display:grid; gap:0.75rem;">
                            @csrf
                            <strong>Confirmar recepción de alimentos:</strong>
                            <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                                <div>
                                    <label>Kg recibidos:</label>
                                    <input type="number" name="cantidad_recibida_kg" step="0.01" max="{{ $solicitud->cantidad_kg }}" value="{{ $solicitud->cantidad_kg }}" required style="width:110px; padding:0.4rem;">
                                </div>
                                <div>
                                    <label>Conservación:</label>
                                    <select name="estado_conservacion" required style="padding:0.4rem;">
                                        <option value="bueno">Bueno</option>
                                        <option value="regular">Regular</option>
                                        <option value="malo">Malo</option>
                                    </select>
                                </div>
                                <div>
                                    <label>Calidad (1-5):</label>
                                    <select name="calificacion_calidad" style="padding:0.4rem;">
                                        <option value="5">5 - Excelente</option>
                                        <option value="4">4 - Muy buena</option>
                                        <option value="3">3 - Aceptable</option>
                                        <option value="2">2 - Baja</option>
                                        <option value="1">1 - Mala</option>
                                    </select>
                                </div>
                            </div>
                            <input type="text" name="comentarios" placeholder="Comentarios opcionales sobre la entrega..." style="padding:0.4rem;">
                            <div>
                                <button type="submit" class="btn-primary">Confirmar entrega recibida</button>
                            </div>
                        </form>
                    @elseif ($solicitud->traslado->entrega)
                        <p style="color:#166534; margin-top:0.5rem;">
                            ✓ Entregado el {{ $solicitud->traslado->entrega->recibido_at->format('d/m/Y H:i') }}
                            ({{ number_format($solicitud->traslado->entrega->cantidad_recibida_kg, 2) }} kg recibidos).
                        </p>
                    @endif
                @endif
            </div>
        </article>
    @empty
        <div class="lote-empty">
            <h3>Aún no has realizado solicitudes</h3>
            <a href="{{ route('comedor.catalogo') }}" class="btn-primary">Ver alimentos disponibles</a>
        </div>
    @endforelse

    <div class="lote-pagination">{{ $solicitudes->links() }}</div>
</section>
@endsection
