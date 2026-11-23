<?php

namespace App\Services;

use App\Models\Entrega;
use App\Models\Traslado;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class RegistrarEntrega
{
    public function ejecutar(
        int $trasladoId,
        int $receivedBy,
        float $cantidadRecibidaKg,
        string $estadoConservacion,
        ?int $calificacionCalidad = null,
        ?string $comentarios = null
    ): Entrega {

        return DB::transaction(function () use (
            $trasladoId,
            $receivedBy,
            $cantidadRecibidaKg,
            $estadoConservacion,
            $calificacionCalidad,
            $comentarios
        ) {

            // Buscar y bloquear el traslado.
            $traslado = Traslado::whereKey($trasladoId)
                ->lockForUpdate()
                ->firstOrFail();

            // Verificar el estado del traslado.
            if ($traslado->estado !== 'en_camino') {
                throw new RuntimeException(
                    'Solo se pueden registrar entregas de traslados en camino.'
                );
            }

            // Obtener y bloquear la solicitud asociada.
            $solicitud = $traslado->solicitud()
                ->lockForUpdate()
                ->firstOrFail();

            if ($solicitud->estado !== 'aceptada') {
                throw new RuntimeException(
                    'La solicitud asociada no está aceptada.'
                );
            }

            // Verificar que no exista una entrega anterior.
            if ($traslado->entrega()->exists()) {
                throw new RuntimeException(
                    'Este traslado ya tiene una entrega registrada.'
                );
            }

            // Verificar la cantidad recibida.
            if ($cantidadRecibidaKg <= 0) {
                throw new InvalidArgumentException(
                    'La cantidad recibida debe ser mayor que cero.'
                );
            }

            if ($cantidadRecibidaKg > (float) $solicitud->cantidad_kg) {
                throw new InvalidArgumentException(
                    'La cantidad recibida no puede superar la cantidad aceptada.'
                );
            }

            // Verificar la calificación.
            if (
                $calificacionCalidad !== null &&
                ($calificacionCalidad < 1 || $calificacionCalidad > 5)
            ) {
                throw new InvalidArgumentException(
                    'La calificación debe estar entre 1 y 5.'
                );
            }

            // Verificar quién registra la recepción.
            $usuario = User::with('role')->findOrFail($receivedBy);

            if (!$usuario->is_active) {
                throw new AuthorizationException(
                    'El usuario no está activo.'
                );
            }

            $esAdministrador =
                $usuario->role?->slug === 'administrador';

            $esResponsableDelComedor =
                $usuario->role?->slug === 'comedor' &&
                $solicitud->comedor->user_id === $usuario->id;

            if (!$esAdministrador && !$esResponsableDelComedor) {
                throw new AuthorizationException(
                    'No tienes autorización para confirmar esta entrega.'
                );
            }

            // Registrar la entrega.
            $entrega = Entrega::create([
                'traslado_id' => $traslado->id,
                'received_by' => $usuario->id,
                'recibido_at' => now(),
                'cantidad_recibida_kg' => $cantidadRecibidaKg,
                'estado_conservacion' => $estadoConservacion,
                'calificacion_calidad' => $calificacionCalidad,
                'comentarios' => $comentarios,
            ]);

            // Marcar el traslado como entregado.
            $traslado->update([
                'estado' => 'entregado',
            ]);

            // Devolver la entrega con sus relaciones.
            return $entrega->load([
                'traslado.solicitud.comedor',
                'receptor',
            ]);
        });
    }
}
