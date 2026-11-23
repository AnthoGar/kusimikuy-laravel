<?php

namespace App\Http\Controllers;

use App\Models\Traslado;
use App\Services\AsignarColaboradoresTraslado;
use App\Services\IniciarTraslado;
use Illuminate\Http\Request;
use Throwable;

class ColaboradorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $disponibles = Traslado::where('estado', 'pendiente')->count();

        $misActivos = Traslado::whereHas('colaboradores', fn($q) => $q->where('users.id', $userId))
            ->whereIn('estado', ['pendiente', 'en_camino'])
            ->count();

        $completados = Traslado::whereHas('colaboradores', fn($q) => $q->where('users.id', $userId))
            ->where('estado', 'entregado')
            ->count();

        return view('dashboards.colaborador.index', compact(
            'disponibles',
            'misActivos',
            'completados'
        ));
    }

    public function traslados(Request $request)
    {
        $traslados = Traslado::with(['solicitud.lote.donor', 'solicitud.comedor', 'colaboradores'])
            ->latest()
            ->paginate(10);

        return view('dashboards.colaborador.traslados', compact('traslados'));
    }

    public function asignarme(
        Request $request,
        Traslado $traslado,
        AsignarColaboradoresTraslado $asignarService
    ) {
        try {
            $asignarService->ejecutar($traslado->id, [$request->user()->id]);
            return back()->with('success', 'Te has asignado a este traslado correctamente.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function iniciar(
        Request $request,
        Traslado $traslado,
        IniciarTraslado $iniciarService
    ) {
        abort_unless(
            $traslado->colaboradores()->where('users.id', $request->user()->id)->exists(),
            403
        );

        try {
            $iniciarService->ejecutar($traslado->id);
            return back()->with('success', 'Traslado iniciado. Estado actualizado a: En camino.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
