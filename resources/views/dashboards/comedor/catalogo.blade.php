@extends('layouts.dashboard')

@section('title', 'Alimentos Disponibles | KusiMikuy')

@section('content')
<div class="dashboard-heading">
    <div>
        <h1>Alimentos disponibles para rescate</h1>
        <p>Solicita los lotes que tu comedor necesita antes de su hora límite de recojo.</p>
    </div>
</div>

@if (session('error'))
    <div style="color:#991b1b; background:#fee2e2; padding:1rem; border-radius:8px; margin-bottom:1rem;">
        {{ session('error') }}
    </div>
@endif

<section class="dashboard-section">
    @forelse ($lotes as $lote)
        <article class="lote-card">
            @if ($lote->imagen)
                <img src="{{ asset('storage/' . $lote->imagen) }}" alt="{{ $lote->nombre }}" class="lote-image">
            @else
                <div class="lote-image-placeholder">Sin imagen</div>
            @endif

            <div class="lote-information">
                <div class="lote-title-row">
                    <h3>{{ $lote->nombre }}</h3>
                    <span class="lote-status lote-status-disponible">{{ ucfirst($lote->estado_conservacion) }}</span>
                </div>

                <p class="lote-category">{{ $lote->categoria->nombre }} • Donado por: {{ $lote->donor->name }}</p>
                <p>{{ $lote->descripcion ?: 'Sin descripción adicional.' }}</p>

                <div class="lote-details">
                    <span><strong>Disponible: {{ number_format((float) $lote->disponible_kg, 2) }} kg</strong></span>
                    <span>Recojo máx: {{ $lote->recojo_maximo->format('d/m/Y H:i') }}</span>
                    <span>Vence: {{ $lote->expiracion->format('d/m/Y H:i') }}</span>
                </div>

                <form action="{{ route('comedor.lotes.solicitar', $lote) }}" method="POST" class="lote-actions" style="align-items:center; gap:0.5rem; margin-top:1rem;">
                    @csrf
                    <input
                        type="number"
                        name="cantidad_kg"
                        step="0.01"
                        min="0.1"
                        max="{{ $lote->disponible_kg }}"
                        value="{{ $lote->disponible_kg }}"
                        required
                        style="width:120px; padding:0.5rem;">
                    <span>kg</span>
                    <button type="submit" class="btn-primary">Solicitar rescate</button>
                </form>
            </div>
        </article>
    @empty
        <div class="lote-empty">
            <h3>No hay lotes disponibles en este momento</h3>
            <p>Vuelve pronto, nuestros donantes publican alimentos todos los días.</p>
        </div>
    @endforelse

    <div class="lote-pagination">{{ $lotes->links() }}</div>
</section>
@endsection
