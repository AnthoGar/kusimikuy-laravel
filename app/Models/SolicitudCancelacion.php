<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudCancelacion extends Model
{
    protected $table = 'solicitud_cancelaciones';

    protected $fillable = [
        'solicitud_id',
        'requested_by',
        'reviewed_by',
        'razon',
        'estado',
        'revisado_at',
    ];
    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudRescate::class, 'solicitud_id');
    }
    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
