<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Encuesta;
use App\Models\SeccionEncuesta;
use App\Services\SeccionEncuestaService;
use App\Http\Requests\StoreSeccionEncuestaRequest;
use App\Http\Requests\UpdateSeccionEncuestaRequest;
use App\Http\Requests\ReordenarSeccionEncuestaRequest;
use App\Http\Resources\SeccionEncuestaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Tag(
 *   name="SeccionesEncuesta",
 *   description="CRUD y reordenamiento de Secciones dentro de una Encuesta"
 * )
 */
class SeccionEncuestaController extends Controller
{
    public function __construct(private SeccionEncuestaService $service)
    {
        // Las rutas con excepción de index y publica (no hay pública para secciones)
        $this->middleware('auth:sanctum');

        // Verifica permisos según políticas
        $this->middleware('can:viewAny,App\Models\Encuesta')->only('index');
        $this->middleware('can:create,App\Models\Encuesta')->only('store');
        $this->middleware('can:view,seccionEncuesta')->only('show');
        $this->middleware('can:update,seccionEncuesta')->only('update');
        $this->middleware('can:delete,seccionEncuesta')->only('destroy');
        $this->middleware('can:reordenar,seccionEncuesta')->only('reordenar');
    }

    /**
     * @OA\Get(
     *   path="/api/encuestas/{encuesta}/secciones",
     *   operationId="indexSeccionesEncuesta",
     *   tags={"SeccionesEncuesta"},
     *   summary="Listar secciones de una encuesta",
     *   description="Devuelve todas las secciones pertenecientes a una encuesta.",
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
     *     description="Lista de secciones",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/SeccionEncuestaResource")
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Encuesta no encontrada")
     * )
     */
    public function index(Encuesta $encuesta): AnonymousResourceCollection
    {
        // 1) Autoriza: se asume que SeccionEncuestaPolicy@viewAny(User $user, Encuesta $encuesta)
        $this->authorize('viewAny', [SeccionEncuesta::class, $encuesta]);

        // 2) Obtiene secciones desde el servicio (por encuesta ya validada)
        $secciones = $this->service->obtenerPorEncuesta($encuesta->id_encuesta);

        return SeccionEncuestaResource::collection($secciones); // 3) Devuelve la colección de recursos
    }

    /**
     * @OA\Post(
     *   path="/api/encuestas/{encuesta}/secciones",
     *   operationId="storeSeccionEncuesta",
     *   tags={"SeccionesEncuesta"},
     *   summary="Crear sección en una encuesta",
     *   description="Añade una nueva sección a la encuesta indicada.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="encuesta",
     *     in="path",
     *     description="ID de la encuesta",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/StoreSeccionEncuestaRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Sección creada",
     *     @OA\JsonContent(ref="#/components/schemas/SeccionEncuestaResource")
     *   ),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Encuesta no encontrada")
     * )
     */
    public function store(StoreSeccionEncuestaRequest $request, Encuesta $encuesta): JsonResponse
    {
        // 1) $request->authorize() ya verificó can:create en SeccionEncuestaPolicy
        // 2) $request->validated() ya contiene ['nombre', 'descripcion'] correctamente filtrado
        $data = $request->validated();

        $seccion = $this->service->crear($encuesta, $data); // 3) Delegar la creación al Servicio, pasándole el modelo Encuesta y el array validad­o

        // 4) Devolver el Resource con código 201
        return (new SeccionEncuestaResource($seccion))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *   path="/api/encuestas/{encuesta}/secciones/{seccionEncuesta}",
     *   operationId="showSeccionEncuesta",
     *   tags={"SeccionesEncuesta"},
     *   summary="Mostrar una sección de encuesta",
     *   description="Devuelve datos de una sección específica de la encuesta.",
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
     *     description="Sección encontrada",
     *     @OA\JsonContent(ref="#/components/schemas/SeccionEncuestaResource")
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Sección no encontrada")
     * )
     */
    public function show(Encuesta $encuesta, SeccionEncuesta $seccion): SeccionEncuestaResource // Route model binding
    {
        $this->authorize('view', $seccion); // 1) authorize() invoca SeccionEncuestaPolicy::view($user, $seccion)

        $seccion->load('preguntas'); // 2) Cargamos las preguntas (u otras relaciones) que necesite el Resource

        return new SeccionEncuestaResource($seccion); // 3) Devolvemos el Resource
    }

    /**
     * @OA\Put(
     *   path="/api/encuestas/{encuesta}/secciones/{seccionEncuesta}",
     *   operationId="updateSeccionEncuesta",
     *   tags={"SeccionesEncuesta"},
     *   summary="Actualizar sección de encuesta",
     *   description="Modifica los datos de la sección indicada.",
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
     *     @OA\JsonContent(ref="#/components/schemas/UpdateSeccionEncuestaRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Sección actualizada",
     *     @OA\JsonContent(ref="#/components/schemas/SeccionEncuestaResource")
     *   ),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Sección no encontrada")
     * )
     */
    public function update(UpdateSeccionEncuestaRequest $request, Encuesta $encuesta, SeccionEncuesta $seccion): SeccionEncuestaResource
    {
        // 1) UpdateSeccionEncuestaRequest::authorize() ya llamó a SeccionEncuestaPolicy::update($user, $seccion) y se aseguró de que el usuario tenga permiso para esta sección.

        // 2) Los datos ya vienen validados en $request->validated()
        $data = $request->validated();

        // 3) Delegar al Servicio (aquí no pasamos el ID, sino el propio modelo)
        $actualizada = $this->service->actualizar($seccion, $data);

        // 4) Devolver el recurso formateado
        return new SeccionEncuestaResource($actualizada);
    }

    /**
     * @OA\Delete(
     *   path="/api/encuestas/{encuesta}/secciones/{seccionEncuesta}",
     *   operationId="destroySeccionEncuesta",
     *   tags={"SeccionesEncuesta"},
     *   summary="Eliminar sección de encuesta",
     *   description="Realiza un soft delete y reordena las demás secciones.",
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
     *   @OA\Response(response=204, description="Sección eliminada"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Sección no encontrada")
     * )
     */
    public function destroy(Encuesta $encuesta, SeccionEncuesta $seccion): JsonResponse
    {
        $this->authorize('delete', $seccion); // 1) Autoriza vía Policy (SeccionEncuestaPolicy::delete)

        $this->service->eliminar($seccion); // 2) Llamar al Service para eliminar (soft delete)

        return response()->json(null, 204); // 3) 204 No Content
    }

    /**
     * @OA\Post(
     *   path="/api/encuestas/{encuesta}/secciones/{seccionEncuesta}/reordenar/{nuevoOrden}",
     *   operationId="reordenarSeccionEncuesta",
     *   tags={"SeccionesEncuesta"},
     *   summary="Reordenar sección dentro de la encuesta",
     *   description="Mueve la sección a la posición indicada, reordenando las demás.",
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
     *     name="nuevoOrden",
     *     in="path",
     *     description="Nuevo índice de orden (1-based)",
     *     required=true,
     *     @OA\Schema(type="integer", example=2)
     *   ),
     *   @OA\Response(response=200, description="Sección reordenada exitosamente"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Sección no encontrada")
     * )
     */
    public function reordenar(
        ReordenarSeccionEncuestaRequest $request,
        Encuesta $encuesta,
        SeccionEncuesta $seccion
    ): AnonymousResourceCollection {
        // 1) El FormRequest ya llamó a $this->authorize('reordenar', $seccion)
        //    y Laravel solo inyectó $seccion si pertenece a $encuesta (binding anidado).
        $nuevoOrden = $request->validated()['nuevo_orden']; // 2) Obtiene el nuevo orden validado
        $this->service->reordenar($seccion, $nuevoOrden); // 3) Llama al servicio para cambiar el orden

        // 4) Retorna la lista actualizada de secciones, ya formateada con Resource
        $seccionesActualizadas = $this->service->obtenerPorEncuesta($encuesta->id_encuesta);
        return SeccionEncuestaResource::collection($seccionesActualizadas);
    }
}
