@extends('layouts.public')

@section('title', 'Iniciar sesión | KusiMikuy')

@section('content')
<section class="register-section">
    <div class="register-container">

        <div class="register-heading">
            <span class="register-eyebrow">QUÉ BUENO TENERTE DE VUELTA</span>
            <h1>Bienvenido a KusiMikuy</h1>
            <p>
                Inicia sesión para continuar formando parte
                de una comunidad que transforma alimentos en oportunidades.
            </p>
        </div>

        <div class="register-card login-card">

            @if ($errors->any())
            <div class="register-alert">
                @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form action="{{ route('login.store') }}" method="POST">
                @csrf

                <div class="register-group">
                    <h2>Accede a tu cuenta</h2>
                    <p>Ingresa tus credenciales para continuar.</p>

                    <div class="register-field">
                        <label for="email">Correo electrónico</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            placeholder="ejemplo@correo.com"
                            autocomplete="email"
                            required
                            autofocus>
                    </div>

                    <div class="register-field">
                        <label for="password">Contraseña</label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Ingresa tu contraseña"
                            autocomplete="current-password"
                            required>
                    </div>

                    <label class="login-remember">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1">
                        <span>Recordar mi sesión</span>
                    </label>
                </div>

                <button type="submit" class="register-button">
                    Iniciar sesión
                </button>

                <p class="register-login">
                    ¿Todavía no tienes una cuenta?
                    <a href="{{ route('register') }}">Regístrate aquí</a>
                </p>
            </form>
        </div>
    </div>
</section>
@endsection