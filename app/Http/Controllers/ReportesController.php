<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ReportesService;
use App\Models\Encuesta; // Para Route-Model Binding
use Illuminate\Http\JsonResponse;
// Opcional: Para exportación a Excel/CSV si lo haces directamente aquí
use App\Exports\RespuestasDetalladasExport;
use App\Exports\ResumenPreguntasExport;
use App\Http\Requests\RespuestasDetalladasRequest;
use App\Http\Requests\ResumenPorPreguntaRequest;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
// use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel;


/**
 * @OA\Tag(
 *   name="Reportes",
 *   description="Endpoints para generar reportes y exportaciones de encuestas"
 * )
 */
class ReportesController extends Controller
{
    public function __construct(private ReportesService $reportesService)
    {
        $this->middleware('auth:sanctum');
        // Asumiendo que sólo el Admin o dueño del Cliente puede ver estos reportes:
        $this->middleware('can:viewAny,App\Models\Encuesta')->only(['respuestasDetalladas', 'resumenPorPregunta']);
    }

    /**
     * @OA\Get(
     *   path="/api/reportes/encuestas/{encuesta}/respuestas-detalladas",
     *   operationId="respuestasDetalladasEncuesta",
     *   tags={"Reportes"},
     *   summary="Obtener respuestas detalladas de una encuesta",
     *   description="Devuelve un array de objetos con cada respuesta y metadatos asociados.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="encuesta",
     *     in="path",
     *     description="ID de la encuesta",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Datos de respuestas detalladas",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/RespuestaDetalladaDto")
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso")
     * )
     */
    public function respuestasDetalladas(RespuestasDetalladasRequest $request, Encuesta $encuesta): JsonResponse
    {
        $this->authorize('viewReport', $encuesta); // 1) Autorizar vía EncuestaPolicy::viewReport(User, Encuesta)

        $filtros = $request->validated(); // 2) Recuperar filtros ya validados por RespuestasDetalladasRequest

        // 3) Llamar al servicio para obtener los datos (DTOs, arreglo o Collection)
        $respuestas = $this->reportesService->obtenerRespuestasDetalladas($encuesta->id_encuesta, $filtros);

        return response()->json($respuestas); // 4) Devolver JSON puro (laravel convierte Collection/array a JSON)
    }

    /**
     * @OA\Get(
     *   path="/api/reportes/encuestas/{encuesta}/resumen-por-pregunta",
     *   operationId="resumenPorPreguntaEncuesta",
     *   tags={"Reportes"},
     *   summary="Obtener resumen agregado por pregunta",
     *   description="Devuelve conteos y porcentajes de respuestas para cada pregunta de la encuesta.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="encuesta",
     *     in="path",
     *     description="ID de la encuesta",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Resumen por pregunta",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/ResumenPreguntaDto")
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso")
     * )
     */
    public function resumenPorPregunta(ResumenPorPreguntaRequest $request, Encuesta $encuesta): JsonResponse
    {
        // 1) Autorizar vía EncuestaPolicy::viewReport (mismo permiso para ver resúmenes)
        $this->authorize('viewReport', $encuesta);

        // 2) Obtener filtros validados
        $filtros = $request->validated();

        // 3) Llamar al servicio
        $resumen = $this->reportesService->obtenerResumenPorPregunta($encuesta->id_encuesta, $filtros);

        // 4) Mapear a Resource si deseas transformarlo o devolver crudo:
        //    return ResumenPreguntaResource::collection($resumen);
        return response()->json($resumen);
    }

    // --- Endpoints de Exportación (Ejemplos) ---
    // Para esto, necesitarás instalar maatwebsite/excel: composer require maatwebsite/excel

    /**
     * GET  /api/reportes/encuestas/{encuesta}/exportar-respuestas-detalladas
     * Exportar respuestas detalladas a Excel.
     * Body o query params:
     *    fecha_desde, fecha_hasta, correo (opcional)
     */
    public function exportarRespuestasDetalladasExcel(RespuestasDetalladasRequest $request, Encuesta $encuesta)
    {
        $this->authorize('viewReport', $encuesta); // 1) Autorizar

        $filtros = $request->validated(); // 2) Obtener filtros

        // 3) Generar nombre de archivo
        $slug         = Str::slug($encuesta->nombre);
        $timestamp     = now()->format('Ymd_His');
        $nombreArchivo = "respuestas_{$slug}_{$timestamp}.xlsx";

        // 4) Devolver descarga Excel con el Export correspondiente
        return Excel::download(
            new RespuestasDetalladasExport($encuesta->id_encuesta, $filtros),
            $nombreArchivo
        );
    }

    /**
     * @OA\Get(
     *   path="/api/reportes/encuestas/{encuesta}/exportar/resumen-por-pregunta.csv",
     *   operationId="exportarResumenPorPreguntaCsv",
     *   tags={"Reportes"},
     *   summary="Exportar resumen por pregunta a CSV",
     *   description="Genera y descarga un CSV con el resumen por pregunta de la encuesta.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="encuesta",
     *     in="path",
     *     description="ID de la encuesta",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Stream CSV de resumen por pregunta",
     *     @OA\MediaType(
     *       mediaType="text/csv",
     *       @OA\Schema(type="string", format="binary")
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso")
     * )
     */
    public function exportarResumenPreguntaCsv(ResumenPorPreguntaRequest $request, Encuesta $encuesta): BinaryFileResponse
    {
        $this->authorize('viewReport', $encuesta); // 1) Autorizar vía EncuestaPolicy::viewReport

        $filtros = $request->validated(); // 2) Obtener filtros validados (fecha_desde, fecha_hasta)

        // 3) Generar nombre de archivo
        $slug        = Str::slug($encuesta->nombre);
        $timestamp   = now()->format('Ymd_His');
        $nombreArchivo = "resumen_preguntas_{$slug}_{$timestamp}.csv";

        // 4) Devolver descarga CSV usando Maatwebsite/Excel
        return Excel::download(new ResumenPreguntasExport($encuesta->id_encuesta, $filtros), $nombreArchivo, Excel::CSV);
    }

    /**
     * @OA\Get(
     *   path="/api/reportes/encuestas/{encuesta}/exportar/respuestas-detalladas.csv",
     *   operationId="exportarRespuestasDetalladasCsv",
     *   tags={"Reportes"},
     *   summary="Exportar respuestas detalladas a CSV",
     *   description="Genera y descarga un CSV con todas las respuestas detalladas de la encuesta.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="encuesta",
     *     in="path",
     *     description="ID de la encuesta",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Stream CSV de respuestas detalladas",
     *     @OA\MediaType(
     *       mediaType="text/csv",
     *       @OA\Schema(type="string", format="binary")
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso")
     * )
     */
    public function exportarRespuestasDetalladasCsv(RespuestasDetalladasRequest $request, Encuesta $encuesta)
    {
        $this->authorize('viewReport', $encuesta); // 1) Autorizar

        $filtros = $request->validated(); // 2) Obtener filtros

        // 3) Generar nombre de archivo
        $slug         = Str::slug($encuesta->nombre);
        $timestamp     = now()->format('Ymd_His');
        $nombreArchivo = "respuestas_{$slug}_{$timestamp}.csv";

        // 4) Devolver descarga CSV con el Export correspondiente
        return Excel::download(
            new RespuestasDetalladasExport($encuesta->id_encuesta, $filtros),
            $nombreArchivo,
            Excel::CSV
        );
    }
}
