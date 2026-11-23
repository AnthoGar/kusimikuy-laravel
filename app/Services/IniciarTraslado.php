<?php

namespace App\Services;

use App\Models\Traslado;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class IniciarTraslado
{
    public function ejecutar(int $trasladoId): Traslado
    {
        return DB::transaction(function () use ($trasladoId) {

            // Buscar y bloquear el traslado.
            $traslado = Traslado::whereKey($trasladoId)
                ->lockForUpdate()
                ->firstOrFail();

            // Verificar que esté pendiente.
            if ($traslado->estado !== 'pendiente') {
                throw new RuntimeException(
                    'Solo se pueden iniciar traslados pendientes.'
                );
            }

            // Verificar que tenga colaboradores asignados.
            if (!$traslado->colaboradores()->exists()) {
                throw new RuntimeException(
                    'Debe asignarse al menos un colaborador antes de iniciar el traslado.'
                );
            }

            // Actualizar el estado.
            $traslado->update([
                'estado' => 'en_camino',
            ]);

            // Devolver el traslado con sus relaciones.
            return $traslado->load([
                'solicitud',
                'colaboradores',
            ]);
        });
    }
}
