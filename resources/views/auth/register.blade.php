@extends('layouts.public')

@section('title', 'Crear cuenta | KusiMikuy')

@section('content')
<section class="register-section">
    <div class="register-container">

        <div class="register-heading">
            <span class="register-eyebrow">FORMA PARTE DEL CAMBIO</span>
            <h1>Únete a KusiMikuy</h1>
            <p>
                Conectamos alimentos, creamos impacto.
                Regístrate y forma parte de una comunidad que transforma vidas.
            </p>
        </div>

        <div class="register-card">

            @if ($errors->any())
            <div class="register-alert">
                <strong>Revisa los siguientes campos:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('register.store') }}" method="POST">
                @csrf

                <div class="register-group">
                    <h2>01. Datos personales</h2>
                    <p>Cuéntanos un poco sobre ti.</p>

                    <div class="register-field">
                        <label for="name">Nombre completo</label>
                        <input type="text" name="name" id="name"
                            value="{{ old('name') }}"
                            placeholder="Ingresa tu nombre completo" required>
                    </div>

                    <div class="register-field">
                        <label for="email">Correo electrónico</label>
                        <input type="email" name="email" id="email"
                            value="{{ old('email') }}"
                            placeholder="ejemplo@correo.com" required>
                    </div>

                    <div class="register-field">
                        <label for="phone">Teléfono <span>(opcional)</span></label>
                        <input type="text" name="phone" id="phone"
                            value="{{ old('phone') }}"
                            placeholder="Tu número de contacto">
                    </div>

                    <div class="register-field">
                        <label for="account_type">¿Cómo deseas participar?</label>
                        <select name="account_type" id="account_type" required>
                            <option value="">Selecciona tu tipo de cuenta</option>
                            <option value="donante" @selected(old('account_type')==='donante' )>
                                Donante
                            </option>
                            <option value="comedor" @selected(old('account_type')==='comedor' )>
                                Comedor popular
                            </option>
                        </select>
                    </div>
                </div>

                <div id="comedor-fields" class="register-group" hidden>
                    <h2>02. Información del comedor</h2>
                    <p>Necesitamos algunos datos para identificar tu comedor.</p>

                    <div class="register-field">
                        <label for="nombre">Nombre del comedor</label>
                        <input type="text" name="nombre" id="nombre"
                            value="{{ old('nombre') }}"
                            placeholder="Nombre de tu comedor">
                    </div>

                    <div class="register-field">
                        <label for="direccion">Dirección</label>
                        <input type="text" name="direccion" id="direccion"
                            value="{{ old('direccion') }}"
                            placeholder="Dirección del comedor">
                    </div>

                    <div class="register-field">
                        <label for="distrito">Distrito</label>
                        <input type="text" name="distrito" id="distrito"
                            value="{{ old('distrito') }}"
                            placeholder="Distrito donde se ubica">
                    </div>

                    <div class="register-field">
                        <label for="raciones_diarias">Raciones diarias</label>
                        <input type="number" name="raciones_diarias"
                            id="raciones_diarias" min="1"
                            value="{{ old('raciones_diarias') }}"
                            placeholder="Ej. 80">
                    </div>

                    <div class="register-field">
                        <label for="tiene_refrigeracion">¿Cuenta con refrigeración?</label>
                        <select name="tiene_refrigeracion" id="tiene_refrigeracion">
                            <option value="0" @selected(old('tiene_refrigeracion', '0' )==='0' )>
                                No
                            </option>
                            <option value="1" @selected(old('tiene_refrigeracion')==='1' )>
                                Sí
                            </option>
                        </select>
                    </div>

                    <div class="register-field">
                        <label for="nombre_contacto">
                            Nombre del contacto <span>(opcional)</span>
                        </label>
                        <input type="text" name="nombre_contacto"
                            id="nombre_contacto"
                            value="{{ old('nombre_contacto') }}"
                            placeholder="Persona responsable">
                    </div>
                </div>

                <div class="register-group">
                    <h2>02. Seguridad de la cuenta</h2>
                    <p>Protege tu acceso a KusiMikuy.</p>

                    <div class="register-field">
                        <label for="password">Contraseña</label>
                        <input type="password" name="password" id="password"
                            placeholder="Mínimo 8 caracteres" required>
                    </div>

                    <div class="register-field">
                        <label for="password_confirmation">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation"
                            id="password_confirmation"
                            placeholder="Vuelve a ingresar tu contraseña" required>
                    </div>
                </div>

                <button type="submit" class="register-button">
                    Crear mi cuenta
                </button>

                <p class="register-login">
                    ¿Ya tienes una cuenta?
                    <a href="#">Inicia sesión</a>
                </p>
            </form>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    const accountType = document.getElementById('account_type');
    const comedorFields = document.getElementById('comedor-fields');

    function toggleComedorFields() {
        const isComedor = accountType.value === 'comedor';

        comedorFields.hidden = !isComedor;

        comedorFields.querySelectorAll('input, select').forEach(field => {
            field.required = isComedor && ['nombre', 'direccion', 'distrito', 'raciones_diarias']
                .includes(field.name);
        });
    }

    accountType.addEventListener('change', toggleComedorFields);
    toggleComedorFields();
</script>
@endpush