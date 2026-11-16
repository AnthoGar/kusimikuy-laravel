<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolicitudRescate extends Model
{
    protected $table = 'solicitudes_rescate';

    protected $fillable = [
        'lot_id',
        'comedor_id',
        'cantidad_kg',
        'estado',
        'respondido_at',
    ];
    protected function casts(): array
    {
        return [
            'cantidad_kg' => 'decimal:2',
            'respondido_at' => 'datetime',
        ];
    }
    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteAlimento::class, 'lot_id');
    }

    public function comedor(): BelongsTo
    {
        return $this->belongsTo(ComedorPopular::class, 'comedor_id');
    }
    public function traslado(): HasOne
    {
        return $this->hasOne(Traslado::class, 'solicitud_id');
    }
    public function cancelaciones(): HasMany
    {
        return $this->hasMany(SolicitudCancelacion::class, 'solicitud_id');
    }
}
