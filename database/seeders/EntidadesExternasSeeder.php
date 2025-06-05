<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EntidadesExternasSeeder extends Seeder
{
  public function run(): void
  {
    // Ejemplo: insertar las tablas del ERP hospitalario
    DB::table('entidades_externas')->insert([
      ['clave' => 'HPREG05', 'descripcion' => 'Pacientes'],
      ['clave' => 'HPAREAS',       'descripcion' => 'Áreas'],
      ['clave' => 'HPMEDICOS',     'descripcion' => 'Médicos'],
      ['clave' => 'HPEXPEDIENTE',  'descripcion' => 'Expedientes'],
      // ... cualquier otra tabla mapeable ...
    ]);
  }
}
