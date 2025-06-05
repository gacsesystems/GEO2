<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SeccionEncuesta;
use App\Models\Pregunta;
use App\Services\PreguntaService;
use App\Http\Requests\StorePreguntaRequest;
use App\Http\Requests\UpdatePreguntaRequest;
use App\Http\Requests\ReordenarPreguntaRequest;
use App\Http\Resources\PreguntaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *   name="Preguntas",
 *   description="CRUD y reordenamiento de Preguntas dentro de una Sección de Encuesta"
 * )
 */
class PreguntaController extends Controller
{
    public function __construct(private PreguntaService $service)
    {
        $this->middleware('auth:sanctum');

        // Políticas de autorización
        $this->middleware('can:viewAny,App\Models\SeccionEncuesta')->only('index');
        $this->middleware('can:create,App\Models\SeccionEncuesta')->only('store');
        $this->middleware('can:view,pregunta')->only('show');
        $this->middleware('can:update,pregunta')->only('update');
        $this->middleware('can:delete,pregunta')->only('destroy');
        $this->middleware('can:reordenar,pregunta')->only('reordenar');
    }

    protected function autorizarAccionEncuesta(SeccionEncuesta $seccion): void
    {
        $user = Auth::user();
        $encuesta = $seccion->encuesta; // Asume que la relación está disponible
        if (!$user->esRol('administrador') && ($encuesta->id_cliente !== $user->id_cliente)) {
            abort(403, 'No autorizado para modificar esta encuesta.');
        }
    }

    /**
     * @OA\Get(
     *   path="/api/encuestas/{encuesta}/secciones/{seccionEncuesta}/preguntas",
     *   operationId="indexPreguntas",
     *   tags={"Preguntas"},
     *   summary="Listar preguntas de una sección",
     *   description="Devuelve todas las preguntas de la sección especificada.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="encuesta",
     *     in="path",
     *     description="ID de la encuesta",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Parameter(
     *     name="seccionEncuesta",
     *     in="path",
     *     description="ID de la sección",
     *     required=true,
     *     @OA\Schema(type="integer", example=5)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Lista de preguntas",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/PreguntaResource")
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Sección no encontrada")
     * )
     */
    public function index(SeccionEncuesta $seccionEncuesta): AnonymousResourceCollection
    {
        $preguntas = $this->service->obtenerPorSeccion($seccionEncuesta->id_seccion);
        return PreguntaResource::collection($preguntas);
    }

    /**
     * @OA\Post(
     *   path="/api/encuestas/{encuesta}/secciones/{seccionEncuesta}/preguntas",
     *   operationId="storePregunta",
     *   tags={"Preguntas"},
     *   summary="Crear una nueva pregunta",
     *   description="Añade una pregunta a la sección especificada.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="encuesta",
     *     in="path",
     *     description="ID de la encuesta",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Parameter(
     *     name="seccionEncuesta",
     *     in="path",
     *     description="ID de la sección",
     *     required=true,
     *     @OA\Schema(type="integer", example=5)
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/StorePreguntaRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Pregunta creada",
     *     @OA\JsonContent(ref="#/components/schemas/PreguntaResource")
     *   ),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Sección no encontrada")
     * )
     */
    public function store(StorePreguntaRequest $request, SeccionEncuesta $seccion): JsonResponse
    {
        // 1) Autorización ya validada en StorePreguntaRequest::authorize()
        // 2) Recoger datos limpios y validados
        $datosValidados = $request->validated();

        // 3) Crear la pregunta delegando al Servicio
        $pregunta = $this->service->crear($seccion, $datosValidados);

        // 4) Responder con recurso y 201 Created
        return (new PreguntaResource($pregunta))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *   path="/api/encuestas/{encuesta}/secciones/{seccionEncuesta}/preguntas/{pregunta}",
     *   operationId="showPregunta",
     *   tags={"Preguntas"},
     *   summary="Mostrar una pregunta",
     *   description="Devuelve los datos de una pregunta específica.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="encuesta",
     *     in="path",
     *     description="ID de la encuesta",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Parameter(
     *     name="seccionEncuesta",
     *     in="path",
     *     description="ID de la sección",
     *     required=true,
     *     @OA\Schema(type="integer", example=5)
     *   ),
     *   @OA\Parameter(
     *     name="pregunta",
     *     in="path",
     *     description="ID de la pregunta",
     *     required=true,
     *     @OA\Schema(type="integer", example=12)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Pregunta encontrada",
     *     @OA\JsonContent(ref="#/components/schemas/PreguntaResource")
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Pregunta no encontrada")
     * )
     */
    public function show(SeccionEncuesta $seccionEncuesta, Pregunta $pregunta): PreguntaResource
    {
        $this->authorize('view', $pregunta); // 1) Autoriza vía PreguntaPolicy::view(User, Pregunta)

        // 2) Cargar relaciones que necesite el Resource
        $pregunta->load([
            'tipoPregunta:id_tipo_pregunta,nombre_tipo',
            'opcionesPregunta' => fn($q) => $q->orderBy('orden'),
            'preguntaPadre:id_pregunta,texto_pregunta,orden',
            'opcionCondicionPadre:id_opcion_pregunta,texto_opcion',
        ]);

        return new PreguntaResource($pregunta);
    }

    /**
     * @OA\Put(
     *   path="/api/encuestas/{encuesta}/secciones/{seccionEncuesta}/preguntas/{pregunta}",
     *   operationId="updatePregunta",
     *   tags={"Preguntas"},
     *   summary="Actualizar una pregunta",
     *   description="Modifica los datos de la pregunta especificada.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="encuesta",
     *     in="path",
     *     description="ID de la encuesta",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Parameter(
     *     name="seccionEncuesta",
     *     in="path",
     *     description="ID de la sección",
     *     required=true,
     *     @OA\Schema(type="integer", example=5)
     *   ),
     *   @OA\Parameter(
     *     name="pregunta",
     *     in="path",
     *     description="ID de la pregunta",
     *     required=true,
     *     @OA\Schema(type="integer", example=12)
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/UpdatePreguntaRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Pregunta actualizada",
     *     @OA\JsonContent(ref="#/components/schemas/PreguntaResource")
     *   ),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Pregunta no encontrada")
     * )
     */
    public function update(UpdatePreguntaRequest $request, SeccionEncuesta $seccionEncuesta, Pregunta $pregunta): PreguntaResource
    {
        // 1) UpdatePreguntaRequest::authorize() ya llamó a PreguntaPolicy::update()
        //    y Laravel, gracias al nested binding, garantiza que el $pregunta
        //    pertenece a la misma $seccionEncuesta (o habría 404).
        $datosValidados = $request->validated(); // 2) Obtener datos validados (campo a campo ya definidos en FormRequest)
        $preguntaActualizada = $this->service->actualizar($pregunta, $datosValidados); // 3) Delegar la actualización al Service
        // 4) Recargar relaciones para la respuesta final
        $preguntaActualizada->load([
            'tipoPregunta:id_tipo_pregunta,nombre_tipo',
            'opcionesPregunta' => fn($q) => $q->orderBy('orden'),
            'preguntaPadre:id_pregunta,texto_pregunta,orden',
            'opcionCondicionPadre:id_opcion_pregunta,texto_opcion',
        ]);
        return new PreguntaResource($preguntaActualizada);
    }

    /**
     * @OA\Delete(
     *   path="/api/encuestas/{encuesta}/secciones/{seccionEncuesta}/preguntas/{pregunta}",
     *   operationId="destroyPregunta",
     *   tags={"Preguntas"},
     *   summary="Eliminar pregunta",
     *   description="Realiza un soft delete y reordena las demás preguntas dentro de la sección.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="encuesta",
     *     in="path",
     *     description="ID de la encuesta",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Parameter(
     *     name="seccionEncuesta",
     *     in="path",
     *     description="ID de la sección",
     *     required=true,
     *     @OA\Schema(type="integer", example=5)
     *   ),
     *   @OA\Parameter(
     *     name="pregunta",
     *     in="path",
     *     description="ID de la pregunta",
     *     required=true,
     *     @OA\Schema(type="integer", example=12)
     *   ),
     *   @OA\Response(response=204, description="Pregunta eliminada"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Pregunta no encontrada")
     * )
     */
    public function destroy(SeccionEncuesta $seccion, Pregunta $pregunta): JsonResponse
    {
        $this->authorize('delete', $pregunta); // 1) Autoriza vía PreguntaPolicy::delete(User, Pregunta)
        $this->service->eliminar($pregunta); // 2) Delegar la eliminación (soft delete + reordenar) al Service
        return response()->json(null, 204); // 3) Devolver 204 No Content
    }

    /**
     * @OA\Post(
     *   path="/api/encuestas/{encuesta}/secciones/{seccionEncuesta}/preguntas/{pregunta}/reordenar",
     *   operationId="reordenarPregunta",
     *   tags={"Preguntas"},
     *   summary="Reordenar pregunta dentro de una sección",
     *   description="Mueve la pregunta a la posición dada, reordenando las demás preguntas.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="encuesta",
     *     in="path",
     *     description="ID de la encuesta",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Parameter(
     *     name="seccionEncuesta",
     *     in="path",
     *     description="ID de la sección",
     *     required=true,
     *     @OA\Schema(type="integer", example=5)
     *   ),
     *   @OA\Parameter(
     *     name="pregunta",
     *     in="path",
     *     description="ID de la pregunta",
     *     required=true,
     *     @OA\Schema(type="integer", example=12)
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="nuevo_orden", type="integer", example=2)
     *     )
     *   ),
     *   @OA\Response(response=200, description="Pregunta reordenada exitosamente"),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Pregunta no encontrada")
     * )
     */
    public function reordenar(ReordenarPreguntaRequest $request, SeccionEncuesta $seccionEncuesta, Pregunta $pregunta): JsonResponse
    {
        // 1) Comprobar que la pregunta pertenece a la sección
        if ($pregunta->id_seccion !== $seccionEncuesta->id_seccion) abort(404, 'Pregunta no encontrada en esta sección.');

        $nuevoOrden = $request->validated()['nuevo_orden']; // 2) El FormRequest ya validó "nuevo_orden" dentro de su rango

        $this->service->reordenar($pregunta, $nuevoOrden); // 3) Llamar al servicio para reordenar internamente en BD

        // 4) Devolver la lista actualizada de preguntas en esa sección
        $preguntasActualizadas = $this->service->obtenerPorSeccion($seccionEncuesta->id_seccion);
        return response()->json(PreguntaResource::collection($preguntasActualizadas), 200);
    }
}
