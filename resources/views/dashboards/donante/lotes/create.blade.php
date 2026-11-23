@extends('layouts.dashboard')

@section('title', 'Publicar alimento | KusiMikuy')

@section('content')

<div class="dashboard-heading">
    <div>
        <h1>Publicar alimento</h1>
        <p>Comparte alimentos disponibles y ayuda a tu comunidad.</p>
    </div>

    <a href="{{ route('donante.lotes.index') }}" class="btn-secondary">
        Volver a mis lotes
    </a>
</div>

<section class="dashboard-section lote-form-section">

    <form
        action="{{ route('donante.lotes.store') }}"
        method="POST"
        enctype="multipart/form-data">
        @csrf

        <div class="form-section-title">
            <h2>Información del alimento</h2>
            <p>Cuéntanos qué alimento deseas compartir.</p>
        </div>

        <div class="form-grid">

            <div class="form-group">
                <label for="nombre">Nombre del alimento *</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="{{ old('nombre') }}"
                    placeholder="Ej. Papa amarilla"
                    required>
                @error('nombre')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="category_id">Categoría *</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Selecciona una categoría</option>

                    @foreach ($categorias as $categoria)
                    <option
                        value="{{ $categoria->id }}"
                        @selected(old('category_id')==$categoria->id)
                        >
                        {{ $categoria->nombre }}
                    </option>
                    @endforeach
                </select>

                @error('category_id')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group form-group-full">
                <label for="descripcion">Descripción</label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    rows="4"
                    placeholder="Describe las características del alimento...">{{ old('descripcion') }}</textarea>

                @error('descripcion')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="cantidad_kg">Cantidad disponible (kg) *</label>
                <input
                    type="number"
                    id="cantidad_kg"
                    name="cantidad_kg"
                    value="{{ old('cantidad_kg') }}"
                    min="0.01"
                    step="0.01"
                    placeholder="Ej. 25.50"
                    required>

                @error('cantidad_kg')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="estado_conservacion">Estado de conservación *</label>
                <select
                    id="estado_conservacion"
                    name="estado_conservacion"
                    required>
                    <option value="">Selecciona el estado</option>
                    <option value="bueno" @selected(old('estado_conservacion')=='bueno' )>Bueno</option>
                    <option value="regular" @selected(old('estado_conservacion')=='regular' )>Regular</option>
                    <option value="malo" @selected(old('estado_conservacion')=='malo' )>Malo</option>
                </select>

                @error('estado_conservacion')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

        </div>

        <div class="form-section-title">
            <h2>Fechas importantes</h2>
            <p>Indica hasta cuándo se puede recoger el alimento.</p>
        </div>

        <div class="form-grid">

            <div class="form-group">
                <label for="expiracion">Fecha de vencimiento *</label>
                <input
                    type="datetime-local"
                    id="expiracion"
                    name="expiracion"
                    value="{{ old('expiracion') }}"
                    required>

                @error('expiracion')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="recojo_maximo">Fecha límite de recojo *</label>
                <input
                    type="datetime-local"
                    id="recojo_maximo"
                    name="recojo_maximo"
                    value="{{ old('recojo_maximo') }}"
                    required>

                @error('recojo_maximo')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

        </div>

        <div class="form-section-title">
            <h2>Imagen del alimento</h2>
            <p>Una fotografía ayuda a los comedores a identificar el producto.</p>
        </div>

        <div class="form-group">
            <label for="imagen">Seleccionar imagen</label>
            <input
                type="file"
                id="imagen"
                name="imagen"
                accept=".jpg,.jpeg,.png,.webp">

            <small>Formatos JPG, PNG o WEBP. Máximo 2 MB.</small>

            @error('imagen')
            <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-actions">
            <a href="{{ route('donante.lotes.index') }}" class="btn-secondary">
                Cancelar
            </a>

            <button type="submit" class="btn-primary">
                Publicar alimento
            </button>
        </div>

    </form>

</section>

@endsection