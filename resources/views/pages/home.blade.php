@extends('layouts.public')

@section('title', 'KusiMikuy | Conectamos alimentos, creamos impacto')

@section('content')

{{-- HERO --}}
<section class="hero">
    <div class="container hero-grid">

        <div class="hero-copy">
            <span class="eyebrow">TECNOLOGÍA CON PROPÓSITO · CUSCO</span>

            <h1>
                La tecnología que conecta.
                <span>Los alimentos que transforman.</span>
            </h1>

            <p>
                Rescatamos alimentos, fortalecemos comunidades
                y cuidamos nuestro planeta mediante una red
                que conecta a quienes pueden ayudar con quienes
                más lo necesitan.
            </p>

            <div class="hero-actions">
                <a href="#comunidad" class="btn btn-primary">
                    Sé parte del cambio
                </a>

                <a href="#proposito" class="btn btn-secondary">
                    Conoce nuestro propósito
                </a>
            </div>

            <div class="hero-note">
                <span class="note-dot"></span>
                Una iniciativa con impacto social y ambiental.
            </div>
        </div>

        <div class="hero-visual">
            <div class="hero-image">
                <img
                    src="{{ asset('img/logo.jpg') }}"
                    alt="Identidad visual de KusiMikuy">
            </div>

            <div class="floating-note">
                <span class="floating-icon">✳</span>
                <div>
                    <strong>Cada aporte cuenta</strong>
                    <small>Cada conexión importa</small>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- PROPÓSITO --}}
<section class="section purpose" id="proposito">
    <div class="container purpose-grid">

        <div>
            <span class="eyebrow">NUESTRO PROPÓSITO</span>
            <h2>
                Cada alimento tiene valor.
                <span>Cada conexión genera oportunidades.</span>
            </h2>
        </div>

        <div class="purpose-copy">
            <p>
                Muchos alimentos que todavía pueden aprovecharse
                no llegan a quienes los necesitan. Al mismo tiempo,
                comedores populares requieren recursos para
                atender a sus comunidades.
            </p>

            <p>
                KusiMikuy busca conectar ambos lados mediante
                una plataforma que facilita la publicación,
                solicitud, coordinación y recepción de alimentos.
            </p>
        </div>

    </div>
</section>

{{-- CÓMO FUNCIONA --}}
<section class="section process" id="como-funciona">
    <div class="container">

        <div class="section-heading">
            <span class="eyebrow">UNA RED, UN PROPÓSITO</span>
            <h2>Del excedente a una nueva oportunidad.</h2>
            <p>
                Un proceso colaborativo para que los alimentos
                aprovechables lleguen a su destino.
            </p>
        </div>

        <div class="process-grid">

            <article class="process-card">
                <span class="process-number">01</span>
                <span class="process-symbol">↗</span>
                <h3>Comparte</h3>
                <p>
                    Los donantes publican alimentos disponibles
                    para su aprovechamiento.
                </p>
            </article>

            <article class="process-card">
                <span class="process-number">02</span>
                <span class="process-symbol">⌕</span>
                <h3>Conecta</h3>
                <p>
                    Los comedores encuentran alimentos y
                    solicitan las cantidades que necesitan.
                </p>
            </article>

            <article class="process-card">
                <span class="process-number">03</span>
                <span class="process-symbol">⇄</span>
                <h3>Coordina</h3>
                <p>
                    Los colaboradores ayudan a organizar
                    el traslado de los alimentos.
                </p>
            </article>

            <article class="process-card">
                <span class="process-number">04</span>
                <span class="process-symbol">✳</span>
                <h3>Transforma</h3>
                <p>
                    La recepción se confirma y el impacto
                    de cada entrega queda registrado.
                </p>
            </article>

        </div>
    </div>
</section>

{{-- COMUNIDAD --}}
<section class="section community" id="comunidad">
    <div class="container">

        <div class="section-heading">
            <span class="eyebrow">SOMOS UNA COMUNIDAD</span>
            <h2>El cambio necesita de todos.</h2>
            <p>
                Cada participante cumple una función importante
                dentro de esta red de colaboración.
            </p>
        </div>

        <div class="community-grid">

            <article class="community-card">
                <span class="community-tag">01 / DONACIÓN</span>
                <h3>Donantes</h3>
                <p>
                    Comparte alimentos aprovechables y dales
                    una nueva oportunidad.
                </p>
            </article>

            <article class="community-card">
                <span class="community-tag">02 / RECEPCIÓN</span>
                <h3>Comedores populares</h3>
                <p>
                    Accede a alimentos disponibles para
                    fortalecer la atención de tu comunidad.
                </p>
            </article>

            <article class="community-card">
                <span class="community-tag">03 / COLABORACIÓN</span>
                <h3>Colaboradores</h3>
                <p>
                    Participa en la coordinación y el traslado
                    de los alimentos.
                </p>
            </article>

            <article class="community-card">
                <span class="community-tag">04 / GESTIÓN</span>
                <h3>Administradores</h3>
                <p>
                    Supervisa las operaciones y contribuye
                    a la transparencia de la plataforma.
                </p>
            </article>

        </div>
    </div>
</section>

{{-- IMPACTO --}}
<section class="section impact" id="impacto">
    <div class="container">

        <div class="impact-content">
            <div>
                <span class="eyebrow">IMPACTO QUE SE PUEDE SEGUIR</span>

                <h2>Compartir también alimenta el futuro.</h2>

                <p>
                    Cada entrega confirmada representa una acción
                    concreta. Nuestro impacto se mide con datos
                    reales y transparentes.
                </p>
            </div>

            <div class="impact-visual">
                <span class="impact-symbol">✳</span>
                <strong>Conectamos alimentos, creamos impacto.</strong>
                <small>Datos basados en entregas confirmadas.</small>
            </div>
        </div>

        <div class="impact-stats">

            <article class="impact-stat">
                <span class="stat-value">
                    {{ number_format($impacto['kg_rescatados'], 2) }}
                </span>
                <span class="stat-label">Kilogramos rescatados</span>
            </article>

            <article class="impact-stat">
                <span class="stat-value">
                    {{ number_format($impacto['entregas_completadas']) }}
                </span>
                <span class="stat-label">Entregas completadas</span>
            </article>

            <article class="impact-stat">
                <span class="stat-value">
                    {{ number_format($impacto['comedores_beneficiados']) }}
                </span>
                <span class="stat-label">Comedores beneficiados</span>
            </article>

            <article class="impact-stat">
                <span class="stat-value">
                    {{ number_format($impacto['solicitudes_atendidas']) }}
                </span>
                <span class="stat-label">Solicitudes atendidas</span>
            </article>

        </div>

    </div>
</section>

{{-- CTA FINAL --}}
<section class="final-cta">
    <div class="container">
        <span class="eyebrow">TU PARTICIPACIÓN IMPORTA</span>
        <h2>El cambio comienza cuando compartimos.</h2>
        <p>
            Sé parte de una red que transforma alimentos
            en oportunidades para las comunidades.
        </p>
        <a href="#comunidad" class="btn btn-light">
            Conoce cómo participar
        </a>
    </div>
</section>

@endsection
