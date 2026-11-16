<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entrega extends Model
{
    protected $table = 'entregas';

    protected $fillable = [
        'traslado_id',
        'received_by',
        'recibido_at',
        'cantidad_recibida_kg',
        'estado_conservacion',
        'calificacion_calidad',
        'comentarios',
    ];
    protected function casts(): array
    {
        return [
            'recibido_at' => 'datetime',
            'cantidad_recibida_kg' => 'decimal:2',
            'calificacion_calidad' => 'integer',
        ];
    }
    public function traslado(): BelongsTo
    {
        return $this->belongsTo(Traslado::class, 'traslado_id');
    }
    public function receptor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
