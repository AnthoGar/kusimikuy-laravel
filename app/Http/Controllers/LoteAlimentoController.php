<?php

namespace App\Http\Controllers;

use App\Models\CategoriaAlimento;
use App\Models\LoteAlimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class LoteAlimentoController extends Controller
{
    /**
     * Mostrar los lotes publicados por el donante.
     */
    public function index(Request $request)
    {
        $lotes = LoteAlimento::with('categoria')
            ->where('donor_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('dashboards.donante.lotes.index', compact('lotes'));
    }

    /**
     * Mostrar el formulario de publicación.
     */
    public function create()
    {
        $categorias = CategoriaAlimento::orderBy('nombre')->get();

        return view(
            'dashboards.donante.lotes.create',
            compact('categorias')
        );
    }

    /**
     * Registrar un nuevo lote.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'category_id' => [
                'required',
                'integer',
                'exists:categorias_alimentos,id',
            ],
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],
            'descripcion' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'cantidad_kg' => [
                'required',
                'numeric',
                'gt:0',
                'max:99999999.99',
            ],
            'estado_conservacion' => [
                'required',
                'in:bueno,regular,malo',
            ],
            'expiracion' => [
                'required',
                'date',
                'after:now',
            ],
            'recojo_maximo' => [
                'required',
                'date',
                'after:now',
                'before:expiracion',
            ],
            'imagen' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        if ($request->hasFile('imagen')) {
            $datos['imagen'] = $request->file('imagen')
                ->store('lotes', 'public');
        }

        $datos['donor_id'] = $request->user()->id;
        $datos['disponible_kg'] = $datos['cantidad_kg'];
        $datos['estado'] = 'disponible';

        LoteAlimento::create($datos);

        return redirect()
            ->route('donante.lotes.index')
            ->with('success', '¡El lote se publicó correctamente!');
    }

    public function edit(Request $request, LoteAlimento $lote)
    {
        // Verificar que el lote pertenezca al donante autenticado.
        abort_unless(
            $lote->donor_id === $request->user()->id,
            403,
            'No tienes permiso para editar este lote.'
        );

        // Obtener las categorías para el formulario.
        $categorias = CategoriaAlimento::orderBy('nombre')->get();

        return view(
            'dashboards.donante.lotes.edit',
            compact('lote', 'categorias')
        );
    }

    public function update(Request $request, LoteAlimento $lote)
    {
        // Validar los datos recibidos.
        $datos = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categorias_alimentos,id'],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'cantidad_kg' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'estado_conservacion' => ['required', 'in:bueno,regular,malo'],
            'expiracion' => ['required', 'date', 'after:now'],
            'recojo_maximo' => ['required', 'date', 'after:now', 'before:expiracion'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Comprobar que el lote pertenezca al usuario autenticado.
        abort_unless(
            $lote->donor_id === $request->user()->id,
            403,
            'No tienes permiso para editar este lote.'
        );

        // Obtener el nuevo total y calcular la disponibilidad.
        $nuevaImagen = null;
        $imagenAnterior = $lote->imagen;

        DB::transaction(function () use (
            $request,
            $lote,
            $datos,
            &$nuevaImagen
        ) {
            // Bloquear el registro durante la actualización.
            $loteActual = LoteAlimento::whereKey($lote->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $loteActual->donor_id === $request->user()->id,
                403,
                'No tienes permiso para editar este lote.'
            );

            $cantidadAnterior = (float) $loteActual->cantidad_kg;
            $disponibleActual = (float) $loteActual->disponible_kg;
            $nuevaCantidad = (float) $datos['cantidad_kg'];

            $nuevaDisponibilidad = round(
                $disponibleActual + ($nuevaCantidad - $cantidadAnterior),
                2
            );

            // No permitir reducir el inventario por debajo
            // de la cantidad ya comprometida.
            if ($nuevaDisponibilidad < 0) {
                throw ValidationException::withMessages([
                    'cantidad_kg' => 'La nueva cantidad no puede ser menor que los alimentos ya comprometidos en solicitudes.',
                ]);
            }

            // Guardar una nueva imagen si el usuario seleccionó una.
            if ($request->hasFile('imagen')) {
                $nuevaImagen = $request->file('imagen')
                    ->store('lotes', 'public');

                $datos['imagen'] = $nuevaImagen;
            }

            $datos['disponible_kg'] = $nuevaDisponibilidad;

            $loteActual->update($datos);
        });

        // Eliminar la imagen anterior solamente después de actualizar correctamente.
        if ($nuevaImagen && $imagenAnterior) {
            Storage::disk('public')->delete($imagenAnterior);
        }

        return redirect()
            ->route('donante.lotes.index')
            ->with('success', '¡El lote se actualizó correctamente!');
    }
}
