<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\RespuestasService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreRespuestaRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Encuesta;
use App\Http\Resources\RespuestaResource;

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
        $datos   = $request->validated();
        $metadatos = [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        try {
            $encuestaRespondida = $this->service->guardarRespuestas($encuestaId, $datos, $metadatos);

            return response()->json([
                'message' => 'Respuestas guardadas exitosamente.',
                'id_encuesta_respondida' => $encuestaRespondida->id_encuesta_respondida,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            \Log::error("Error al guardar respuestas: " . $e->getMessage());
            return response()->json([
                'message' => 'Ocurrió un error al procesar las respuestas.'
            ], 500);
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

        // Autorización manual: solo Admin o cliente dueño puede ver
        if (! $user->esRol('administrador') && $user->id_cliente !== Encuesta::find($encuestaId)?->id_cliente) {
            abort(403, 'No autorizado para ver estas respuestas.');
        }

        $coleccion = $this->service->obtenerPorEncuesta($encuestaId);

        return response()->json(['data' => RespuestaResource::collection($coleccion)], 200);
    }
}
