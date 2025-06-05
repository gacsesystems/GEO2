<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pregunta;
use App\Models\OpcionPregunta;
use App\Services\OpcionPreguntaService;
use App\Http\Requests\StoreOpcionPreguntaRequest;
use App\Http\Requests\UpdateOpcionPreguntaRequest;
use App\Http\Requests\ReordenarOpcionPreguntaRequest;
use App\Http\Resources\OpcionPreguntaResource;
use App\Http\Requests\StoreBulkOpcionPreguntaRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *   name="OpcionesPregunta",
 *   description="CRUD y reordenamiento de Opciones dentro de una Pregunta"
 * )
 */
class OpcionPreguntaController extends Controller
{
    public function __construct(private OpcionPreguntaService $service)
    {
        $this->middleware('auth:sanctum');

        // Policies:
        // - viewAny: listar todas las opciones de la pregunta (solo si puede update en Pregunta).
        $this->middleware('can:viewAny,pregunta')->only('index');

        // - create: solo si puede create en Pregunta.
        $this->middleware('can:create,App\Models\OpcionPregunta,pregunta')->only('store');

        // - view: solo si puede view en OpcionPregunta.
        $this->middleware('can:view,opcionPregunta')->only('show');

        // - update: solo si puede update en OpcionPregunta.
        $this->middleware('can:update,opcionPregunta')->only('update');

        // - delete: solo si puede delete en OpcionPregunta.
        $this->middleware('can:delete,opcionPregunta')->only('destroy');

        // - reordenar: solo si puede reordenar en OpcionPregunta.
        $this->middleware('can:reordenar,opcionPregunta')->only('reordenar');
    }

    protected function autorizarAccionPregunta(Pregunta $pregunta): void
    {
        $user = Auth::user();
        $encuesta = $pregunta->seccionEncuesta->encuesta;
        if (!$user->esRol('administrador') && ($encuesta->id_cliente !== $user->id_cliente)) {
            abort(403, 'No autorizado para modificar las opciones de esta pregunta.');
        }
        if (!$pregunta->tipoPregunta?->requiere_opciones) {
            abort(422, "Este tipo de pregunta ('{$pregunta->tipoPregunta?->nombre}') no permite opciones.");
        }
    }

    /**
     * @OA\Get(
     *   path="/api/encuestas/{encuesta}/secciones/{seccionEncuesta}/preguntas/{pregunta}/opciones",
     *   operationId="indexOpcionesPregunta",
     *   tags={"OpcionesPregunta"},
     *   summary="Listar opciones de una pregunta",
     *   description="Devuelve todas las opciones pertenecientes a la pregunta indicada.",
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
     *     description="Lista de opciones",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/OpcionPreguntaResource")
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Pregunta no encontrada")
     * )
     */
    public function index(Pregunta $pregunta): AnonymousResourceCollection
    {
        $this->authorize('viewAny', $pregunta); // 1) Lanza 403 si PreguntaPolicy::viewAny($user, $pregunta) devuelve false

        $opciones = $this->service->obtenerPorPregunta($pregunta->id_pregunta); // 2) Obtener colección de opciones ordenadas (el Service hace el where por pregunta)

        return OpcionPreguntaResource::collection($opciones); // 3) Devolver recurso en formato JSON (200 OK implícito)
    }

    /**
     * @OA\Post(
     *   path="/api/encuestas/{encuesta}/secciones/{seccionEncuesta}/preguntas/{pregunta}/opciones",
     *   operationId="storeOpcionPregunta",
     *   tags={"OpcionesPregunta"},
     *   summary="Crear opción para una pregunta",
     *   description="Añade una nueva opción a la pregunta indicada.",
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
     *     @OA\JsonContent(ref="#/components/schemas/StoreOpcionPreguntaRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Opción creada",
     *     @OA\JsonContent(ref="#/components/schemas/OpcionPreguntaResource")
     *   ),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Pregunta no encontrada")
     * )
     */
    public function store(StoreOpcionPreguntaRequest $request, Pregunta $pregunta): JsonResponse
    {
        // 1) StoreOpcionPreguntaRequest::authorize() ya invoca OpcionPreguntaPolicy::create()
        $datos = $request->validated();

        // 2) Crear la opción vía Service
        $opcion = $this->service->crear($pregunta, $datos);

        // 3) Responder con recurso y 201 Created
        return (new OpcionPreguntaResource($opcion))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *   path="/api/encuestas/{encuesta}/secciones/{seccionEncuesta}/preguntas/{pregunta}/opciones/{opcionPregunta}",
     *   operationId="showOpcionPregunta",
     *   tags={"OpcionesPregunta"},
     *   summary="Obtener una opción específica",
     *   description="Devuelve datos de la opción indicada.",
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
     *   @OA\Parameter(
     *     name="opcionPregunta",
     *     in="path",
     *     description="ID de la opción",
     *     required=true,
     *     @OA\Schema(type="integer", example=3)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Opción encontrada",
     *     @OA\JsonContent(ref="#/components/schemas/OpcionPreguntaResource")
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Opción no encontrada")
     * )
     */
    public function show(Pregunta $pregunta, OpcionPregunta $opcionPregunta): OpcionPreguntaResource
    {
        // 1) Route Model Binding anidado ya se aseguró de que $opcionPregunta->id_pregunta == $pregunta->id_pregunta.
        //    Si no coincide, Laravel devuelve 404 antes de llegar aquí.

        // 2) Autoriza mediante Policy: OpcionPreguntaPolicy::view(User, OpcionPregunta)
        $this->authorize('view', $opcionPregunta);

        // 3) Devolver el Resource
        return new OpcionPreguntaResource($opcionPregunta);
    }

    /**
     * @OA\Put(
     *   path="/api/encuestas/{encuesta}/secciones/{seccionEncuesta}/preguntas/{pregunta}/opciones/{opcionPregunta}",
     *   operationId="updateOpcionPregunta",
     *   tags={"OpcionesPregunta"},
     *   summary="Actualizar opción de pregunta",
     *   description="Modifica el texto, valor u orden de la opción indicada.",
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
     *   @OA\Parameter(
     *     name="opcionPregunta",
     *     in="path",
     *     description="ID de la opción",
     *     required=true,
     *     @OA\Schema(type="integer", example=3)
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/UpdateOpcionPreguntaRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Opción actualizada",
     *     @OA\JsonContent(ref="#/components/schemas/OpcionPreguntaResource")
     *   ),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Opción no encontrada")
     * )
     */
    public function update(UpdateOpcionPreguntaRequest $request, Pregunta $pregunta, OpcionPregunta $opcion): JsonResponse
    {
        // 1) Route Model Binding asegura correspondencia pregunta⇄opción; de lo contrario 404.
        // 2) UpdateOpcionPreguntaRequest::authorize() invoca OpcionPreguntaPolicy::update(User, OpcionPregunta).
        //    Si la política falla, Laravel devuelve 403 automáticamente.
        $datosValidados = $request->validated(); // 3) Validar y obtener datos limpios

        // 4) Delegar la actualización al Service
        $opcionActualizada = $this->service->actualizar($opcion, $datosValidados);

        // 5) Devolver recurso con 200 OK
        return (new OpcionPreguntaResource($opcionActualizada))->response()->setStatusCode(200);
    }

    /**
     * @OA\Delete(
     *   path="/api/encuestas/{encuesta}/secciones/{seccionEncuesta}/preguntas/{pregunta}/opciones/{opcionPregunta}",
     *   operationId="destroyOpcionPregunta",
     *   tags={"OpcionesPregunta"},
     *   summary="Eliminar opción de pregunta",
     *   description="Realiza un soft delete y reordena las demás opciones.",
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
     *   @OA\Parameter(
     *     name="opcionPregunta",
     *     in="path",
     *     description="ID de la opción",
     *     required=true,
     *     @OA\Schema(type="integer", example=3)
     *   ),
     *   @OA\Response(response=204, description="Opción eliminada"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Opción no encontrada")
     * )
     */
    public function destroy(Pregunta $pregunta, OpcionPregunta $opcion): JsonResponse
    {
        // 1) Route Model Binding anidado: si no coincide la relación, Laravel arroja 404.
        // 2) Autoriza con Policy: OpcionPreguntaPolicy::delete(User, OpcionPregunta).
        $this->authorize('delete', $opcion);

        // 3) Delegar la eliminación al Service
        $this->service->eliminar($opcion);

        // 4) 204 No Content
        return response()->json(null, 204);
    }

    /**
     * @OA\Post(
     *   path="/api/opciones/{opcionPregunta}/reordenar",
     *   operationId="reordenarOpcionPregunta",
     *   tags={"OpcionesPregunta"},
     *   summary="Reordenar opción de pregunta",
     *   description="Mueve la opción a la posición dada dentro de la pregunta.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="opcionPregunta",
     *     in="path",
     *     description="ID de la opción",
     *     required=true,
     *     @OA\Schema(type="integer", example=3)
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="nuevo_orden", type="integer", example=2)
     *     )
     *   ),
     *   @OA\Response(response=200, description="Opción reordenada exitosamente"),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Opción no encontrada")
     * )
     */
    public function reordenar(ReordenarOpcionPreguntaRequest $request, Pregunta $pregunta, OpcionPregunta $opcion): JsonResponse
    {
        // 1) El FormRequest ya autorizó vía OpcionPreguntaPolicy::reordenar($user, $opcion)
        //    y Laravel, gracias al binding anidado, aseguró que $opcion->id_pregunta == $pregunta->id_pregunta.
        //    Si no coinciden, Laravel devuelve 404 automáticamente.

        // 2) Obtener el nuevo orden validado
        $nuevoOrden = $request->validated()['nuevo_orden'];

        // 3) Delegar la lógica al Service
        $this->service->reordenar($opcion, $nuevoOrden);

        // 4) Recuperar el listado actualizado de opciones (ordenadas) para esta pregunta
        $opcionesActualizadas = $this->service->obtenerPorPregunta($pregunta->id_pregunta);

        // 5) Devolver la colección, 200 OK por defecto
        return response()->json(OpcionPreguntaResource::collection($opcionesActualizadas), 200);
    }

    /**
     * @OA\Post(
     *   path="/api/encuestas/{encuesta}/secciones/{seccionEncuesta}/preguntas/{pregunta}/opciones/bulk",
     *   operationId="storeBulkOpcionesPregunta",
     *   tags={"OpcionesPregunta"},
     *   summary="Crear múltiples opciones de una vez",
     *   description="Añade varias opciones en bloque para la pregunta indicada.",
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
     *     @OA\JsonContent(ref="#/components/schemas/StoreBulkOpcionPreguntaRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Opciones creadas exitosamente",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/OpcionPreguntaResource")
     *     )
     *   ),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Pregunta no encontrada")
     * )
     */
    public function storeBulk(StoreBulkOpcionPreguntaRequest $request, Pregunta $pregunta): JsonResponse
    {
        // 1) Opcional: verificar que la pregunta pertenezca a la sección y a la encuesta CORRECTOS.
        //    / encuestas/{encuesta}/secciones/{seccionEncuesta}/preguntas/{pregunta} 
        //    Si usas route-model-binding, Laravel ya inyecta el Pregunta correcto VINCULADO A {pregunta}.

        // 2) Autorización: que el usuario pueda crear opciones en esta pregunta
        $this->authorize('create', [\App\Models\OpcionPregunta::class, $pregunta]);

        // 3) Llamar al service:
        $datosValidos = $request->validated()['opciones'];
        $coleccionCreada = $this->service->crearMultiples($pregunta, $datosValidos);

        // 4) Devolver JSON con los recursos recién creados
        return response()->json([
            'data' => OpcionPreguntaResource::collection($coleccionCreada)
        ], 201);
    }
}
