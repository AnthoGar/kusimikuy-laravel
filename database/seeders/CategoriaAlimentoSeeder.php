<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoriaAlimento;

class CategoriaAlimentoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Frutas',
                'descripcion' => 'Frutas frescas y aptas para consumo.',
            ],
            [
                'nombre' => 'Verduras',
                'descripcion' => 'Verduras y hortalizas frescas.',
            ],
            [
                'nombre' => 'Tuberculos',
                'descripcion' => 'Papa, camote, yuca y otros tuberculos.',
            ],
            [
                'nombre' => 'Cereales y granos',
                'descripcion' => 'Arroz, quinua, avena, maiz y otros granos.',
            ],
            [
                'nombre' => 'Legumbres',
                'descripcion' => 'Lentejas, frejoles, habas y otras legumbres.',
            ],
            [
                'nombre' => 'Productos lacteos',
                'descripcion' => 'Leche, queso, yogurt y derivados.',
            ],
            [
                'nombre' => 'Carnes y huevos',
                'descripcion' => 'Carnes, pollo, pescado y huevos.',
            ],
            [
                'nombre' => 'Otros alimentos',
                'descripcion' => 'Otros productos alimenticios aprovechables.',
            ],
        ];

        foreach ($categorias as $categoria) {
            CategoriaAlimento::updateOrCreate(
                ['nombre' => $categoria['nombre']],
                ['descripcion' => $categoria['descripcion']]
            );
        }
    }
}
