<?php

namespace App\Services;

use App\Models\SeccionEncuesta;
use App\Models\Pregunta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PreguntaService
{
  /**
   * Obtener todas las preguntas de una sección, con relaciones cargadas y ordenadas.
   *
   * @param  int  $idSeccion
   * @return \Illuminate\Support\Collection<App\Models\Pregunta>
   */
  public function obtenerPorSeccion(int $idSeccion): Collection
  {
    return Pregunta::with([
      'tipoPregunta:id_tipo_pregunta,nombre_tipo',
      'opcionesPregunta' => fn($q) => $q->orderBy('orden'),
      'preguntaPadre:id_pregunta,texto_pregunta,orden',
      'opcionCondicionPadre:id_opcion_pregunta,texto_opcion',
    ])->where('id_seccion', $idSeccion)->orderBy('orden')->get();
  }

  /**
   * Obtener una pregunta por su ID, cargando relaciones necesarias.
   *
   * @param  int  $idPregunta
   * @return App\Models\Pregunta|null
   */
  public function obtenerPorId(int $idPregunta): ?Pregunta
  {
    return Pregunta::with([
      'tipoPregunta:id_tipo_pregunta,nombre_tipo',
      'opcionesPregunta' => fn($q) => $q->orderBy('orden'),
      'preguntaPadre',
      'opcionCondicionPadre',
    ])
      ->find($idPregunta);
  }

  /**
   * Crear una nueva pregunta en la sección dada, asignando orden e insertando opciones si corresponde.
   *
   * @param  SeccionEncuesta         $seccion
   * @param  array<string, mixed>    $datos   Datos validados (pueden incluir 'opciones')
   * @return App\Models\Pregunta
   */
  public function crear(SeccionEncuesta $seccion, array $datos): Pregunta
  {
    return DB::transaction(function () use ($seccion, $datos) {
      $ultimoOrden = $seccion->preguntas()->max('orden') ?? 0;

      $datosPregunta = [
        'id_seccion'             => $seccion->id_seccion,
        'texto_pregunta'          => $datos['texto_pregunta'],
        'id_tipo_pregunta'        => $datos['id_tipo_pregunta'],
        'orden'                  => $ultimoOrden + 1,
        'es_obligatoria'          => $datos['es_obligatoria'] ?? false,
        'numero_minimo'           => $datos['numero_minimo'] ?? null,
        'numero_maximo'           => $datos['numero_maximo'] ?? null,
        'fecha_minima'            => $datos['fecha_minima'] ?? null,
        'fecha_maxima'            => $datos['fecha_maxima'] ?? null,
        'hora_minima'             => $datos['hora_minima'] ?? null,
        'hora_maxima'             => $datos['hora_maxima'] ?? null,
        'texto_ayuda'             => $datos['texto_ayuda'] ?? null,
        'id_pregunta_padre'        => $datos['id_pregunta_padre'] ?? null,
        'valor_condicion_padre'     => $datos['valor_condicion_padre'] ?? null,
        'id_opcion_condicion_padre'  => $datos['id_opcion_condicion_padre'] ?? null,
        // 'usuario_registro_id' se asigna mediante trait Auditable
      ];

      $pregunta = Pregunta::create($datosPregunta);

      if (! empty($datos['opciones']) && is_array($datos['opciones'])) {
        $ordenOpcion = 1;
        foreach ($datos['opciones'] as $opcionData) {
          $pregunta->opcionesPregunta()->create([
            'texto_opcion' => $opcionData['texto_opcion'],
            'valor_opcion' => $opcionData['valor_opcion'] ?? null,
            'orden'        => $ordenOpcion++,
          ]);
        }
      }

      return $pregunta->load(['tipoPregunta', 'opcionesPregunta']);
    });
  }

  /**
   * Actualizar una pregunta existente y recargar relaciones.
   *
   * @param  Pregunta              $pregunta
   * @param  array<string, mixed>  $datos    Datos validados (no incluye opciones)
   * @return Pregunta
   */
  public function actualizar(Pregunta $pregunta, array $datos): Pregunta
  {
    return DB::transaction(function () use ($pregunta, $datos) {
      // Actualizar solo los campos presentes en $datos
      $pregunta->fill($datos);
      // Si el tipo de pregunta cambia y ya no requiere opciones, eliminar opciones existentes
      // O si el tipo nuevo requiere opciones y antes no, etc. (lógica más compleja)
      // Por ahora, la actualización de opciones se manejará por separado a través de OpcionPreguntaService.
      $pregunta->save();
      return $pregunta->fresh(['tipoPregunta', 'opcionesPregunta']);
    });
  }

  /**
   * Eliminar (soft delete) una pregunta y reordenar las restantes en la misma sección.
   *
   * @param  Pregunta  $pregunta
   * @return bool
   */
  public function eliminar(Pregunta $pregunta): bool
  {
    DB::transaction(function () use ($pregunta) {
      $idSeccion     = $pregunta->id_seccion;
      $ordenEliminado = $pregunta->orden;

      // Antes de eliminar la pregunta, podrías necesitar eliminar dependencias
      // como respuestas o condiciones en otras preguntas que apunten a esta.
      // Por simplicidad, asumimos que las FK con ON DELETE SET NULL o CASCADE manejan esto.

      // Eliminar preguntas hijas (preguntas que tienen esta como padre)
      // Pregunta::where('id_pregunta_padre', $pregunta->id_pregunta)->update(['id_pregunta_padre' => null, 'valor_condicion_padre' => null, 'id_opcion_condicion_padre' => null]);

      $pregunta->delete(); // Soft delete

      // Decrementar orden de las preguntas restantes
      Pregunta::where('id_seccion', $idSeccion)->where('orden', '>', $ordenEliminado)->decrement('orden');
    });

    return true;
  }

  /**
   * Reordenar una pregunta dentro de su sección y ajustar orden de las demás.
   *
   * @param  Pregunta  $preguntaAMover
   * @param  int       $nuevoOrden
   * @return bool
   */
  public function reordenar(Pregunta $preguntaAMover, int $nuevoOrden): bool
  {
    $idSeccion   = $preguntaAMover->id_seccion;
    $ordenActual = $preguntaAMover->orden;

    if ($nuevoOrden === $ordenActual) return true;

    DB::transaction(function () use ($idSeccion, $preguntaAMover, $ordenActual, $nuevoOrden) {
      if ($nuevoOrden < $ordenActual) {
        Pregunta::where('id_seccion', $idSeccion)
          ->whereBetween('orden', [$nuevoOrden, $ordenActual - 1])
          ->increment('orden');
      } else {
        Pregunta::where('id_seccion', $idSeccion)
          ->whereBetween('orden', [$ordenActual + 1, $nuevoOrden])
          ->decrement('orden');
      }

      $preguntaAMover->orden = $nuevoOrden; // Ajustar la pregunta movida

      // Si tenía padre y el nuevo orden queda antes del padre, quitar relación
      if ($preguntaAMover->id_pregunta_padre) {
        $preguntaPadre = Pregunta::find($preguntaAMover->id_pregunta_padre);
        if (
          $preguntaPadre
          && $preguntaPadre->id_seccion === $idSeccion
          && $nuevoOrden <= $preguntaPadre->orden
        ) {
          $preguntaAMover->id_pregunta_padre           = null;
          $preguntaAMover->valor_condicion_padre       = null;
          $preguntaAMover->id_opcion_condicion_padre   = null;
        }
      }

      $preguntaAMover->save();
    });

    return true;
  }
}
