<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Traslado extends Model
{
    protected $table = 'traslados';
    protected $fillable = [
        'solicitud_id',
        'programado_at',
        'estado',
        'notas',
    ];
    protected function casts(): array
    {
        return [
            'programado_at' => 'datetime',
        ];
    }
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudRescate::class, 'solicitud_id');
    }
    public function colaboradores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'traslado_user')
                    ->withTimestamps();
    }
    public function entrega(): HasOne
    {
        return $this->hasOne(Entrega::class, 'traslado_id');
    }
}
