<?php

namespace App\Http\Controllers;

use App\Models\ComedorPopular;
use App\Models\Entrega;
use App\Models\LoteAlimento;
use App\Models\SolicitudRescate;
use App\Models\Traslado;
use App\Services\RegistrarEntrega;
use Illuminate\Http\Request;
use Throwable;

class ComedorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $comedor = ComedorPopular::where('user_id', $request->user()->id)->first();

        if (!$comedor) {
            return redirect()->route('comedor.perfil')
                ->with('info', 'Completa primero los datos de tu comedor popular para solicitar alimentos.');
        }

        $solicitudesActivas = SolicitudRescate::where('comedor_id', $comedor->id)
            ->whereIn('estado', ['pendiente', 'aceptada'])
            ->count();

        $entregasRecibidas = Entrega::whereHas('traslado.solicitud', fn($q) => $q->where('comedor_id', $comedor->id))
            ->count();

        $kgRecibidos = Entrega::whereHas('traslado.solicitud', fn($q) => $q->where('comedor_id', $comedor->id))
            ->sum('cantidad_recibida_kg');

        return view('dashboards.comedor.index', compact(
            'comedor',
            'solicitudesActivas',
            'entregasRecibidas',
            'kgRecibidos'
        ));
    }

    public function perfil(Request $request)
    {
        $comedor = ComedorPopular::where('user_id', $request->user()->id)->first();
        return view('dashboards.comedor.perfil', compact('comedor'));
    }

    public function guardarPerfil(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'direccion' => ['required', 'string', 'max:255'],
            'distrito' => ['required', 'string', 'max:100'],
            'raciones_diarias' => ['required', 'integer', 'min:1'],
            'tiene_refrigeracion' => ['nullable', 'boolean'],
            'nombre_contacto' => ['nullable', 'string', 'max:150'],
        ]);

        $datos['user_id'] = $request->user()->id;
        $datos['tiene_refrigeracion'] = $request->boolean('tiene_refrigeracion');

        ComedorPopular::updateOrCreate(
            ['user_id' => $request->user()->id],
            $datos
        );

        return redirect()->route('comedor.dashboard')
            ->with('success', 'Datos del comedor guardados correctamente.');
    }

    public function catalogo(Request $request)
    {
        $comedor = ComedorPopular::where('user_id', $request->user()->id)->first();
        if (!$comedor) {
            return redirect()->route('comedor.perfil')
                ->with('info', 'Registra los datos de tu comedor antes de solicitar lotes.');
        }

        $lotes = LoteAlimento::with(['categoria', 'donor'])
            ->where('estado', 'disponible')
            ->where('disponible_kg', '>', 0)
            ->where('recojo_maximo', '>', now())
            ->latest()
            ->paginate(9);

        return view('dashboards.comedor.catalogo', compact('lotes', 'comedor'));
    }

    public function solicitarRescate(Request $request, LoteAlimento $lote)
    {
        $comedor = ComedorPopular::where('user_id', $request->user()->id)->firstOrFail();

        $datos = $request->validate([
            'cantidad_kg' => ['required', 'numeric', 'gt:0', 'max:' . $lote->disponible_kg],
        ]);

        if ($lote->estado !== 'disponible' || $lote->recojo_maximo->isPast()) {
            return back()->with('error', 'Este lote ya no está disponible para rescate.');
        }

        SolicitudRescate::create([
            'lot_id' => $lote->id,
            'comedor_id' => $comedor->id,
            'cantidad_kg' => $datos['cantidad_kg'],
            'estado' => 'pendiente',
        ]);

        return redirect()->route('comedor.solicitudes.index')
            ->with('success', 'Solicitud de rescate enviada al donante.');
    }

    public function misSolicitudes(Request $request)
    {
        $comedor = ComedorPopular::where('user_id', $request->user()->id)->firstOrFail();

        $solicitudes = SolicitudRescate::with(['lote.donor', 'traslado.colaboradores', 'traslado.entrega'])
            ->where('comedor_id', $comedor->id)
            ->latest()
            ->paginate(10);

        return view('dashboards.comedor.solicitudes', compact('solicitudes'));
    }

    public function confirmarEntrega(
        Request $request,
        Traslado $traslado,
        RegistrarEntrega $registrarEntregaService
    ) {
        $datos = $request->validate([
            'cantidad_recibida_kg' => ['required', 'numeric', 'gt:0'],
            'estado_conservacion' => ['required', 'in:bueno,regular,malo'],
            'calificacion_calidad' => ['nullable', 'integer', 'min:1', 'max:5'],
            'comentarios' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $registrarEntregaService->ejecutar(
                $traslado->id,
                $request->user()->id,
                (float) $datos['cantidad_recibida_kg'],
                $datos['estado_conservacion'],
                $datos['calificacion_calidad'] ? (int) $datos['calificacion_calidad'] : null,
                $datos['comentarios'] ?? null
            );

            return back()->with('success', '¡Entrega confirmada y registrada en el impacto comunitario!');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
