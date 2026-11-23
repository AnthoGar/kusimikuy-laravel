@extends('layouts.dashboard')

@section('title', 'Datos del Comedor | KusiMikuy')

@section('content')
<div class="dashboard-heading">
    <div>
        <h1>Perfil del Comedor Popular</h1>
        <p>Mantén actualizada la ubicación y capacidad de atención de tu comedor.</p>
    </div>
</div>

@if (session('info'))
    <div class="alert-success">{{ session('info') }}</div>
@endif

@if ($errors->any())
    <div style="color:#991b1b; background:#fee2e2; padding:1rem; border-radius:8px; margin-bottom:1rem;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="dashboard-section">
    <form action="{{ route('comedor.perfil.store') }}" method="POST" style="display:grid; gap:1rem; max-width:600px;">
        @csrf
        <div>
            <label><strong>Nombre del Comedor Popular *</strong></label><br>
            <input type="text" name="nombre" value="{{ old('nombre', $comedor->nombre ?? '') }}" required style="width:100%; padding:0.6rem;">
        </div>

        <div>
            <label><strong>Distrito *</strong></label><br>
            <input type="text" name="distrito" value="{{ old('distrito', $comedor->distrito ?? '') }}" required style="width:100%; padding:0.6rem;">
        </div>

        <div>
            <label><strong>Dirección exacta de entrega *</strong></label><br>
            <input type="text" name="direccion" value="{{ old('direccion', $comedor->direccion ?? '') }}" required style="width:100%; padding:0.6rem;">
        </div>

        <div>
            <label><strong>Raciones diarias atendidas *</strong></label><br>
            <input type="number" name="raciones_diarias" min="1" value="{{ old('raciones_diarias', $comedor->raciones_diarias ?? 50) }}" required style="width:100%; padding:0.6rem;">
        </div>

        <div>
            <label><strong>Persona de contacto</strong></label><br>
            <input type="text" name="nombre_contacto" value="{{ old('nombre_contacto', $comedor->nombre_contacto ?? auth()->user()->name) }}" style="width:100%; padding:0.6rem;">
        </div>

        <div>
            <label>
                <input type="checkbox" name="tiene_refrigeracion" value="1" {{ old('tiene_refrigeracion', $comedor->tiene_refrigeracion ?? false) ? 'checked' : '' }}>
                El comedor cuenta con refrigeración para alimentos perecibles
            </label>
        </div>

        <div>
            <button type="submit" class="btn-primary">Guardar datos del comedor</button>
        </div>
    </form>
</section>
@endsection
