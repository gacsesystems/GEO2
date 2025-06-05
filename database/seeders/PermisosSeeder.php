<?php

namespace Database\Seeders;

use App\Models\Permisos;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class PermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /// Permisos Generales
        Permisos::firstOrCreate(['nombre_permiso' => 'acceder_panel_admin'], ['descripcion_permiso' => 'Acceder al panel de administración']);
        Permisos::firstOrCreate(['nombre_permiso' => 'acceder_panel_cliente'], ['descripcion_permiso' => 'Acceder al panel de cliente']);

        // Permisos de Clientes (CRUD)
        Permisos::firstOrCreate(['nombre_permiso' => 'ver_clientes'], ['descripcion_permiso' => 'Ver listado de clientes']);
        Permisos::firstOrCreate(['nombre_permiso' => 'crear_clientes'], ['descripcion_permiso' => 'Crear nuevos clientes']);
        // ... más permisos (editar_clientes, eliminar_clientes, ver_detalle_cliente)

        // Permisos de Encuestas (CRUD)
        Permisos::firstOrCreate(['nombre_permiso' => 'ver_encuestas_propias'], ['descripcion_permiso' => 'Ver listado de encuestas propias (rol Cliente)']);
        Permisos::firstOrCreate(['nombre_permiso' => 'ver_todas_las_encuestas'], ['descripcion_permiso' => 'Ver todas las encuestas (rol Administrador)']);
        // ... más permisos

        // Asignar permisos a roles (ejemplo)
        $adminRole = Role::where('nombre_rol', 'Administrador')->first();
        $clienteRole = Role::where('nombre_rol', 'Cliente')->first();

        if ($adminRole) {
            $adminRole->permisos()->syncWithoutDetaching(Permisos::whereIn('nombre_permiso', [
                'acceder_panel_admin',
                'ver_clientes',
                'crear_clientes',
                'ver_todas_las_encuestas'
            ])->pluck('id_permiso'));
        }
        if ($clienteRole) {
            $clienteRole->permisos()->syncWithoutDetaching(Permisos::whereIn('nombre_permiso', [
                'acceder_panel_cliente',
                'ver_encuestas_propias'
            ])->pluck('id_permiso'));
        }
    }
}
