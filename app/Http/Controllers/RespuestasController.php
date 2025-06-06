<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\RespuestasService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreRespuestaRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Encuesta;
use App\Http\Resources\RespuestaResource;
use App\Models\EncuestaRespondida;

/**
 * @OA\Tag(
 *   name="Respuestas",
 *   description="Endpoints para almacenar y consultar respuestas de encuestas"
 * )
 */
class RespuestasController extends Controller
{
    public function __construct(private RespuestasService $service)
    {
        // La mayoría de endpoints son públicos, pero los reportes requieren autenticación
        $this->middleware('auth:sanctum')->only([
            'obtenerRespuestasDetalladas',
            'obtenerResumenPorPregunta',
            'descargarResumenPorPregunta'
        ]);

        // El endpoint store() puede ser público o protegido.
        // Si quieres permitir contestar anónimamente: no pongas middleware en store().
        // Si solo “no anónimos” deben contestar: descomenta la siguiente línea:
        // $this->middleware('auth:sanctum')->only(['store']);
    }

    /**
     * @OA\Post(
     *   path="/api/encuestas/{encuesta}/respuestas",
     *   operationId="storeRespuestasEncuesta",
     *   tags={"Respuestas"},
     *   summary="Enviar respuestas de una encuesta (público)",
     *   description="Recibe un array de respuestas para la encuesta dada y las guarda en BD.",
     *   @OA\Parameter(
     *     name="encuesta",
     *     in="path",
     *     description="ID de la encuesta",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/StoreRespuestaRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Respuestas guardadas exitosamente",
     *     @OA\JsonContent(
     *       @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/RespuestaResource"))
     *     )
     *   ),
     *   @OA\Response(response=400, description="Datos inválidos o fecha fuera de rango"),
     *   @OA\Response(response=404, description="Encuesta no encontrada")
     * )
     */
    public function store(StoreRespuestaRequest $request, int $encuestaId): JsonResponse
    {
        // 1) Verificamos que la encuesta exista (o 404)
        $enc = Encuesta::findOrFail($encuestaId);

        // 2) Si es un cuestionario y no está activo, devolvemos 403
        if ($enc->es_cuestionario && !$enc->esta_activa) {
            return response()->json(['message' => 'El cuestionario ya no está disponible.'], 403);
        }

        $datos   = $request->validated();
        $metadatos = [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        try {
            $encRes = $this->service->guardarRespuestas($encuestaId, $datos, [
                'ip'        => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'message' => 'Respuestas guardadas exitosamente.',
                'id_encuesta_respondida' => $encRes->id_encuesta_respondida,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            \Log::error("Error al guardar respuestas: " . $e->getMessage());
            return response()->json(['message' => 'Ocurrió un error al procesar las respuestas.'], 500);
        }
    }

    /**
     * @OA\Get(
     *   path="/api/encuestas/{encuesta}/respuestas",
     *   operationId="indexRespuestasEncuesta",
     *   tags={"Respuestas"},
     *   summary="Listar respuestas de una encuesta (Admin/Cliente)",
     *   description="Devuelve todas las respuestas almacenadas para la encuesta dada.",
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
     *     description="Listado de respuestas",
     *     @OA\JsonContent(
     *       @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/RespuestaResource"))
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso")
     * )
     */
    public function index(int $encuestaId): JsonResponse
    {
        $user = Auth::user();

        // Solo admin o cliente dueño de la encuesta puede ver el listado
        $enc = Encuesta::find($encuestaId);
        if (! $enc) {
            return response()->json(['message' => 'Encuesta no encontrada.'], 404);
        }

        // Autorización manual: solo Admin o cliente dueño puede ver
        if (! $user->esRol('Administrador') && $user->id_cliente !== $enc->id_cliente) {
            abort(403, 'No autorizado para ver estas respuestas.');
        }

        $coleccion = $this->service->obtenerPorEncuesta($encuestaId);

        return response()->json(['data' => RespuestaResource::collection($coleccion)], 200);
    }

    /**
     * @OA\Get(
     *   path="/api/encuestas/{encuesta}/respuestas/{respondida}",
     *   operationId="showDetalleRespuestas",
     *   tags={"Respuestas"},
     *   summary="Obtener detalle de las respuestas de una cabecera (encuesta_respondida_id)",
     *   description="Devuelve cada respuesta con entidad_externa y campo_externo para que Delphi lo actualice.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="encuesta",
     *     in="path",
     *     description="ID de la encuesta",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Parameter(
     *     name="respondida",
     *     in="path",
     *     description="ID de encuesta_respondida",
     *     required=true,
     *     @OA\Schema(type="integer", example=123)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Detalle completo de respuestas con mapeo",
     *     @OA\JsonContent(
     *       @OA\Property(property="encuesta_id",             type="integer", example=1),
     *       @OA\Property(property="encuesta_respondida_id",  type="integer", example=123),
     *       @OA\Property(property="paciente_id",             type="integer", example=45),
     *       @OA\Property(
     *         property="respuestas",
     *         type="array",
     *         @OA\Items(
     *           @OA\Property(property="id_pregunta", type="integer", example=12),
     *           @OA\Property(property="valor_texto", type="string", example="María Pérez"),
     *           @OA\Property(property="valor_numerico", type="number", example=37),
     *           @OA\Property(property="valor_fecha", type="string", example="2023-10-15"),
     *           @OA\Property(property="valor_booleano", type="boolean", example=false),
     *           @OA\Property(property="id_opcion_seleccionada_unica", type="integer", example=5),
     *           @OA\Property(property="opciones_seleccionadas_ids", type="array", @OA\Items(type="integer")),
     *           @OA\Property(property="entidad_externa", type="string", example="G_PACIENTES"),
     *           @OA\Property(property="campo_externo", type="string", example="NOMBRE"),
     *           @OA\Property(property="valor_para_externo", type="string", example="María Pérez")
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function showDetalle(int $encuestaId, int $respondidaId): JsonResponse
    {
        $user = Auth::user();

        // 1) La cabecera debe existir, y pertenecer a la encuesta:
        $encResp = EncuestaRespondida::find($respondidaId);
        if (! $encResp || $encResp->id_encuesta != $encuestaId) {
            return response()->json(['message' => 'No se encontró la respuesta indicada.'], 404);
        }

        // 2) Solo admin o cliente dueño de la encuesta puede ver:
        $enc = $encResp->encuesta; // relación EncuestaRespondida→Encuesta
        if (! $user->esRol('Administrador') && $user->id_cliente !== $enc->id_cliente) {
            abort(403, 'No autorizado para ver esta respuesta.');
        }

        // 3) Obtener detalle de RespuestaPregunta (con pivot de opciones)
        $detalleConMapeo = $this->service->obtenerDetalleRespuestasConMapeo($respondidaId);

        return response()->json([
            'encuesta_id'             => $encuestaId,
            'encuesta_respondida_id'  => $respondidaId,
            'paciente_id'             => $encResp->id_paciente,
            'respuestas'              => $detalleConMapeo,
        ], 200);
    }
    // Ejemplo de showDetalle:
    // {
    //     "encuesta_id": 7,
    //     "encuesta_respondida_id": 456,
    //     "paciente_id": 123,
    //     "respuestas": [
    //       {
    //         "id_pregunta": 12,
    //         "valor_texto": "María Pérez",
    //         "valor_numerico": null,
    //         "valor_fecha": null,
    //         "valor_booleano": false,
    //         "id_opcion_seleccionada_unica": null,
    //         "opciones_seleccionadas_ids": [],
    //         "entidad_externa": "G_PACIENTES",
    //         "campo_externo": "NOMBRE",
    //         "valor_para_externo": "María Pérez"
    //       },
    //       {
    //         "id_pregunta": 13,
    //         "valor_texto": null,
    //         "valor_numerico": 37,
    //         "valor_fecha": null,
    //         "valor_booleano": false,
    //         "id_opcion_seleccionada_unica": null,
    //         "opciones_seleccionadas_ids": [],
    //         "entidad_externa": "G_PACIENTES",
    //         "campo_externo": "EDAD",
    //         "valor_para_externo": 37
    //       },
    //       {
    //         "id_pregunta": 14,
    //         "valor_texto": null,
    //         "valor_numerico": null,
    //         "valor_fecha": null,
    //         "valor_booleano": false,
    //         "id_opcion_seleccionada_unica": 5,
    //         "opciones_seleccionadas_ids": [],
    //         "entidad_externa": "G_PACIENTES",
    //         "campo_externo": "ESTADO_CIVIL",
    //         "valor_para_externo": "Casado"
    //       },
    //       // …
    //     ]
    //   }
}
