<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CamposExternosSeeder extends Seeder
{
  public function run(): void
  {
    // Ejemplo a mano (debiera ser genérico si tienes cientos de columnas)
    // Supongamos que la entidad G_PACIENTES tiene columnas: Nombre, Edad, Domicilio, Correo, EstadoCivil
    DB::table('campos_externos')->insert([
      ['entidad_externa_id' => 1, 'nombre' => 'DESC_PAC',      'tipo' => 'string',  'descripcion' => 'Nombre completo del paciente'],
      ['entidad_externa_id' => 1, 'nombre' => 'EDAD_PAC',        'tipo' => 'integer', 'descripcion' => 'Edad en años'],
      ['entidad_externa_id' => 1, 'nombre' => 'DIR_PAC',   'tipo' => 'string',  'descripcion' => 'Dirección del paciente'],
      ['entidad_externa_id' => 1, 'nombre' => 'CORREO_PAC',      'tipo' => 'string',  'descripcion' => 'Email del paciente'],
      ['entidad_externa_id' => 1, 'nombre' => 'APE_PAT_PAC', 'tipo' => 'string',  'descripcion' => 'Apellido paterno del paciente'],
      ['entidad_externa_id' => 1, 'nombre' => 'APE_MAT_PAC', 'tipo' => 'string',  'descripcion' => 'Apellido materno del paciente'],
      ['entidad_externa_id' => 1, 'nombre' => 'SEXO_PAC', 'tipo' => 'string',  'descripcion' => 'Sexo del paciente'],
      ['entidad_externa_id' => 1, 'nombre' => 'FECHA_ING_PAC', 'tipo' => 'date',  'descripcion' => 'Fecha de ingreso del paciente'],

      ['entidad_externa_id' => 2, 'nombre' => 'DESC_AREA',  'tipo' => 'string',  'descripcion' => 'Nombre del área hospitalaria'],
      ['entidad_externa_id' => 3, 'nombre' => 'DESC_MEDICO', 'tipo' => 'string',  'descripcion' => 'Nombre del doctor'],

      // …etc…
    ]);
  }
}
