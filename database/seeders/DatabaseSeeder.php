<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RoleSeeder::class,
            PermisosSeeder::class, // Asegúrate que RoleSeeder se ejecute antes si PermissionSeeder asigna a roles
            TiposPreguntaSeeder::class,
            ParametrosSeeder::class,
            UserSeeder::class, // UserSeeder al final para que los roles ya existan
            // ClienteSeeder::class, // Si creas uno
            // EncuestaSeeder::class, // etc.
        ]);
    }
}
