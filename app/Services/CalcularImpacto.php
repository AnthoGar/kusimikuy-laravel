<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CalcularImpacto
{
    public function ejecutar(): array
    {
        // Consultar únicamente entregas de traslados finalizados.
        $resultado = DB::table('entregas')
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
            ->where('traslados.estado', 'entregado')
            ->selectRaw('
                COALESCE(
                    SUM(entregas.cantidad_recibida_kg),
                    0
                ) AS kg_rescatados,

                COUNT(DISTINCT entregas.id)
                    AS entregas_completadas,

                COUNT(DISTINCT solicitudes_rescate.comedor_id)
                    AS comedores_beneficiados,

                COUNT(DISTINCT solicitudes_rescate.id)
                    AS solicitudes_atendidas
            ')
            ->first();

        // Devolver los indicadores en un arreglo.
        return [
            'kg_rescatados' => round(
                (float) $resultado->kg_rescatados,
                2
            ),

            'entregas_completadas' =>
                (int) $resultado->entregas_completadas,

            'comedores_beneficiados' =>
                (int) $resultado->comedores_beneficiados,

            'solicitudes_atendidas' =>
                (int) $resultado->solicitudes_atendidas,
        ];
    }
}
