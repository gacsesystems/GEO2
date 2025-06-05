<?php

namespace App\Services;

use App\Models\Pregunta;
use App\Models\OpcionPregunta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OpcionPreguntaService
{
  /**
   * Obtener todas las opciones de una pregunta, ordenadas por “orden”.
   */
  public function obtenerPorPregunta(int $idPregunta): Collection
  {
    return OpcionPregunta::where('id_pregunta', $idPregunta)->orderBy('orden')->get();
  }

  /**
   * Obtener una opción específica por su ID.
   */
  public function obtenerPorId(int $idOpcion): ?OpcionPregunta
  {
    return OpcionPregunta::find($idOpcion);
  }

  /**
   * Crear una nueva opción para la pregunta indicada,
   * asignando automáticamente el próximo orden disponible.
   *
   * @param  \App\Models\Pregunta  $pregunta
   * @param  array<string,mixed>   $datos      // ['texto_opcion' => string, 'valor_opcion' => string|null]
   * @return \App\Models\OpcionPregunta
   *
   * @throws \InvalidArgumentException  Si el tipo de pregunta no permite opciones.
   */
  public function crear(Pregunta $pregunta, array $datos): OpcionPregunta
  {
    // 1) Verificar que el tipo de pregunta permite opciones
    //    (tipoPregunta viene cargado o se carga “lazy” en Eloquent)
    if (! $pregunta->tipoPregunta?->requiere_opciones) {
      throw new InvalidArgumentException("El tipo de pregunta '{$pregunta->tipoPregunta?->nombre}' no permite opciones.");
    }

    $ultimoOrden = $pregunta->opcionesPregunta()->max('orden') ?? 0; // 2) Calcular orden: máximo existente + 1

    // 3) Preparar array para mass assignment
    $datosCompletos = array_merge($datos, [
      'id_pregunta'   => $pregunta->id_pregunta,
      'orden'         => $ultimoOrden + 1,
    ]);

    return OpcionPregunta::create($datosCompletos); // 4) Crear la opción
  }

  /**
   * Crear múltiples opciones para una pregunta (ej. al crear la pregunta).
   * @param Pregunta $pregunta
   * @param array $opcionesData Array de arrays, cada sub-array con ['texto_opcion', 'valor_opcion' (opc)]
   * @return Collection
   */
  public function crearMultiples(Pregunta $pregunta, array $opcionesData): Collection
  {
    if (!$pregunta->tipoPregunta?->requiere_opciones) {
      throw new \InvalidArgumentException("Este tipo de pregunta ('{$pregunta->tipoPregunta?->nombre}') no permite opciones.");
    }
    $opcionesCreadas = new Collection();
    DB::transaction(function () use ($pregunta, $opcionesData, &$opcionesCreadas) {
      // Podrías borrar las existentes si esta función implica un reemplazo total
      // $pregunta->opcionesPregunta()->delete(); // Ojo: esto es hard delete si no usan SoftDeletes
      $orden = 1;
      foreach ($opcionesData as $datoOpcion) {
        $opcionesCreadas->push(
          $pregunta->opcionesPregunta()->create([
            'texto_opcion' => $datoOpcion['texto_opcion'],
            'valor_opcion' => $datoOpcion['valor_opcion'] ?? null,
            'orden' => $orden++,
          ])
        );
      }
    });
    return $opcionesCreadas;
  }

  /**
   * Actualizar una opción existente.
   */
  public function actualizar(OpcionPregunta $opcion, array $datos): OpcionPregunta
  {
    $opcion->update($datos);
    return $opcion->fresh();
  }

  /**
   * Eliminar (soft delete) una opción y reordenar las restantes.
   */
  public function eliminar(OpcionPregunta $opcion): bool
  {
    DB::transaction(function () use ($opcion) {
      $idPregunta = $opcion->id_pregunta;
      $ordenEliminado = $opcion->orden;

      // Antes de eliminar, verificar si esta opción es usada en id_opcion_condicion_padre
      // en alguna otra pregunta y ponerla a null.
      Pregunta::where('id_opcion_condicion_padre', $opcion->id_opcion_pregunta)
        ->update(['id_opcion_condicion_padre' => null]);

      $opcion->delete(); // Soft delete

      OpcionPregunta::where('id_pregunta', $idPregunta)
        ->where('orden', '>', $ordenEliminado)
        ->decrement('orden');
    });
    return true;
  }

  /**
   * Reordenar una opción dentro de una pregunta.
   */
  public function reordenar(OpcionPregunta $opcion, int $nuevoOrden): bool
  {
    $idPregunta = $opcion->id_pregunta;
    $ordenActual = $opcion->orden;

    if ($nuevoOrden == $ordenActual) return true;

    DB::transaction(function () use ($idPregunta, $opcion, $ordenActual, $nuevoOrden) {
      if ($nuevoOrden < $ordenActual) {
        // Mover hacia arriba: incrementar las que estén >= nuevoOrden y < ordenActual
        OpcionPregunta::where('id_pregunta', $idPregunta)
          ->where('orden', '>=', $nuevoOrden)
          ->where('orden', '<', $ordenActual)
          ->increment('orden');
      } else {
        // Mover hacia abajo: decrementar las que estén <= nuevoOrden y > ordenActual
        OpcionPregunta::where('id_pregunta', $idPregunta)
          ->where('orden', '<=', $nuevoOrden)
          ->where('orden', '>', $ordenActual)
          ->decrement('orden');
      }
      $opcion->orden = $nuevoOrden;
      $opcion->save();
    });
    return true;
  }
}
