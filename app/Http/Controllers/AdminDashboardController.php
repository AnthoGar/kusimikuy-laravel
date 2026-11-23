<?php

namespace App\Http\Controllers;

use App\Models\LoteAlimento;
use App\Models\Traslado;
use App\Models\User;
use App\Services\AsignarColaboradoresTraslado;
use App\Services\CalcularImpacto;
use Illuminate\Http\Request;
use Throwable;

class AdminDashboardController extends Controller
{
    public function index(CalcularImpacto $calcularImpacto)
    {
        $impacto = $calcularImpacto->ejecutar();
        $totalUsuarios = User::count();
        $lotesActivos = LoteAlimento::where('estado', 'disponible')->count();

        return view('dashboards.admin.index', compact(
            'impacto',
            'totalUsuarios',
            'lotesActivos'
        ));
    }

    public function usuarios()
    {
        $usuarios = User::with('role')->latest()->paginate(15);
        return view('dashboards.admin.usuarios', compact('usuarios'));
    }

    public function toggleUsuario(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', 'Estado del usuario actualizado.');
    }

    public function traslados()
    {
        $traslados = Traslado::with(['solicitud.lote', 'solicitud.comedor', 'colaboradores', 'entrega'])
            ->latest()
            ->paginate(12);

        $colaboradores = User::where('is_active', true)
            ->whereHas('role', fn($q) => $q->where('slug', 'colaborador'))
            ->get();

        return view('dashboards.admin.traslados', compact('traslados', 'colaboradores'));
    }

    public function asignarColaborador(
        Request $request,
        Traslado $traslado,
        AsignarColaboradoresTraslado $asignarService
    ) {
        $datos = $request->validate([
            'colaborador_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        try {
            $asignarService->ejecutar($traslado->id, [(int) $datos['colaborador_id']]);
            return back()->with('success', 'Colaborador asignado al traslado.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
