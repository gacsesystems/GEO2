<?php

namespace App\Services;

use App\Models\Encuesta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class EncuestaService
{
  // /**
  //  * Obtener encuestas para un cliente específico.
  //  */
  public function obtenerPorCliente(int $idCliente): Collection
  {
    return Encuesta::where('idCliente', $idCliente)
      ->orderBy('Nombre')
      ->get();
  }

  /**
   * Traer todas las encuestas paginadas (15 por página), 
   * filtrando según rol/cliente:
   * - Si es ADMIN → devuelve todas las encuestas.
   * - Si es CLIENTE → devuelve solo las encuestas cuyo id_Cliente coincida.
   *
   * @return LengthAwarePaginator
   */
  public function obtenerTodas(): LengthAwarePaginator
  {
    $user = Auth::user();
    $query = Encuesta::with('cliente:id_cliente,alias')->orderBy('nombre');
    if (!$user->esRol('Administrador')) $query->where('id_cliente', $user->id_cliente);

    return $query->paginate(15);
  }

  /**
   * Obtener una encuesta por su ID.
   */
  public function obtenerPorId(int $idEncuesta): ?Encuesta
  {
    return Encuesta::findOrFail($idEncuesta);
  }

  /**
   * Obtener el detalle completo de una encuesta para el diseñador o para responder.
   * Carga secciones, preguntas (con su tipo y opciones si aplica).
   */
  public function obtenerDetalleCompleto(int $idEncuesta): ?Encuesta
  {
    return Encuesta::select('id_encuesta', 'nombre', 'descripcion', 'id_cliente')->with([
      'cliente:id_cliente,alias,ruta_logo', // Info del cliente
      'seccionesEncuesta' => fn($q) => $q->orderBy('orden'), // Secciones ordenadas
      'seccionesEncuesta.preguntas' => fn($q) => $q->orderBy('orden'), // Preguntas por sección, ordenadas
      'seccionesEncuesta.preguntas.tipoPregunta', // Tipo de cada pregunta
      'seccionesEncuesta.preguntas.opcionesPregunta' => fn($q) => $q->orderBy('orden'), // Opciones por pregunta, ordenadas
      'seccionesEncuesta.preguntas.preguntaPadre:id_pregunta,texto_pregunta', // Info básica de la pregunta padre
      'seccionesEncuesta.preguntas.opcionCondicionPadre:id_opcion_pregunta,texto_opcion', // Info básica de la opción padre
    ])->findOrFail($idEncuesta);
  }


  /**
   * Crear una nueva encuesta.
   * @param array $datos Validados ['nombre', 'descripcion'].
   * @param int $idCliente El ID del cliente al que pertenece la encuesta.
   * @return Encuesta
   */
  public function crear(array $datos, int $idCliente): Encuesta
  {
    $datosCompletos = array_merge($datos, [
      'id_cliente' => $idCliente,
      // usuario_registro_id será manejado por el Trait Auditable
    ]);
    return Encuesta::create($datosCompletos);
  }

  /**
   * Actualizar una encuesta existente.
   * @param Encuesta $encuesta
   * @param array $datos Validados ['nombre', 'descripcion'].
   * @return Encuesta
   */
  public function actualizar(Encuesta $encuesta, array $datos): Encuesta
  {
    // usuario_modificacion_id será manejado por el Trait Auditable
    $encuesta->update($datos);
    return $encuesta->fresh(); // Retorna la instancia actualizada
  }

  /**
   * Eliminar (soft delete) una encuesta.
   * @param Encuesta $encuesta
   * @return bool
   */
  public function eliminar(Encuesta $encuesta): bool
  {
    // usuario_eliminacion_id será manejado por el Trait Auditable
    return $encuesta->delete();
  }
}
