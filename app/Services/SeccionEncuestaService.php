<?php

namespace App\Services;

use App\Models\Encuesta;
use App\Models\SeccionEncuesta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB; // Para transacciones si reordenas múltiples

class SeccionEncuestaService
{
  /**
   * Obtener todas las secciones de una encuesta específica, ordenadas.
   */
  public function obtenerPorEncuesta(int $idEncuesta): Collection
  {
    return SeccionEncuesta::where('id_encuesta', $idEncuesta)->orderBy('orden')->get();
  }

  /**
   * Obtener una sección específica por su ID.
   *
   * @param  int  $idSeccion
   * @return SeccionEncuesta|null
   */
  public function obtenerPorId(int $idSeccion): ?SeccionEncuesta
  {
    return SeccionEncuesta::find($idSeccion);
  }

  /**
   * Crear una nueva sección para una encuesta.
   * Asigna automáticamente el siguiente número de orden.
   * @param Encuesta $encuesta
   * @param array $datos Validados ['nombre', 'descripcion'].
   * @return SeccionEncuesta
   */
  public function crear(Encuesta $encuesta, array $datos): SeccionEncuesta
  {
    $ultimoOrden = $encuesta->seccionesEncuesta()->max('orden') ?? 0;
    $datosCompletos = array_merge($datos, [
      'id_encuesta' => $encuesta->id_encuesta,
      'orden' => $ultimoOrden + 1,
      // usuario_registro_id será manejado por el Trait Auditable
    ]);
    return SeccionEncuesta::create($datosCompletos);
  }

  /**
   * Actualizar una sección existente.
   * No maneja el reordenamiento aquí, eso sería una operación separada.
   * @param SeccionEncuesta $seccion
   * @param array $datos Validados ['nombre', 'descripcion'].
   * @return SeccionEncuesta
   */
  public function actualizar(SeccionEncuesta $seccion, array $datos): SeccionEncuesta
  {
    // usuario_modificacion_id será manejado por el Trait Auditable
    $seccion->update($datos);
    return $seccion->fresh();
  }

  /**
   * Eliminar (soft delete) una sección y reordenar las secciones restantes.
   * @param SeccionEncuesta $seccion
   * @return bool
   */
  public function eliminar(SeccionEncuesta $seccion): bool
  {
    DB::transaction(function () use ($seccion) {
      $idEncuesta = $seccion->id_encuesta;
      $ordenEliminado = $seccion->orden;

      // usuario_eliminacion_id será manejado por el Trait Auditable
      $seccion->delete();

      // Reordenar las secciones restantes de esa encuesta
      SeccionEncuesta::where('id_encuesta', $idEncuesta)
        ->where('orden', '>', $ordenEliminado)
        ->decrement('orden');
    });
    return true; // Asumimos que la transacción tuvo éxito si no hay excepción
  }

  /**
   * Reordenar una sección dentro de una encuesta.
   * @param SeccionEncuesta $seccionAMover
   * @param int $nuevoOrden El nuevo índice de orden (basado en 1).
   * @return bool
   */
  public function reordenar(SeccionEncuesta $seccionAMover, int $nuevoOrden): bool
  {
    $idEncuesta = $seccionAMover->id_encuesta;
    $ordenActual = $seccionAMover->orden;

    if ($nuevoOrden == $ordenActual) {
      return true; // No hay cambios
    }

    DB::transaction(function () use ($idEncuesta, $seccionAMover, $ordenActual, $nuevoOrden) {
      if ($nuevoOrden < $ordenActual) {
        // Mover hacia arriba (orden menor)
        SeccionEncuesta::where('id_encuesta', $idEncuesta)
          ->where('orden', '>=', $nuevoOrden)
          ->where('orden', '<', $ordenActual)
          ->increment('orden');
      } else {
        // Mover hacia abajo (orden mayor)
        SeccionEncuesta::where('id_encuesta', $idEncuesta)
          ->where('orden', '<=', $nuevoOrden)
          ->where('orden', '>', $ordenActual)
          ->decrement('orden');
      }
      $seccionAMover->orden = $nuevoOrden;
      $seccionAMover->save();
    });
    return true;
  }
}
