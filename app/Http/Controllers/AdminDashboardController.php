<?php

namespace App\Http\Controllers;

use App\Models\LoteAlimento;
use App\Models\Traslado;
use App\Models\User;
use App\Services\AsignarColaboradoresTraslado;
use App\Services\CalcularImpacto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $resultado = DB::transaction(function () use ($user) {
            $usuario = User::whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Evitar que el administrador desactive su propia cuenta.
            if ($usuario->is_active && $usuario->id === auth()->id()) {
                return 'self';
            }

            // Comprobar si se intenta desactivar a un administrador.
            if (
                $usuario->is_active &&
                $usuario->role?->slug === 'administrador'
            ) {
                $administradoresActivos = User::where('is_active', true)
                    ->whereHas('role', function ($query) {
                        $query->where('slug', 'administrador');
                    })
                    ->lockForUpdate()
                    ->get(['id']);

                // No permitir desactivar al último administrador.
                if ($administradoresActivos->count() <= 1) {
                    return 'last_admin';
                }
            }

            $usuario->is_active = !$usuario->is_active;
            $usuario->save();

            return 'updated';
        });

        if ($resultado === 'self') {
            return back()->with(
                'error',
                'No puedes desactivar tu propia cuenta.'
            );
        }

        if ($resultado === 'last_admin') {
            return back()->with(
                'error',
                'No puedes desactivar al último administrador activo del sistema.'
            );
        }

        return back()->with('success', 'Estado del usuario actualizado.');
    }

    public function traslados()
    {
        $traslados = Traslado::with([
            'solicitud.lote',
            'solicitud.comedor',
            'colaboradores',
            'entrega'
        ])
            ->latest()
            ->paginate(12);

        $colaboradores = User::where('is_active', true)
            ->whereHas('role', fn ($query) => $query->where('slug', 'colaborador'))
            ->get();

        return view('dashboards.admin.traslados', compact(
            'traslados',
            'colaboradores'
        ));
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
            $asignarService->ejecutar(
                $traslado->id,
                [(int) $datos['colaborador_id']]
            );

            return back()->with('success', 'Colaborador asignado al traslado.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
