<?php

namespace App\Services;

use App\Models\Encuesta;
use App\Models\EncuestaRespondida;
use App\Models\Pregunta;
use App\Models\RespuestaPregunta;
use App\Models\OpcionPregunta;
use App\Models\TipoPregunta;
use Carbon\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// use App\Models\Paciente;
use App\Models\PreguntaMapeoExterno;

use InvalidArgumentException;

class RespuestasService
{
  /**
   * Guarda un bloque de respuestas para una encuesta.
   *
   * @param  int    $idEncuesta
   * @param  array  $datosPeticion  (incluye 'correo_respuesta', 'fecha_inicio_respuesta', 'fecha_fin_respuesta', 'respuestas')
   * @param  array  $metadatos     (ip, user-agent, etc.)
   * @return EncuestasRespondida
   *
   * @throws \InvalidArgumentException
   */
  public function guardarRespuestas(int $idEncuesta, array $datosPeticion, array $metadatos = []): EncuestaRespondida
  {
    $encuesta = Encuesta::find($idEncuesta);
    if (!$encuesta || ! $encuesta->cliente?->activo) {
      throw new InvalidArgumentException('Encuesta no válida o no disponible.');
    }

    if (! $encuesta->cliente?->activo) {
      throw new InvalidArgumentException('El cliente de esta encuesta no está activo.');
    }

    // 1) Si es cuestionario y no está activo, abortar:
    if ($encuesta->es_cuestionario && ! $encuesta->esta_activa) {
      throw new InvalidArgumentException('El cuestionario no está en su periodo de vigencia.');
    }

    $pacienteId = $datosPeticion['paciente_id'] ?? null; // 2) Obtener paciente_id (si viene) o nulo

    return DB::transaction(function () use ($encuesta, $datosPeticion, $metadatos, $pacienteId) {
      // 1) Crear encabezado en encuestas_respondidas
      $encResp = $encuesta->encuestasRespondidas()->create([
        'correo_respuesta'         => $datosPeticion['correo_respuesta'] ?? null,
        'id_usuario_respuesta'     => Auth::check() ? Auth::id() : null,
        'id_encuesta_respondida' => null, // se autoincrementa
        'id_paciente'            => $pacienteId,      // Si existe
        'fecha_inicio_respuesta'   => isset($datosPeticion['fecha_inicio_respuesta'])
          ? Carbon::parse($datosPeticion['fecha_inicio_respuesta'])
          : now(),
        'fecha_fin_respuesta'      => isset($datosPeticion['fecha_fin_respuesta'])
          ? Carbon::parse($datosPeticion['fecha_fin_respuesta'])
          : now(),
        'metadatos'                => $metadatos,
      ]);

      // 2) Iterar sobre cada respuesta enviada
      foreach ($datosPeticion['respuestas'] as $respData) {
        $pregunta = Pregunta::with('tipoPregunta', 'seccionEncuesta')->find($respData['id_pregunta']);

        if (! $pregunta || $pregunta->seccionEncuesta->id_encuesta !== $encuesta->id_encuesta) {
          // Si la pregunta no pertenece a esta encuesta, saltamos
          continue;
        }

        $valorEnviado        = $respData['valor_respuesta'] ?? null;
        $idsOpcionesMultiple = $respData['ids_opciones_seleccionadas'] ?? [];

        // Si la pregunta es obligatoria y no se envió nada, la omitimos.
        if ($pregunta->es_obligatoria && is_null($valorEnviado) && empty($idsOpcionesMultiple)) {
          continue;
        }

        // Base de datos de campos a insertar
        $camposDetalle = ['id_pregunta' => $pregunta->id_pregunta];

        $nombreTipo = $pregunta->tipoPregunta->nombre;

        // Mapear según tipo de pregunta
        switch ($nombreTipo) {
          case TipoPregunta::NOMBRE_VALORACION:
          case TipoPregunta::NOMBRE_VALOR_NUMERICO:
            if (! is_numeric($valorEnviado)) {
              // Si se envía no numérico para tipo numérico, lo ignoramos
              $camposDetalle['valor_numerico'] = null;
            } else {
              $numero = (float) $valorEnviado;
              // **Validación de rango mínimo/máximo**
              if (! is_null($pregunta->numero_minimo) && $numero < $pregunta->numero_minimo) {
                throw new InvalidArgumentException(
                  "La respuesta para la pregunta '{$pregunta->texto_pregunta}' debe ser igual o mayor a {$pregunta->numero_minimo}."
                );
              }
              if (! is_null($pregunta->numero_maximo) && $numero > $pregunta->numero_maximo) {
                throw new InvalidArgumentException(
                  "La respuesta para la pregunta '{$pregunta->texto_pregunta}' debe ser igual o menor a {$pregunta->numero_maximo}."
                );
              }
              $camposDetalle['valor_numerico'] = $numero;
            }
            break;

          case TipoPregunta::NOMBRE_TEXTO_CORTO:
          case TipoPregunta::NOMBRE_TEXTO_LARGO:
            $camposDetalle['valor_texto'] = is_string($valorEnviado) ? substr($valorEnviado, 0, 4000) : null;
            break;

          case TipoPregunta::NOMBRE_OPCION_UNICA:
          case TipoPregunta::NOMBRE_LISTA_DESPLEGABLE:
            $idOpcion = is_numeric($valorEnviado) ? (int) $valorEnviado : null;
            // Verificar que la opción sea válida para esta pregunta
            if ($idOpcion && ! OpcionPregunta::where('id_opcion_pregunta', $idOpcion)
              ->where('id_pregunta', $pregunta->id_pregunta)
              ->exists()) {
              $idOpcion = null;
            }
            $camposDetalle['id_opcion_seleccionada_unica'] = $idOpcion;
            break;

          case TipoPregunta::NOMBRE_FECHA:
            try {
              $f = $valorEnviado ? Carbon::parse($valorEnviado)->toDateString() : null;
            } catch (\Exception) {
              $f = null;
            }
            // Validar rango de fecha si pregunta->fecha_minima / fecha_maxima
            if ($f && (($pregunta->fecha_minima && $f < $pregunta->fecha_minima) || ($pregunta->fecha_maxima && $f > $pregunta->fecha_maxima))) {
              throw new InvalidArgumentException("La fecha para la pregunta '{$pregunta->texto_pregunta}' debe estar entre {$pregunta->fecha_minima} y {$pregunta->fecha_maxima}.");
            }

            $camposDetalle['valor_fecha'] = $f;
            break;

          case TipoPregunta::NOMBRE_HORA:
            try {
              $camposDetalle['valor_fecha'] = $valorEnviado ? Carbon::parse($valorEnviado)->toTimeString() : null;
            } catch (\Exception) {
              $camposDetalle['valor_fecha'] = null;
            }
            break;

          case TipoPregunta::NOMBRE_BOOLEANO:
            if (is_bool($valorEnviado)) {
              $camposDetalle['valor_booleano'] = $valorEnviado;
            } elseif (in_array(strtolower((string) $valorEnviado), ['true', '1', 'si', 'yes'])) {
              $camposDetalle['valor_booleano'] = true;
            } elseif (in_array(strtolower((string) $valorEnviado), ['false', '0', 'no'])) {
              $camposDetalle['valor_booleano'] = false;
            } else {
              $camposDetalle['valor_booleano'] = null;
            }
            break;

          case TipoPregunta::NOMBRE_SELECCION_MULTIPLE:
            // No se inserta valor principal; manejamos asociación más adelante.
            // Dejamos para after: sincronización de pivot
            break;

          default:
            // Para cualquier otro tipo no contemplado, no guardamos campos extra.
            break;
        }
        // 3) Si hay algún valor para insertar o es selección múltiple
        if (
          count(array_filter($camposDetalle, fn($v, $k) => $k !== 'id_pregunta' && ! is_null($v), ARRAY_FILTER_USE_BOTH)) > 0
          || ($nombreTipo === TipoPregunta::NOMBRE_SELECCION_MULTIPLE && !empty($idsOpcionesMultiple))
        ) {
          $detalle = $encResp->respuestasPregunta()->create($camposDetalle);

          // 4) Manejar selección múltiple (tabla pivot)
          if ($nombreTipo === TipoPregunta::NOMBRE_SELECCION_MULTIPLE && !empty($idsOpcionesMultiple)) {
            $opcionesValidas = OpcionPregunta::where('id_pregunta', $pregunta->id_pregunta)
              ->whereIn('id_opcion_pregunta', $idsOpcionesMultiple)
              ->pluck('id_opcion_pregunta');

            if ($opcionesValidas->isNotEmpty()) {
              $detalle->opcionesSeleccionadas()->sync($opcionesValidas->all());
            }
          }
        }
      }

      return $encResp;
    });
  }

  /**
   * Extrae el valor adecuado de la respuesta según el tipo de pregunta.
   */
  protected function extraerValorFromRespuesta(array $respData)
  {
    if (! empty($respData['valor_texto'])) {
      return $respData['valor_texto'];
    }
    if (! empty($respData['valor_numerico'])) {
      return $respData['valor_numerico'];
    }
    if (! empty($respData['id_opcion_seleccionada_unica'])) {
      $op = OpcionPregunta::find($respData['id_opcion_seleccionada_unica']);
      return $op?->texto_opcion;
    }
    if (! empty($respData['valor_fecha'])) {
      return $respData['valor_fecha'];
    }
    if (! empty($respData['valor_booleano'])) {
      return (bool)$respData['valor_booleano'];
    }
    // Notar que para selección múltiple se guarda en pivot; rara vez mapeamos a tabla externa.
    return null;
  }

  /**
   * Obtener todas las respuestas (detalle) de una encuesta, sólo para Admin/Cliente.
   *
   * @param  int  $idEncuesta
   * @return SupportCollection<RespuestaPregunta>
   */
  public function obtenerPorEncuesta(int $idEncuesta): SupportCollection
  {
    return RespuestaPregunta::whereHas('pregunta', fn($q) => $q->where('id_encuesta', $idEncuesta))->get();
  }

  /**
   * Devuelve el detalle de las respuestas de una cabecera (encuesta_respondida_id)
   * con sus pivotes de selección múltiple.
   *
   * @param int $respondidaId
   * @return \Illuminate\Database\Eloquent\Collection<RespuestaPregunta>
   */
  public function obtenerDetalleRespuestas(int $respondidaId)
  {
    return RespuestaPregunta::with('opcionesSeleccionadas')->where('id_encuesta_respondida', $respondidaId)->get();
  }

  /**
   * Devuelve el detalle de las respuestas de una cabecera (encuesta_respondida_id),
   * con sus pivotes de selección múltiple, y añade entidad/campo para Delphi.
   *
   * @param int $respondidaId
   * @return array
   */
  public function obtenerDetalleRespuestasConMapeo(int $respondidaId): array
  {
    // 1) Obtenemos todas las respuestas_pregunta con su pivot de opciones
    $respuestas = RespuestaPregunta::with('opcionesSeleccionadas')
      ->where('id_encuesta_respondida', $respondidaId)
      ->get();

    // 2) Para no hacer 1 query por cada respuesta, precargamos todos los mapeos de la encuesta:
    //    Primero buscamos: ¿a qué encuesta pertenece esta cabecera?
    $encResp = EncuestaRespondida::find($respondidaId);
    $encuestaId = $encResp->id_encuesta;

    // 3) Precargamos en memoria los mapeos “pregunta_id → (entidad, campo)”
    $mapeos = PreguntaMapeoExterno::where('encuesta_id', $encuestaId)
      ->get()
      ->keyBy('pregunta_id');
    // ahora $mapeos[pregunta_id] = objeto con entidad_externa y campo_externo

    // 4) Armamos el array final:
    $detalle = [];
    foreach ($respuestas as $resp) {
      $pregId = $resp->id_pregunta;

      // “opcionesSeleccionadas” viene en la relación
      $idsOpciones = $resp->opcionesSeleccionadas->pluck('id_opcion_pregunta')->all();

      // Sacamos el mapeo si existe:
      $entidad = null;
      $campo   = null;
      if (isset($mapeos[$pregId])) {
        $entidad = $mapeos[$pregId]->entidad_externa;
        $campo   = $mapeos[$pregId]->campo_externo;
      }

      // Extraemos el valor efectivo que enviaron (texto / numérico / fecha / booleano / opción única):
      $valor = $this->extraerValorFromRespuesta($resp->toArray());
      // (este método ya lo tenías en tu Service)

      $detalle[] = [
        'id_pregunta'                  => $pregId,
        'valor_texto'                  => $resp->valor_texto,
        'valor_numerico'               => $resp->valor_numerico,
        'valor_fecha'                  => $resp->valor_fecha,
        'valor_booleano'               => (bool) $resp->valor_booleano,
        'id_opcion_seleccionada_unica' => $resp->id_opcion_seleccionada_unica,
        'opciones_seleccionadas_ids'   => $idsOpciones,
        // --- AÑADIMOS LOS CAMPOS DE MAPPING PARA DELPHI ---
        'entidad_externa'              => $entidad, // ej: "HPREG05"
        'campo_externo'                => $campo,   // ej: "DESC_PAC"
        'valor_para_externo'           => $valor,   // ejemplo: "María Pérez"
      ];
    }

    return $detalle;
  }
}
