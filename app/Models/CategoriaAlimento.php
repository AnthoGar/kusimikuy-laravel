<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaAlimento extends Model
{
    protected $table = 'categorias_alimentos';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function lotes(): HasMany
    {
        return $this->hasMany(LoteAlimento::class, 'category_id');
    }
}
