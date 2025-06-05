<?php

namespace App\Services;

use Carbon\Carbon;

class FormateadorValorRespuestaService
{
  /**
   * Formatea el valor de una respuesta a un string legible.
   * Espera que el objeto $respuesta tenga las propiedades:
   * nombre_tipo_pregunta, valor_texto, valor_numerico, valor_fecha, valor_booleano,
   * opcionSeleccionadaUnica (relación cargada o null),
   * opcionesSeleccionadas (relación cargada o null).
   *
   * @param object $respuesta (Puede ser un modelo RespuestasPregunta con relaciones o un objeto stdClass con los campos)
   * @return string|null
   */
  public function formatear(object $respuesta): ?string
  {
    switch ($respuesta->nombre_tipo_pregunta) {
      case 'Valoración':
        return $respuesta->valor_numerico !== null ? (int)$respuesta->valor_numerico . " estrella(s)" : null;
      case 'Valor numérico':
        return $respuesta->valor_numerico !== null ? (string)$respuesta->valor_numerico : null;
      case 'Texto corto':
      case 'Texto largo':
        return $respuesta->valor_texto;
      case 'Opción múltiple (única respuesta)':
      case 'Lista desplegable (única respuesta)':
        return $respuesta->opcionSeleccionadaUnica?->texto_opcion;
      case 'Selección múltiple (varias respuestas)':
        if (isset($respuesta->opcionesSeleccionadas) && $respuesta->opcionesSeleccionadas->isNotEmpty()) {
          return $respuesta->opcionesSeleccionadas->pluck('texto_opcion')->implode(', ');
        }
        return null;
      case 'Fecha':
        return $respuesta->valor_fecha ? Carbon::parse($respuesta->valor_fecha)->format('d/m/Y') : null;
      case 'Hora':
        return $respuesta->valor_fecha ? Carbon::parse($respuesta->valor_fecha)->format('H:i') : null;
      case 'Booleano (Sí/No)':
        if (is_null($respuesta->valor_booleano)) return null;
        return $respuesta->valor_booleano ? 'Sí' : 'No';
      default:
        return null;
    }
  }
}
