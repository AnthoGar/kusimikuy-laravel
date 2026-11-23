@extends('layouts.dashboard')

@section('title', 'Supervisar Traslados | KusiMikuy')

@section('content')
<div class="dashboard-heading">
    <div>
        <h1>Supervisión de traslados</h1>
        <p>Verifica el estado logístico y asigna colaboradores de respaldo si es necesario.</p>
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
        <article class="lote-card">
            <div class="lote-information">
                <div class="lote-title-row">
                    <h3>Traslado #{{ $traslado->id }} — {{ $traslado->solicitud->lote->nombre }}</h3>
                    <span class="lote-status lote-status-{{ $traslado->estado }}">
                        {{ ucfirst(str_replace('_', ' ', $traslado->estado)) }}
                    </span>
                </div>
                <p>
                    <strong>Comedor:</strong> {{ $traslado->solicitud->comedor->nombre }} |
                    <strong>Cantidad:</strong> {{ number_format($traslado->solicitud->cantidad_kg, 2) }} kg
                </p>
                <p>
                    <strong>Colaboradores:</strong>
                    {{ $traslado->colaboradores->isNotEmpty() ? $traslado->colaboradores->pluck('name')->join(', ') : 'Sin asignar' }}
                </p>

                @if ($traslado->estado === 'pendiente')
                    <form action="{{ route('admin.traslados.asignar', $traslado) }}" method="POST" class="lote-actions" style="margin-top:0.75rem;">
                        @csrf
                        <select name="colaborador_id" required style="padding:0.45rem;">
                            <option value="">Seleccionar colaborador...</option>
                            @foreach ($colaboradores as $col)
                                <option value="{{ $col->id }}">{{ $col->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-primary">Asignar colaborador</button>
                    </form>
                @endif
            </div>
        </article>
    @empty
        <div class="lote-empty">
            <h3>No hay traslados registrados aún</h3>
        </div>
    @endforelse

    <div class="lote-pagination">{{ $traslados->links() }}</div>
</section>
@endsection
