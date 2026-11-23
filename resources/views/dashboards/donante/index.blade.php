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

<section class="dashboard-section">
    <h2>Resumen de actividad</h2>

    <p>
        Aquí podrás consultar tus lotes publicados,
        solicitudes recibidas y entregas realizadas.
    </p>
</section>

@endsection