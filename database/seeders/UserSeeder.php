<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Cliente;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Obtener los Roles de la base de datos
        $rolAdministrador = Role::where('nombre_rol', 'Administrador')->first();
        $rolCliente = Role::where('nombre_rol', 'Cliente')->first();

        // --- Usuario Administrador (sin cambios respecto a la versión anterior) ---
        if ($rolAdministrador) {
            User::updateOrCreate(
                ['email' => 'sagt@gacse.com'],
                [
                    'nombre_completo' => 'Saúl González',
                    'password' => Hash::make('gacse123'),
                    'id_rol' => $rolAdministrador->id_rol,
                    'id_cliente' => null,
                    'activo' => true,
                    'email_verified_at' => now()
                ]
            );
        } else {
            $this->command->warn('Rol "Administrador" no encontrado. Saltando creación de usuario administrador.');
        }

        // --- Creación de Cliente de Ejemplo (CORREGIDO) ---
        // Obtener el ID del usuario administrador para auditoría, si existe
        $adminUser = User::where('email', 'admin@example.com')->first();

        $clienteEjemplo = Cliente::updateOrCreate(
            ['alias' => 'HOSPITAL_DEMO'], // Criterio para buscar/actualizar
            [
                // Asegúrate de que estos nombres de columna coincidan con tu $fillable en el modelo Cliente
                'razon_social' => 'Hospital de Demostración XYZ',
                // 'alias' => 'HOSPITAL_DEMO', // Ya está en el criterio de búsqueda, pero no hace daño repetirlo aquí
                'activo' => true,
                'limite_encuestas' => 10, // Ejemplo
                'vigencia' => null, // O una fecha si es necesario: now()->addYear(),
                'ruta_logo' => null, // O una ruta de ejemplo si la tienes
                // Campos de auditoría (si el trait Auditable está funcionando, se llenarán automáticamente
                // si hay un usuario autenticado. Para seeders, a veces es mejor ser explícito si no hay Auth).
                // Si el trait no se activa porque no hay Auth::user() durante el seeder, puedes definirlos:
                'usuario_registro_id' => $adminUser?->id, // Usar el ID del admin creado anteriormente si existe
            ]
        );

        // --- Usuario Cliente de Ejemplo (sin cambios respecto a la versión anterior) ---
        if ($rolCliente && $clienteEjemplo) {
            User::updateOrCreate(
                ['email' => 'saulizaso@hotmail.com'],
                [
                    'nombre_completo' => 'Usuario Cliente Demo',
                    'password' => Hash::make('password'),
                    'id_rol' => $rolCliente->id_rol,
                    'id_cliente' => $clienteEjemplo->id_cliente, // Usar la PK correcta
                    'activo' => true,
                    'email_verified_at' => now()
                ]
            );
        } else {
            if (!$rolCliente) {
                $this->command->warn('Rol "Cliente" no encontrado. Saltando creación de usuario cliente.');
            }
            if (!$clienteEjemplo && $rolCliente) {
                $this->command->warn('Cliente de ejemplo "HOSPITAL_DEMO" no encontrado/creado. Saltando creación de usuario cliente.');
            }
        }
    }
}
