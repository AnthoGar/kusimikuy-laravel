<?php

namespace App\Services;

use App\Models\Traslado;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AsignarColaboradoresTraslado
{
    public function ejecutar(
        int $trasladoId,
        array $colaboradoresIds
    ): Traslado {

        return DB::transaction(function () use (
            $trasladoId,
            $colaboradoresIds
        ) {

            // Bloquear el traslado durante la operación.
            $traslado = Traslado::whereKey($trasladoId)
                ->lockForUpdate()
                ->firstOrFail();

            // Evitar una asignación vacía.
            if (empty($colaboradoresIds)) {
                throw new RuntimeException(
                    'Debe seleccionar al menos un colaborador.'
                );
            }

            // Eliminar identificadores repetidos.
            $colaboradoresIds = array_unique($colaboradoresIds);

            // Buscar los colaboradores seleccionados.
            $colaboradores = User::whereIn('id', $colaboradoresIds)
                ->where('is_active', true)
                ->whereHas('role', function ($query) {
                    $query->where('slug', 'colaborador');
                })
                ->get();

            // Verificar que todos sean colaboradores válidos.
            if ($colaboradores->count() !== count($colaboradoresIds)) {
                throw new RuntimeException(
                    'Uno o más usuarios no son colaboradores activos válidos.'
                );
            }

            // Asignar sin eliminar relaciones existentes.
            $traslado->colaboradores()->syncWithoutDetaching(
                $colaboradoresIds
            );

            return $traslado->load('colaboradores');
        });
    }
}
