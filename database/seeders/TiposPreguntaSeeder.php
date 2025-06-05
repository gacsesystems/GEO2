<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoPregunta;

class TiposPreguntaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            ['id_tipo_pregunta' => 1, 'nombre' => 'Valoración', 'descripcion' => 'Ej. Estrellas 1-5', 'requiere_opciones' => false, 'permite_min_max_numerico' => true, 'permite_min_max_fecha' => false, 'es_seleccion_multiple' => false],
            ['id_tipo_pregunta' => 2, 'nombre' => 'Valor numérico', 'requiere_opciones' => false, 'permite_min_max_numerico' => true, 'permite_min_max_fecha' => false, 'es_seleccion_multiple' => false],
            ['id_tipo_pregunta' => 3, 'nombre' => 'Texto corto', 'descripcion' => 'Campo de texto de una línea', 'requiere_opciones' => false, 'permite_min_max_numerico' => false, 'permite_min_max_fecha' => false, 'es_seleccion_multiple' => false],
            ['id_tipo_pregunta' => 4, 'nombre' => 'Texto largo', 'descripcion' => 'Campo de texto multilínea', 'requiere_opciones' => false, 'permite_min_max_numerico' => false, 'permite_min_max_fecha' => false, 'es_seleccion_multiple' => false],
            ['id_tipo_pregunta' => 5, 'nombre' => 'Opción múltiple', 'descripcion' => 'Selección de una opción entre varias', 'requiere_opciones' => true, 'permite_min_max_numerico' => false, 'permite_min_max_fecha' => false, 'es_seleccion_multiple' => false],
            ['id_tipo_pregunta' => 6, 'nombre' => 'Selección múltiple', 'descripcion' => 'Selección de varias opciones', 'requiere_opciones' => true, 'permite_min_max_numerico' => false, 'permite_min_max_fecha' => false, 'es_seleccion_multiple' => true],
            ['id_tipo_pregunta' => 7, 'nombre' => 'Lista desplegable', 'descripcion' => 'Selección de una opción en lista desplegable', 'requiere_opciones' => true, 'permite_min_max_numerico' => false, 'permite_min_max_fecha' => false, 'es_seleccion_multiple' => false],
            ['id_tipo_pregunta' => 8, 'nombre' => 'Fecha', 'descripcion' => 'Selección de fecha', 'requiere_opciones' => false, 'permite_min_max_numerico' => false, 'permite_min_max_fecha' => true, 'es_seleccion_multiple' => false],
            ['id_tipo_pregunta' => 9, 'nombre' => 'Hora', 'descripcion' => 'Selección de hora', 'requiere_opciones' => false, 'permite_min_max_numerico' => false, 'permite_min_max_fecha' => true, 'es_seleccion_multiple' => false],
            ['id_tipo_pregunta' => 10, 'nombre' => 'Booleano (Sí/No)', 'requiere_opciones' => false, 'permite_min_max_numerico' => false, 'permite_min_max_fecha' => false, 'es_seleccion_multiple' => false]
        ];
        foreach ($tipos as $tipo) {
            TipoPregunta::updateOrCreate(['id_tipo_pregunta' => $tipo['id_tipo_pregunta']], $tipo);
        }
    }
}
