<?php

namespace App\Services;

use App\Models\EncuestaRespondida;
use App\Models\Pregunta;
use App\Models\RespuestaPregunta;
use App\Models\TipoPregunta;
use App\DTOs\Reportes\RespuestaDetalladaDto;
use App\DTOs\Reportes\ResumenPreguntaDto;
use App\DTOs\Reportes\ResumenOpcionDto;
use Carbon\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportesService
{
  /**
   * Obtener respuestas detalladas para una encuesta, con filtros opcionales.
   *
   * @param  int    $idEncuesta
   * @param  array  $filtros  ['fecha_desde' => 'Y-m-d', 'fecha_hasta' => 'Y-m-d', 'correo' => 'texto']
   * @return SupportCollection<RespuestaDetalladaDto>
   */
  public function obtenerRespuestasDetalladas(int $idEncuesta, array $filtros = []): SupportCollection
  {
    // Base de query: join entre respuestas_pregunta, preguntas, secciones, encuestas_respondidas y clientes
    $query = RespuestaPregunta::query()
      ->select([
        'respuestas_pregunta.id_respuesta_pregunta',
        'respuestas_pregunta.valor_texto',
        'respuestas_pregunta.valor_numerico',
        'respuestas_pregunta.valor_fecha',
        'respuestas_pregunta.valor_booleano',
        'respuestas_pregunta.id_opcion_seleccionada_unica',
        'respuestas_pregunta.created_at as fecha_registro',
        'p.id_pregunta',
        'p.texto_pregunta',
        'p.orden as orden_pregunta',
        'tp.id_tipo_pregunta',
        'tp.nombre as nombre_tipo',
        's.id_seccion',
        's.nombre as nombre_seccion',
        's.orden as orden_seccion',
        'er.id_encuesta_respondida',
        'er.correo_respuesta',
        'er.fecha_inicio_respuesta',
        'er.fecha_fin_respuesta',
        'e.id_encuesta',
        'e.nombre as nombre_encuesta',
        'c.alias as alias_cliente',
        'c.razon_social as razon_social_cliente'
      ])
      ->join('preguntas as p', 'respuestas_pregunta.id_pregunta', '=', 'p.id_pregunta')
      ->join('tipos_pregunta as tp', 'p.id_tipo_pregunta', '=', 'tp.id_tipo_pregunta')
      ->join('secciones_encuesta as s', 'p.id_seccion', '=', 's.id_seccion')
      ->join('encuestas_respondidas as er', 'respuestas_pregunta.id_encuesta_respondida', '=', 'er.id_encuesta_respondida')
      ->join('encuestas as e', 'er.id_encuesta', '=', 'e.id_encuesta')
      ->leftJoin('clientes as c', 'e.id_cliente', '=', 'c.id_cliente')
      ->where('e.id_encuesta', $idEncuesta);

    // Aplicar filtros de fecha y correo si vienen
    if (!empty($filtros['fecha_desde'])) {
      $desde = Carbon::parse($filtros['fecha_desde'])->startOfDay();
      $query->whereDate('er.fecha_inicio_respuesta', '>=', $desde);
    }
    if (!empty($filtros['fecha_hasta'])) {
      $hasta = Carbon::parse($filtros['fecha_hasta'])->endOfDay();
      $query->whereDate('er.fecha_inicio_respuesta', '<=', $hasta);
    }
    if (!empty($filtros['correo'])) {
      $query->where('er.correo_respuesta', 'like', '%' . $filtros['correo'] . '%');
    }

    $resultado = $query
      ->orderBy('er.id_encuesta_respondida')
      ->orderBy('s.orden')
      ->orderBy('p.orden')
      ->get();

    return $resultado->map(function ($item) {
      // Calcular tiempo transcurrido entre inicio y fin de respuesta
      $tiempo = null;
      if ($item->fecha_inicio_respuesta && $item->fecha_fin_respuesta) {
        $inicio = Carbon::parse($item->fecha_inicio_respuesta);
        $fin = Carbon::parse($item->fecha_fin_respuesta);
        $tiempo = $fin->diff($inicio)->format('%H:%I:%S');
      }

      // Devolver un DTO (si los tienes). Si no, devuelve un array asociativo en su lugar.
      return new RespuestaDetalladaDto(
        id_encuesta: $item->id_encuesta,
        nombre_encuesta: $item->nombre_encuesta,
        alias_cliente: $item->alias_cliente,
        razon_social_cliente: $item->razon_social_cliente,
        id_encuesta_respondida: $item->id_encuesta_respondida,
        correo_respuesta_encuesta: $item->correo_respuesta,
        fecha_inicial_respuesta: Carbon::parse($item->fecha_inicio_respuesta),
        fecha_final_respuesta: $item->fecha_fin_respuesta ? Carbon::parse($item->fecha_fin_respuesta) : null,
        tiempo_transcurrido: $tiempo,
        id_seccion: $item->id_seccion,
        nombre_seccion: $item->nombre_seccion,
        orden_seccion: $item->orden_seccion,
        id_pregunta: $item->id_pregunta,
        texto_pregunta: $item->texto_pregunta,
        orden_pregunta_en_seccion: $item->orden_pregunta,
        nombre_tipo_pregunta: $item->nombre_tipo,
        id_tipo_pregunta: $item->id_tipo_pregunta,
        id_respuesta_pregunta_detalle: $item->id_respuesta_pregunta,
        valor_respuesta_texto_formateado: $item->valor_texto,
        fecha_registro_respuesta_detalle: Carbon::parse($item->fecha_registro),
        // valor_respuesta_texto: $item->valor_texto,
        // valor_respuesta_numerico: $item->valor_numerico,
        // valor_respuesta_fecha: $item->valor_fecha,
        // valor_respuesta_booleano: $item->valor_booleano,
        // id_opcion_seleccionada: $item->id_opcion_seleccionada_unica,
        // fecha_registro_respuesta: Carbon::parse($item->fecha_registro)
      );
    });
  }

  /**
   * Obtener resumen de respuestas por pregunta para una encuesta, con filtros opcionales.
   *
   * @param  int    $idEncuesta
   * @param  array  $filtros  ['fecha_desde' => 'Y-m-d', 'fecha_hasta' => 'Y-m-d']
   * @return SupportCollection<ResumenPreguntaDto>
   */
  public function obtenerResumenPorPregunta(int $idEncuesta, array $filtros = []): SupportCollection
  {
    // 1) Obtener todas las preguntas de esa encuesta, con su tipo y sección
    $preguntas = Pregunta::query()
      ->select([
        'preguntas.id_pregunta',
        'preguntas.texto_pregunta',
        'preguntas.orden as orden_pregunta',
        'preguntas.es_obligatoria',
        'tp.id_tipo_pregunta',
        'tp.nombre as nombre_tipo',
        'tp.requiere_opciones',
        'tp.es_seleccion_multiple',
        's.id_seccion',
        's.nombre as nombre_seccion',
        's.orden as orden_seccion'
      ])
      ->join('tipos_pregunta as tp', 'preguntas.id_tipo_pregunta', '=', 'tp.id_tipo_pregunta')
      ->join('secciones_encuesta as s', 'preguntas.id_seccion', '=', 's.id_seccion')
      ->where('s.id_encuesta', $idEncuesta)
      ->with(['opcionesPregunta:id_opcion_pregunta,id_pregunta,texto_opcion,orden'])
      ->orderBy('s.orden')
      ->orderBy('preguntas.orden')
      ->get();

    if ($preguntas->isEmpty()) {
      return new SupportCollection();
    }

    // 2) Filtrar las encuestas respondidas por fecha (si aplica), para este idEncuesta
    $encuestasRespQuery = EncuestaRespondida::query()
      ->where('id_encuesta', $idEncuesta);

    if (!empty($filtros['fecha_desde'])) {
      $desde = Carbon::parse($filtros['fecha_desde'])->startOfDay();
      $encuestasRespQuery->whereDate('fecha_inicio_respuesta', '>=', $desde);
    }
    if (!empty($filtros['fecha_hasta'])) {
      $hasta = Carbon::parse($filtros['fecha_hasta'])->endOfDay();
      $encuestasRespQuery->whereDate('fecha_inicio_respuesta', '<=', $hasta);
    }

    $idsEncuestasFiltradas = $encuestasRespQuery->pluck('id_encuesta_respondida');
    $totalEncuestasConsideradas = $idsEncuestasFiltradas->count();

    // Si no hay encuestas respondidas, devolvemos un resumen vacío para cada pregunta
    if ($totalEncuestasConsideradas === 0) {
      return $preguntas->map(function ($preg) {
        $opcionesDto = null;
        if ($preg->requiere_opciones && $preg->opcionesPregunta) {
          $opcionesDto = $preg->opcionesPregunta->map(fn($op) => new ResumenOpcionDto(
            id_opcion_pregunta: $op->id_opcion_pregunta,
            texto_opcion: $op->texto_opcion,
            conteo: 0,
            porcentaje: 0.0
          ))->all();
        }

        return new ResumenPreguntaDto(
          id_pregunta: $preg->id_pregunta,
          texto_pregunta: $preg->texto_pregunta,
          orden_pregunta_en_seccion: $preg->orden_pregunta,
          nombre_tipo_pregunta: $preg->nombre_tipo,
          id_tipo_pregunta: $preg->id_tipo_pregunta,
          id_seccion: $preg->id_seccion,
          nombre_seccion: $preg->nombre_seccion,
          orden_seccion: $preg->orden_seccion,
          total_respuestas_para_pregunta: 0,
          resumen_opciones: $opcionesDto,
          valor_promedio: null,
          valor_minimo: null,
          valor_maximo: null,
          conteo_nulos_o_no_aplica: 0
        );
      });
    }

    $coleccionResumen = new SupportCollection();

    foreach ($preguntas as $preg) {
      // Query base para obtener sólo las respuestas de esta pregunta en las encuestas filtradas
      $respQuery = RespuestaPregunta::query()
        ->where('id_pregunta', $preg->id_pregunta)
        ->whereIn('id_encuesta_respondida', $idsEncuestasFiltradas);

      // 2.a) conteo de respuestas válidas (no nulas) para esta pregunta
      $conteoValidas = (clone $respQuery)
        ->where(fn($q) => $q
          ->whereNotNull('valor_texto')
          ->orWhereNotNull('valor_numerico')
          ->orWhereNotNull('valor_fecha')
          ->orWhereNotNull('valor_booleano')
          ->orWhereNotNull('id_opcion_seleccionada_unica')
          ->orWhereHas('opcionesSeleccionadas'))
        ->count();

      $resumenOpciones   = null;
      $valorPromedio     = null;
      $valorMinimo       = null;
      $valorMaximo       = null;

      $basePorcentaje = $totalEncuestasConsideradas ?: 1;

      // 2.b) Si la pregunta requiere opciones
      if ($preg->requiere_opciones) {
        $resumenOpciones = new SupportCollection();
        $opcionesMap = $preg->opcionesPregunta->keyBy('id_opcion_pregunta');

        if ($preg->es_seleccion_multiple) {
          $conteos = DB::table('respuesta_opcion_seleccionada as ros')
            ->join('respuestas_pregunta as rp', 'ros.id_respuesta_pregunta', '=', 'rp.id_respuesta_pregunta')
            ->where('rp.id_pregunta', $preg->id_pregunta)
            ->whereIn('rp.id_encuesta_respondida', $idsEncuestasFiltradas)
            ->select('ros.id_opcion_pregunta', DB::raw('COUNT(ros.id_opcion_pregunta) as conteo'))
            ->groupBy('ros.id_opcion_pregunta')
            ->pluck('conteo', 'id_opcion_pregunta');
        } else {
          $conteos = (clone $respQuery)
            ->whereNotNull('id_opcion_seleccionada_unica')
            ->select('id_opcion_seleccionada_unica', DB::raw('COUNT(id_opcion_seleccionada_unica) as conteo'))
            ->groupBy('id_opcion_seleccionada_unica')
            ->pluck('conteo', 'id_opcion_seleccionada_unica');
        }

        foreach ($opcionesMap as $idOp => $opModelo) {
          $cnt    = $conteos->get($idOp, 0);
          $pct    = $cnt / $basePorcentaje * 100;
          $resumenOpciones->push(new ResumenOpcionDto(
            id_opcion_pregunta: $idOp,
            texto_opcion: $opModelo->texto_opcion,
            conteo: $cnt,
            porcentaje: round($pct, 2)
          ));
        }
      }
      // 2.c) Si la pregunta es numérica o valoración (sin opciones), calculamos agregados
      elseif (in_array($preg->nombre_tipo, [TipoPregunta::NOMBRE_VALOR_NUMERICO, TipoPregunta::NOMBRE_VALORACION])) {
        $ag = (clone $respQuery)
          ->whereNotNull('valor_numerico')
          ->selectRaw('AVG(valor_numerico) as prom, MIN(valor_numerico) as min, MAX(valor_numerico) as max')
          ->first();

        $valorPromedio = $ag?->prom ? round($ag->prom, 2) : null;
        $valorMinimo   = $ag?->min;
        $valorMaximo   = $ag?->max;
      }
      // 2.d) Si la pregunta es booleano (Sí/No)
      elseif ($preg->nombre_tipo === TipoPregunta::NOMBRE_BOOLEANO) {
        $conteosBool = (clone $respQuery)
          ->select('valor_booleano', DB::raw('COUNT(id_respuesta_pregunta) as conteo'))
          ->whereNotNull('valor_booleano')
          ->groupBy('valor_booleano')
          ->get()
          ->keyBy('valor_booleano');

        $resumenOpciones = new SupportCollection();
        foreach ([1 => 'Sí', 0 => 'No'] as $val => $texto) {
          $cnt = $conteosBool->get($val)->conteo ?? 0;
          $pct = $cnt / $basePorcentaje * 100;
          $resumenOpciones->push(new ResumenOpcionDto(
            id_opcion_pregunta: $val,
            texto_opcion: $texto,
            conteo: $cnt,
            porcentaje: round($pct, 2)
          ));
        }
      }

      // 2.e) conteo de respuestas nulas/no aplicadas
      $conteoNulos = $totalEncuestasConsideradas - $conteoValidas;
      if ($conteoNulos < 0) {
        $conteoNulos = 0;
      }

      $coleccionResumen->push(new ResumenPreguntaDto(
        id_pregunta: $preg->id_pregunta,
        texto_pregunta: $preg->texto_pregunta,
        orden_pregunta_en_seccion: $preg->orden_pregunta,
        nombre_tipo_pregunta: $preg->nombre_tipo,
        id_tipo_pregunta: $preg->id_tipo_pregunta,
        id_seccion: $preg->id_seccion,
        nombre_seccion: $preg->nombre_seccion,
        orden_seccion: $preg->orden_seccion,
        total_respuestas_para_pregunta: $conteoValidas,
        resumen_opciones: $resumenOpciones?->all(),
        valor_promedio: $valorPromedio,
        valor_minimo: $valorMinimo,
        valor_maximo: $valorMaximo,
        conteo_nulos_o_no_aplica: $conteoNulos
      ));
    }

    return $coleccionResumen;
  }

  /**
   * Exportar respuestas detalladas a CSV (StreamedResponse).
   *
   * @param  int  $idEncuesta
   * @param  array $filtros  (mismos de obtenerRespuestasDetalladas)
   * @return StreamedResponse
   */
  public function exportarRespuestasDetalladasCsv(int $idEncuesta, array $filtros = []): StreamedResponse
  {
    // Definir encabezados CSV
    $titulos = [
      'ID Encuesta',
      'Nombre Encuesta',
      'Alias Cliente',
      'Razón Social Cliente',
      'ID Encuesta Respondida',
      'Correo Respuesta',
      'Fecha Inicio Respuesta',
      'Fecha Fin Respuesta',
      'Tiempo Transcurrido',
      'Sección',
      'Pregunta',
      'Tipo Pregunta',
      'Valor Texto',
      'Valor Numérico',
      'Valor Fecha',
      'Valor Booleano',
      'ID Opción Seleccionada',
      'Fecha Registro Respuesta'
    ];

    // Obtener colección de DTOs
    $coleccion = $this->obtenerRespuestasDetalladas($idEncuesta, $filtros);

    return new StreamedResponse(function () use ($titulos, $coleccion) {
      $handle = fopen('php://output', 'w');
      fputcsv($handle, $titulos);

      foreach ($coleccion as $dto) {
        fputcsv($handle, [
          $dto->id_encuesta,
          $dto->nombre_encuesta,
          $dto->alias_cliente,
          $dto->razon_social_cliente,
          $dto->id_encuesta_respondida,
          $dto->correo_respuesta_encuesta,
          $dto->fecha_inicial_respuesta->format('Y-m-d H:i:s'),
          $dto->fecha_final_respuesta?->format('Y-m-d H:i:s'),
          $dto->tiempo_transcurrido,
          $dto->nombre_seccion,
          $dto->texto_pregunta,
          $dto->nombre_tipo_pregunta,
          $dto->valor_respuesta_texto,
          $dto->valor_respuesta_numerico,
          $dto->valor_respuesta_fecha,
          $dto->valor_respuesta_booleano !== null ? ($dto->valor_respuesta_booleano ? 'Sí' : 'No') : null,
          $dto->id_opcion_seleccionada,
          $dto->fecha_registro_respuesta->format('Y-m-d H:i:s'),
        ]);
      }
      fclose($handle);
    }, 200, [
      'Content-Type' => 'text/csv; charset=UTF-8',
      'Content-Disposition' => 'attachment; filename="respuestas_detalladas.csv"',
    ]);
  }

  /**
   * Exportar resumen por pregunta a CSV (StreamedResponse).
   *
   * @param  int   $idEncuesta
   * @param  array $filtros  (mismos de obtenerResumenPorPregunta)
   * @return StreamedResponse
   */
  public function exportarResumenPorPreguntaCsv(int $idEncuesta, array $filtros = []): StreamedResponse
  {
    $titulos = [
      'ID Pregunta',
      'Texto Pregunta',
      'ID Sección',
      'Nombre Sección',
      'Orden Pregunta',
      'Tipo Pregunta',
      'Total Respuestas Válidas',
      'ID Opción',
      'Texto Opción',
      'Conteo',
      'Porcentaje',
      'Valor Promedio',
      'Valor Mínimo',
      'Valor Máximo',
      'Conteo Nulos/No Aplica'
    ];

    $coleccion = $this->obtenerResumenPorPregunta($idEncuesta, $filtros);

    return new StreamedResponse(function () use ($titulos, $coleccion) {
      $handle = fopen('php://output', 'w');
      fputcsv($handle, $titulos);

      foreach ($coleccion as $dto) {
        // Si hay opciones, escribimos una línea por cada opción
        if (!empty($dto->resumen_opciones)) {
          foreach ($dto->resumen_opciones as $opDto) {
            fputcsv($handle, [
              $dto->id_pregunta,
              $dto->texto_pregunta,
              $dto->id_seccion,
              $dto->nombre_seccion,
              $dto->orden_pregunta_en_seccion,
              $dto->nombre_tipo_pregunta,
              $dto->total_respuestas_para_pregunta,
              $opDto->id_opcion_pregunta,
              $opDto->texto_opcion,
              $opDto->conteo,
              $opDto->porcentaje,
              $dto->valor_promedio,
              $dto->valor_minimo,
              $dto->valor_maximo,
              $dto->conteo_nulos_o_no_aplica,
            ]);
          }
        } else {
          // Si no hay opciones (por ejemplo, pregunta numérica o de texto), imprimimos sin sección de opciones
          fputcsv($handle, [
            $dto->id_pregunta,
            $dto->texto_pregunta,
            $dto->id_seccion,
            $dto->nombre_seccion,
            $dto->orden_pregunta_en_seccion,
            $dto->nombre_tipo_pregunta,
            $dto->total_respuestas_para_pregunta,
            null,
            null,
            null,
            null,
            $dto->valor_promedio,
            $dto->valor_minimo,
            $dto->valor_maximo,
            $dto->conteo_nulos_o_no_aplica,
          ]);
        }
      }

      fclose($handle);
    }, 200, [
      'Content-Type'        => 'text/csv; charset=UTF-8',
      'Content-Disposition' => 'attachment; filename="resumen_por_pregunta.csv"',
    ]);
  }
}
