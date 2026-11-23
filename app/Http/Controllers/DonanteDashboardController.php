<?php

namespace App\Http\Controllers;

use App\Models\Entrega;
use App\Models\LoteAlimento;
use App\Models\SolicitudRescate;
use App\Services\AceptarSolicitudRescate;
use App\Services\CrearTraslado;
use Illuminate\Http\Request;
use Throwable;

class DonanteDashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $totalLotes = LoteAlimento::where('donor_id', $userId)->count();

        $solicitudesPendientes = SolicitudRescate::whereHas('lote', fn($q) => $q->where('donor_id', $userId))
            ->where('estado', 'pendiente')
            ->count();

        $entregasCompletadas = Entrega::whereHas('traslado.solicitud.lote', fn($q) => $q->where('donor_id', $userId))
            ->count();

        $kgRescatados = Entrega::whereHas('traslado.solicitud.lote', fn($q) => $q->where('donor_id', $userId))
            ->sum('cantidad_recibida_kg');

        return view('dashboards.donante.index', compact(
            'totalLotes',
            'solicitudesPendientes',
            'entregasCompletadas',
            'kgRescatados'
        ));
    }

    public function solicitudes(Request $request)
    {
        $solicitudes = SolicitudRescate::with(['lote', 'comedor', 'traslado.colaboradores'])
            ->whereHas('lote', fn($q) => $q->where('donor_id', $request->user()->id))
            ->latest()
            ->paginate(10);

        return view('dashboards.donante.solicitudes', compact('solicitudes'));
    }

    public function aceptarSolicitud(
        Request $request,
        SolicitudRescate $solicitud,
        AceptarSolicitudRescate $aceptarService,
        CrearTraslado $crearTrasladoService
    ) {
        try {
            $aceptarService->ejecutar($solicitud->id, $request->user()->id);
            // Crear automáticamente el traslado pendiente para que un colaborador lo tome
            $crearTrasladoService->ejecutar($solicitud->id);

            return back()->with('success', 'Solicitud aceptada y traslado programado para recojo.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function rechazarSolicitud(Request $request, SolicitudRescate $solicitud)
    {
        abort_unless((int) $solicitud->lote->donor_id === $request->user()->id, 403);

        if ($solicitud->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden rechazar solicitudes pendientes.');
        }

        $solicitud->update([
            'estado' => 'rechazada',
            'respondido_at' => now(),
        ]);

        return back()->with('success', 'Solicitud rechazada.');
    }
}
