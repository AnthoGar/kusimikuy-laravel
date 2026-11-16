<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoteAlimento extends Model
{
    protected $table = 'lotes_alimentos';

    protected $fillable = [
        'donor_id',
        'category_id',
        'nombre',
        'descripcion',
        'cantidad_kg',
        'disponible_kg',
        'estado_conservacion',
        'expiracion',
        'recojo_maximo',
        'imagen',
        'estado',
    ];
    protected function casts(): array
    {
        return [
            'cantidad_kg' => 'decimal:2',
            'disponible_kg' => 'decimal:2',
            'expiracion' => 'datetime',
            'recojo_maximo' => 'datetime',
        ];
    }
    public function donante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donor_id');
    }
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaAlimento::class, 'category_id');
    }
    public function solicitudes(): HasMany
    {
        return $this->hasMany(SolicitudRescate::class, 'lot_id');
    }
}
