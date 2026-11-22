<?php

namespace App\Services;

use App\Models\SolicitudRescate;
use App\Models\Traslado;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CrearTraslado
{
    public function ejecutar(
        int $solicitudId,
        ?string $programadoAt = null,
        ?string $notas = null
    ): Traslado {

        return DB::transaction(function () use (
            $solicitudId,
            $programadoAt,
            $notas
        ) {

            // Bloquear la solicitud para evitar operaciones simultáneas.
            $solicitud = SolicitudRescate::whereKey($solicitudId)
                ->lockForUpdate()
                ->firstOrFail();

            // Solo se permiten solicitudes aceptadas.
            if ($solicitud->estado !== 'aceptada') {
                throw new RuntimeException(
                    'Solo se pueden crear traslados para solicitudes aceptadas.'
                );
            }

            // Comprobar que no exista otro traslado.
            if ($solicitud->traslado()->exists()) {
                throw new RuntimeException(
                    'Esta solicitud ya tiene un traslado registrado.'
                );
            }

            // Crear el traslado.
            return Traslado::create([
                'solicitud_id' => $solicitud->id,
                'programado_at' => $programadoAt,
                'estado' => 'pendiente',
                'notas' => $notas,
            ]);
        });
    }
}
