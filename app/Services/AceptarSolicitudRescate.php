<?php

namespace App\Services;

use App\Models\LoteAlimento;
use App\Models\SolicitudRescate;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;
use RuntimeException;

class AceptarSolicitudRescate
{
    public function ejecutar(
        int $solicitudId,
        int $donanteId
    ): SolicitudRescate {
        return DB::transaction(function () use ($solicitudId, $donanteId) {

            $solicitud = SolicitudRescate::whereKey($solicitudId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($solicitud->estado !== 'pendiente') {
                throw new RuntimeException(
                    'La solicitud ya fue respondida o no está pendiente.'
                );
            }

            $lote = LoteAlimento::where('id', $solicitud->lot_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Verificar que el usuario sea el propietario del lote.
            if ((int) $lote->donor_id !== $donanteId) {
                throw new AuthorizationException(
                    'No tienes permiso para aceptar esta solicitud.'
                );
            }

            if ($lote->estado !== 'disponible') {
                throw new RuntimeException(
                    'El lote no se encuentra disponible.'
                );
            }

            $ahora = now();

            if ($lote->expiracion->lessThanOrEqualTo($ahora)) {
                throw new RuntimeException(
                    'El alimento ya venció.'
                );
            }

            if ($lote->recojo_maximo->lessThanOrEqualTo($ahora)) {
                throw new RuntimeException(
                    'El plazo máximo de recojo ha vencido.'
                );
            }

            if (
                (float) $solicitud->cantidad_kg <= 0 ||
                (float) $solicitud->cantidad_kg >
                (float) $lote->disponible_kg
            ) {
                throw new RuntimeException(
                    'No existe suficiente cantidad disponible.'
                );
            }

            $lote->disponible_kg = round(
                (float) $lote->disponible_kg -
                    (float) $solicitud->cantidad_kg,
                2
            );

            if ((float) $lote->disponible_kg === 0.0) {
                $lote->estado = 'agotado';
            }

            $lote->save();

            $solicitud->estado = 'aceptada';
            $solicitud->respondido_at = $ahora;
            $solicitud->save();

            return $solicitud;
        });
    }
}
