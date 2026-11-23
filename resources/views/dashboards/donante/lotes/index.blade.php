@extends('layouts.dashboard')

@section('title', 'Mis lotes | KusiMikuy')

@section('content')

<div class="dashboard-heading">
    <div>
        <h1>Mis lotes de alimentos</h1>
        <p>Administra los alimentos que compartes con tu comunidad.</p>
    </div>

    <a href="{{ route('donante.lotes.create') }}" class="btn-primary">
        + Publicar alimento
    </a>
</div>

@if (session('success'))
<div class="alert-success">
    {{ session('success') }}
</div>
@endif

<section class="dashboard-section">

    <div class="dashboard-section-header">
        <h2>Alimentos publicados</h2>
        <span>{{ $lotes->total() }} lotes registrados</span>
    </div>

    @forelse ($lotes as $lote)

    <article class="lote-card">

        @if ($lote->imagen)
        <img
            src="{{ asset('storage/' . $lote->imagen) }}"
            alt="{{ $lote->nombre }}"
            class="lote-image">
        @else
        <div class="lote-image-placeholder">
            Sin imagen
        </div>
        @endif

        <div class="lote-information">

            <div class="lote-title-row">
                <h3>{{ $lote->nombre }}</h3>

                <span class="lote-status lote-status-{{ $lote->estado }}">
                    {{ ucfirst($lote->estado) }}
                </span>
            </div>

            <p class="lote-category">
                {{ $lote->categoria->nombre }}
            </p>

            <p>{{ $lote->descripcion ?: 'Sin descripción.' }}</p>

            <div class="lote-details">
                <span>
                    Publicado: {{ number_format((float) $lote->cantidad_kg, 2) }} kg
                </span>

                <span>
                    Disponible: {{ number_format((float) $lote->disponible_kg, 2) }} kg
                </span>

                <span>
                    Vence: {{ $lote->expiracion->format('d/m/Y H:i') }}
                </span>
            </div>

        </div>

    </article>

    @empty

    <div class="lote-empty">
        <h3>Aún no tienes alimentos publicados</h3>
        <p>Comparte tu primer lote y comienza a generar impacto.</p>

        <a href="{{ route('donante.lotes.create') }}" class="btn-primary">
            Publicar mi primer alimento
        </a>
    </div>

    @endforelse

    <div class="lote-pagination">
        {{ $lotes->links() }}
    </div>

</section>

@endsection