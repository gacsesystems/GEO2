<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TipoPregunta;
use App\Http\Resources\TipoPreguntaResource;
use App\Services\TipoPreguntaService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreTipoPreguntaRequest;
use App\Http\Requests\UpdateTipoPreguntaRequest;

/**
 * @OA\Tag(
 *   name="TiposPregunta",
 *   description="Gestión de tipos de pregunta (catálogo)"
 * )
 */
class TipoPreguntaController extends Controller
{
    public function __construct(private TipoPreguntaService $service)
    {
        $this->middleware('auth:sanctum');
        // Por ejemplo, sólo Administradores pueden CRUD sobre tipos:
        $this->middleware('can:viewAny,App\Models\TipoPregunta')->only('index');
        $this->middleware('can:create,App\Models\TipoPregunta')->only('store');
        $this->middleware('can:view,App\Models\TipoPregunta')->only('show');
        $this->middleware('can:update,TipoPregunta')->only('update');
        $this->middleware('can:delete,TipoPregunta')->only('destroy');
    }

    /**
     * @OA\Get(
     *   path="/api/tipo-preguntas",
     *   operationId="getTipoPreguntasList",
     *   tags={"TiposPregunta"},
     *   summary="Listar todos los tipos de pregunta",
     *   description="Devuelve el catálogo completo de tipos de pregunta.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200,
     *     description="Lista de tipos de pregunta",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/TipoPreguntaResource")
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso")
     * )
     */
    public function index(): JsonResponse
    {
        $coleccion = $this->service->all();
        return response()->json(['data' => TipoPreguntaResource::collection($coleccion)], 200);
    }

    /**
     * @OA\Post(
     *   path="/api/tipo-preguntas",
     *   operationId="storeTipoPregunta",
     *   tags={"TiposPregunta"},
     *   summary="Crear un nuevo tipo de pregunta",
     *   description="Añade un tipo al catálogo de tipos de pregunta.",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/StoreTipoPreguntaRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Tipo de pregunta creado",
     *     @OA\JsonContent(ref="#/components/schemas/TipoPreguntaResource")
     *   ),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso")
     * )
     */
    public function store(StoreTipoPreguntaRequest $request): JsonResponse
    {
        $datos = $request->validated();
        $nuevo = $this->service->create($datos);

        return response()->json(['data' => new TipoPreguntaResource($nuevo)], 201);
    }

    /**
     * @OA\Get(
     *   path="/api/tipo-preguntas/{tipoPregunta}",
     *   operationId="showTipoPregunta",
     *   tags={"TiposPregunta"},
     *   summary="Obtener un tipo de pregunta",
     *   description="Devuelve los datos de un tipo de pregunta específico.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="tipoPregunta",
     *     in="path",
     *     description="ID del tipo de pregunta",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Tipo de pregunta encontrado",
     *     @OA\JsonContent(ref="#/components/schemas/TipoPreguntaResource")
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="TipoPregunta no encontrado")
     * )
     */
    public function show(TipoPregunta $tipoPregunta): JsonResponse
    {
        return response()->json(['data' => new TipoPreguntaResource($tipoPregunta)], 200);
    }

    /**
     * @OA\Put(
     *   path="/api/tipo-preguntas/{tipoPregunta}",
     *   operationId="updateTipoPregunta",
     *   tags={"TiposPregunta"},
     *   summary="Actualizar tipo de pregunta",
     *   description="Modifica el nombre, descripción o banderas del tipo de pregunta.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="tipoPregunta",
     *     in="path",
     *     description="ID del tipo de pregunta",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/UpdateTipoPreguntaRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Tipo de pregunta actualizado",
     *     @OA\JsonContent(ref="#/components/schemas/TipoPreguntaResource")
     *   ),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="TipoPregunta no encontrado")
     * )
     */
    public function update(UpdateTipoPreguntaRequest $request, TipoPregunta $tipoPregunta): JsonResponse
    {
        $datos = $request->validated();
        $actualizado = $this->service->update($tipoPregunta, $datos);

        return response()->json(['data' => new TipoPreguntaResource($actualizado)], 200);
    }

    /**
     * @OA\Delete(
     *   path="/api/tipo-preguntas/{tipoPregunta}",
     *   operationId="destroyTipoPregunta",
     *   tags={"TiposPregunta"},
     *   summary="Eliminar tipo de pregunta",
     *   description="Realiza un hard delete o soft delete del tipo (según tu migración).",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="tipoPregunta",
     *     in="path",
     *     description="ID del tipo de pregunta",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Response(response=204, description="Tipo de pregunta eliminado"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="TipoPregunta no encontrado")
     * )
     */
    public function destroy(TipoPregunta $tipoPregunta): JsonResponse
    {
        $this->service->delete($tipoPregunta);
        return response()->json(null, 204);
    }
}
