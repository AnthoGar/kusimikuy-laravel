@extends('layouts.dashboard')

@section('content')
<div class="dashboard-section">
    <div class="section-header">
        <div>
            <h1>Editar lote</h1>
            <p>Actualiza la información de tu alimento publicado.</p>
        </div>

        <a href="{{ route('donante.lotes.index') }}" class="btn-secondary">
            Volver a mis lotes
        </a>
    </div>

    <div class="form-card">
        <form
            action="{{ route('donante.lotes.update', $lote) }}"
            method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="nombre">Nombre del alimento</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="{{ old('nombre', $lote->nombre) }}"
                    required>
                @error('nombre')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="category_id">Categoría</label>
                <select id="category_id" name="category_id" required>
                    @foreach ($categorias as $categoria)
                    <option
                        value="{{ $categoria->id }}"
                        @selected(old('category_id', $lote->category_id) == $categoria->id)
                        >
                        {{ $categoria->nombre }}
                    </option>
                    @endforeach
                </select>
                @error('category_id')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    rows="4">{{ old('descripcion', $lote->descripcion) }}</textarea>
                @error('descripcion')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="cantidad_kg">Cantidad total (kg)</label>
                <input
                    type="number"
                    id="cantidad_kg"
                    name="cantidad_kg"
                    step="0.01"
                    min="0.01"
                    value="{{ old('cantidad_kg', $lote->cantidad_kg) }}"
                    required>
                @error('cantidad_kg')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="estado_conservacion">Estado de conservación</label>
                <select
                    id="estado_conservacion"
                    name="estado_conservacion"
                    required>
                    <option value="bueno" @selected(old('estado_conservacion', $lote->estado_conservacion) == 'bueno')>
                        Bueno
                    </option>
                    <option value="regular" @selected(old('estado_conservacion', $lote->estado_conservacion) == 'regular')>
                        Regular
                    </option>
                    <option value="malo" @selected(old('estado_conservacion', $lote->estado_conservacion) == 'malo')>
                        Malo
                    </option>
                </select>
                @error('estado_conservacion')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="expiracion">Fecha de vencimiento</label>
                <input
                    type="datetime-local"
                    id="expiracion"
                    name="expiracion"
                    value="{{ old('expiracion', $lote->expiracion->format('Y-m-d\TH:i')) }}"
                    required>
                @error('expiracion')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="recojo_maximo">Fecha límite de recojo</label>
                <input
                    type="datetime-local"
                    id="recojo_maximo"
                    name="recojo_maximo"
                    value="{{ old('recojo_maximo', $lote->recojo_maximo->format('Y-m-d\TH:i')) }}"
                    required>
                @error('recojo_maximo')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>Imagen actual</label>

                @if ($lote->imagen)
                <img
                    src="{{ asset('storage/' . $lote->imagen) }}"
                    alt="{{ $lote->nombre }}"
                    class="lote-image">
                @else
                <p>No hay una imagen registrada.</p>
                @endif
            </div>

            <div class="form-group">
                <label for="imagen">Reemplazar imagen (opcional)</label>
                <input
                    type="file"
                    id="imagen"
                    name="imagen"
                    accept=".jpg,.jpeg,.png,.webp">
                <small>JPG, PNG o WEBP. Máximo 2 MB.</small>
                @error('imagen')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn-primary">
                Guardar cambios
            </button>
        </form>
    </div>
</div>
@endsection