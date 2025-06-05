<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Encuesta;
use App\Models\Cliente; // Para obtener encuestas de un cliente
use App\Services\EncuestaService;
use App\Http\Requests\StoreEncuestaRequest;
use App\Http\Requests\UpdateEncuestaRequest;
use App\Http\Resources\EncuestaResource;
use App\Http\Resources\EncuestaDetalleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt; // Si usamos URLs cifradas
use Illuminate\Contracts\Encryption\DecryptException; // Para manejar errores de descifrado

/**
 * @OA\Tag(
 *   name="Encuestas",
 *   description="CRUD y endpoints adicionales para Encuestas"
 * )
 */
class EncuestaController extends Controller
{
    protected EncuestaService $encuestaService;

    public function __construct(private EncuestaService $service)
    {
        // 1) Protegemos todo con Sanctum
        $this->middleware('auth:sanctum');

        // 2) Middleware can: para verificar permisos antes de cada acción
        $this->middleware('can:viewAny,App\Models\Encuesta')->only('index', 'porCliente');
        $this->middleware('can:create,App\Models\Encuesta')->only('store');
        $this->middleware('can:view,encuesta')->only('show', 'detalleCompleto', 'generarUrlCifrada');
        $this->middleware('can:update,encuesta')->only('update');
        $this->middleware('can:delete,encuesta')->only('destroy');
    }

    /**
     * @OA\Get(
     *   path="/api/encuestas",
     *   operationId="getEncuestasList",
     *   tags={"Encuestas"},
     *   summary="Listar todas las encuestas",
     *   description="Devuelve una lista de encuestas (paginadas o no).",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200,
     *     description="Lista de encuestas",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/EncuestaResource")
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso")
     * )
     */
    public function index(): AnonymousResourceCollection|JsonResponse
    {
        $coleccion = $this->service->obtenerTodas(); // Si llegó hasta aquí, la policy “viewAny” ya autorizó.

        return EncuestaResource::collection($coleccion); // Devolver paginación con Resource
    }

    /**
     * Muestra las encuestas de un cliente específico (solo para Admins).
     */
    public function porCliente(Cliente $cliente): AnonymousResourceCollection|JsonResponse
    {
        // La política viewAny ya permitió el acceso solo a Administradores
        $encuestas = $this->encuestaService->obtenerPorId($cliente->id_cliente);
        return EncuestaResource::collection($encuestas);
    }

    /**
     * @OA\Post(
     *   path="/api/encuestas",
     *   operationId="storeEncuesta",
     *   tags={"Encuestas"},
     *   summary="Crear una nueva encuesta",
     *   description="Crea una encuesta para el cliente autenticado (o, si es admin, puede especificar id_cliente).",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/StoreEncuestaRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Encuesta creada",
     *     @OA\JsonContent(ref="#/components/schemas/EncuestaResource")
     *   ),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso")
     * )
     */
    public function store(StoreEncuestaRequest $request): JsonResponse
    {
        // 1) $request->authorize() ya validó rol y límite de encuestas, y
        //    $request->validated() trae nombre, descripcion y (si es Admin) id_cliente.
        $data = $request->validated();
        $user = $request->user();

        if ($user->esRol('Cliente')) $data['id_cliente'] = $user->id_cliente; // 2) Si viene un Cliente normal, ignorar cualquier 'id_cliente' que haya en el request

        $encuesta = $this->service->crear($data, $user->id_cliente); // 3) Crear la encuesta con el Servicio

        // 4) Devolver el recurso con status 201
        return (new EncuestaResource($encuesta->load('cliente')))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *   path="/api/encuestas/{encuesta}",
     *   operationId="showEncuesta",
     *   tags={"Encuestas"},
     *   summary="Mostrar encuesta",
     *   description="Devuelve datos de una encuesta específica.",
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
     *     description="Encuesta encontrada",
     *     @OA\JsonContent(ref="#/components/schemas/EncuestaResource")
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Encuesta no encontrada")
     * )
     */
    public function show(Encuesta $encuesta): EncuestaResource|JsonResponse // Route-Model Binding
    {
        $this->authorize('view', $encuesta); // Lanza 403 si la Policy::view($user, $encuesta) devuelve false
        $encuesta->load('cliente'); // Optativo: si necesitas cargar el cliente para el Resource

        return new EncuestaResource($encuesta);
    }

    /**
     * @OA\Get(
     *   path="/api/encuestas/{encuesta}/detalle-completo",
     *   operationId="detalleCompletoEncuesta",
     *   tags={"Encuestas"},
     *   summary="Detalle completo de encuesta",
     *   description="Devuelve encuesta con secciones, preguntas y opciones anidadas.",
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
     *     description="Encuesta con detalle completo",
     *     @OA\JsonContent(ref="#/components/schemas/EncuestaDetalleResource")
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Encuesta no encontrada")
     * )
     */
    public function detalleCompleto(Encuesta $encuesta): EncuestaDetalleResource|JsonResponse
    {
        $user = Auth::user();
        // Solo el propietario de la encuesta (cliente) o un administrador pueden ver el detalle completo.
        // Para responder, se usará un endpoint público sin autenticación.
        // Política “view” ya validó Admin vs. propietario, pero volvemos a checar cliente
        if (! $user->esRol('Administrador') && $user->id_cliente !== $encuesta->idCliente) {
            return response()->json(['message' => 'No autorizado para ver el detalle de esta encuesta.'], 403);
        }

        $detalle = $this->encuestaService->obtenerDetalleCompleto($encuesta->id_encuesta);
        if (! $detalle) return response()->json(['message' => 'Encuesta no encontrada.'], 404);

        return new EncuestaDetalleResource($detalle);
    }

    /**
     * @OA\Put(
     *   path="/api/encuestas/{encuesta}",
     *   operationId="updateEncuesta",
     *   tags={"Encuestas"},
     *   summary="Actualizar una encuesta",
     *   description="Actualiza los datos básicos de la encuesta.",
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
     *     @OA\JsonContent(ref="#/components/schemas/UpdateEncuestaRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Encuesta actualizada",
     *     @OA\JsonContent(ref="#/components/schemas/EncuestaResource")
     *   ),
     *   @OA\Response(response=400, description="Datos inválidos"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Encuesta no encontrada")
     * )
     */
    public function update(UpdateEncuestaRequest $request, Encuesta $encuesta): EncuestaResource
    {
        $data = $request->validated(); // 1) El FormRequest ya autorizó con can:update y validó nombre/descripcion

        // 2) Delegar al servicio pasando el modelo, no el ID
        $encuestaActualizada = $this->service->actualizar($encuesta, [
            'nombre'      => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
        ]);

        // 3) (Opcional) cargar el cliente para que el Resource tenga esa relación
        $encuestaActualizada->load('cliente');

        // 4) Devolver el Resource. Laravel asume status 200.
        return new EncuestaResource($encuestaActualizada);
    }

    /**
     * @OA\Delete(
     *   path="/api/encuestas/{encuesta}",
     *   operationId="destroyEncuesta",
     *   tags={"Encuestas"},
     *   summary="Eliminar encuesta",
     *   description="Realiza un soft delete sobre la encuesta.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="encuesta",
     *     in="path",
     *     description="ID de la encuesta",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Response(response=204, description="Encuesta eliminada"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Encuesta no encontrada")
     * )
     */
    public function destroy(Encuesta $encuesta): JsonResponse
    {
        $this->authorize('delete', $encuesta); // 1) Autorizar vía Policy (EncuestaPolicy::delete)
        $this->encuestaService->eliminar($encuesta); // 2) Delegar al Service (El Service usa $encuesta->delete())
        return response()->json(null, 204); // 3) Devolver 204 No Content
    }

    /**
     * @OA\Get(
     *   path="/api/encuestas/publica/{idEncuesta}",
     *   operationId="publicaEncuesta",
     *   tags={"Encuestas"},
     *   summary="Obtener encuesta públicamente",
     *   description="Devuelve la estructura completa de la encuesta sin requerir autenticación.",
     *   @OA\Parameter(
     *     name="idEncuesta",
     *     in="path",
     *     description="ID de la encuesta",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Encuesta pública encontrada",
     *     @OA\JsonContent(ref="#/components/schemas/EncuestaDetalleResource")
     *   ),
     *   @OA\Response(response=404, description="Encuesta no encontrada o no disponible")
     * )
     */
    public function publica(int $idEncuesta) // No hay Type Hinting de Encuesta aquí para evitar middleware de auth implícito
    {
        $encuesta = $this->encuestaService->obtenerDetalleCompleto($idEncuesta);

        if (!$encuesta || !$encuesta->cliente?->activo) { // También chequear si el cliente de la encuesta está activo
            return response()->json(['message' => 'Encuesta no disponible o no encontrada.'], 404);
        }

        // Aquí podrías añadir lógica para verificar si la encuesta tiene una fecha de inicio/fin,
        // si está "publicada", etc., antes de devolverla.

        return new EncuestaDetalleResource($encuesta);
    }

    /**
     * @OA\Post(
     *   path="/api/encuestas/{encuesta}/generar-url",
     *   operationId="generarUrlCifradaEncuesta",
     *   tags={"Encuestas"},
     *   summary="Generar URL cifrada para encuesta",
     *   description="Devuelve un código cifrado para acceder públicamente a la encuesta.",
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
     *     description="Código cifrado generado",
     *     @OA\JsonContent(
     *        @OA\Property(property="codigo_url", type="string", example="eyJpdiI6In..."),
     *        @OA\Property(property="url_sugerida_frontend", type="string", example="https://miapp.com/survey/code/eyJpdiI6In...")
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Encuesta no encontrada")
     * )
     */
    public function generarUrlCifrada(Encuesta $encuesta): JsonResponse
    {
        // Autorización: Solo el dueño de la encuesta o un admin puede generar esto
        $user = Auth::user();
        if (! $user->esRol('Administrador') && $user->id_cliente !== $encuesta->idCliente) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        // Simplemente ciframos el ID de la encuesta. Puedes añadir más data si quieres (ej. un salt).
        $codigoUrl = Crypt::encryptString((string)$encuesta->id_encuesta);

        // La URL base la construirá el frontend, aquí solo devolvemos el código.
        return response()->json([
            'id_encuesta' => $encuesta->id_encuesta,
            'codigo_url' => $codigoUrl,
            'url_sugerida_frontend' => url('/survey/code/' . $codigoUrl) // Ejemplo
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/encuestas/publica/code/{codigoUrl}",
     *   operationId="publicaEncuestaPorCodigo",
     *   tags={"Encuestas"},
     *   summary="Obtener encuesta usando URL cifrada",
     *   description="Desencripta el código y devuelve la encuesta para responderla públicamente.",
     *   @OA\Parameter(
     *     name="codigoUrl",
     *     in="path",
     *     description="Código cifrado de la encuesta",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Encuesta pública obtenida",
     *     @OA\JsonContent(ref="#/components/schemas/EncuestaDetalleResource")
     *   ),
     *   @OA\Response(response=404, description="Enlace inválido o encuesta no encontrada")
     * )
     */
    public function publicaPorCodigo(string $codigoUrl): EncuestaDetalleResource|JsonResponse
    {
        try {
            $decrypted = Crypt::decryptString($codigoUrl);

            if (! is_numeric($decrypted)) {
                throw new DecryptException('ID de encuesta inválido después de descifrar.');
            }

            return $this->publica((int)$decrypted);
        } catch (DecryptException $e) {
            return response()->json(['message' => 'Enlace de encuesta inválido o expirado.'], 404);
        }
    }
}
