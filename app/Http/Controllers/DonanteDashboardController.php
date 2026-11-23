<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DonanteDashboardController extends Controller
{
    public function index(Request $request)
    {
        $usuario = $request->user();

        // 1. Total de lotes publicados por el donante.
        $totalLotes = DB::table('lotes_alimentos')
            ->where('donor_id', $usuario->id)
            ->count();

        // 2. Solicitudes pendientes recibidas.
        $solicitudesPendientes = DB::table('solicitudes_rescate')
            ->join(
                'lotes_alimentos',
                'solicitudes_rescate.lot_id',
                '=',
                'lotes_alimentos.id'
            )
            ->where('lotes_alimentos.donor_id', $usuario->id)
            ->where('solicitudes_rescate.estado', 'pendiente')
            ->count();

        // 3. Entregas completadas.
        $entregasCompletadas = DB::table('entregas')
            ->join(
                'traslados',
                'entregas.traslado_id',
                '=',
                'traslados.id'
            )
            ->join(
                'solicitudes_rescate',
                'traslados.solicitud_id',
                '=',
                'solicitudes_rescate.id'
            )
            ->join(
                'lotes_alimentos',
                'solicitudes_rescate.lot_id',
                '=',
                'lotes_alimentos.id'
            )
            ->where('lotes_alimentos.donor_id', $usuario->id)
            ->where('traslados.estado', 'entregado')
            ->count();

        // 4. Kilogramos realmente recibidos.
        $kgRescatados = DB::table('entregas')
            ->join(
                'traslados',
                'entregas.traslado_id',
                '=',
                'traslados.id'
            )
            ->join(
                'solicitudes_rescate',
                'traslados.solicitud_id',
                '=',
                'solicitudes_rescate.id'
            )
            ->join(
                'lotes_alimentos',
                'solicitudes_rescate.lot_id',
                '=',
                'lotes_alimentos.id'
            )
            ->where('lotes_alimentos.donor_id', $usuario->id)
            ->where('traslados.estado', 'entregado')
            ->sum('entregas.cantidad_recibida_kg');

        return view('dashboards.donante.index', [
            'totalLotes' => $totalLotes,
            'solicitudesPendientes' => $solicitudesPendientes,
            'entregasCompletadas' => $entregasCompletadas,
            'kgRescatados' => round((float) $kgRescatados, 2),
        ]);
    }
}
