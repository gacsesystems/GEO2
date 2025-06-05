<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['nombre_rol' => 'Administrador'], ['descripcion_rol' => 'Administrador del sistema con todos los accesos.']);
        Role::firstOrCreate(['nombre_rol' => 'Cliente'], ['descripcion_rol' => 'Usuario cliente que gestiona sus propias encuestas.']);
        Role::firstOrCreate(['nombre_rol' => 'Encuestado'], ['descripcion_rol' => 'Usuario que solo puede responder encuestas (puede ser anónimo o con login).']);
    }
}
