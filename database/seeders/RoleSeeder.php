<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Donante', 'slug' => 'donante'],
            ['name' => 'Comedor', 'slug' => 'comedor'],
            ['name' => 'Colaborador', 'slug' => 'colaborador'],
            ['name' => 'Administrador', 'slug' => 'administrador'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                ['name' => $role['name']]
            );
        }
    }
}
