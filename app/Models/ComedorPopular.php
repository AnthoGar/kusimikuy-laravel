<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComedorPopular extends Model
{
    protected $table = 'comedores_populares';

    protected $fillable = [
        'user_id',
        'nombre',
        'direccion',
        'distrito',
        'latitud',
        'longitud',
        'raciones_diarias',
        'tiene_refrigeracion',
        'nombre_contacto',
    ];
    protected function casts(): array
    {
        return [
            'latitud' => 'decimal:7',
            'longitud' => 'decimal:7',
            'raciones_diarias' => 'integer',
            'tiene_refrigeracion' => 'boolean',
        ];
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function solicitudes(): HasMany
    {
        return $this->hasMany(SolicitudRescate::class, 'comedor_id');
    }
}
