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
    public function __construct(private EncuestaService $service)
    {
        // 1) Protegemos todo con Sanctum
        $this->middleware('auth:sanctum')->except(['cuestionarioParaPaciente']);

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
        $encuestas = $this->service->obtenerPorId($cliente->id_cliente);
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
        if (! $user->esRol('Administrador') && $user->id_cliente !== $encuesta->id_cliente) {
            return response()->json(['message' => 'No autorizado para ver el detalle de esta encuesta.'], 403);
        }

        $detalle = $this->service->obtenerDetalleCompleto($encuesta->id_encuesta);
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
    public function update(UpdateEncuestaRequest $request, Encuesta $encuesta): EncuestaResource|JsonResponse
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
        $this->service->eliminar($encuesta); // 2) Delegar al Service (El Service usa $encuesta->delete())
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
        $encuesta = $this->service->obtenerDetalleCompleto($idEncuesta);

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
     *        @OA\Property(property="url_sugerida_frontend", type="string", example="https://miapp.com/encuesta/codigo/eyJpdiI6In...")
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
        if (! $user->esRol('Administrador') && $user->id_cliente !== $encuesta->id_cliente) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        // Simplemente ciframos el ID de la encuesta. Puedes añadir más data si quieres (ej. un salt).
        $codigoUrl = Crypt::encryptString((string)$encuesta->id_encuesta);

        // La URL base la construirá el frontend, aquí solo devolvemos el código.
        return response()->json([
            'id_encuesta' => $encuesta->id_encuesta,
            'codigo_url' => $codigoUrl,
            'url_sugerida_frontend' => url('/encuesta/codigo/' . $codigoUrl) // Ejemplo
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

    /**
     * GET /api/cuestionarios/{encuesta}/{paciente_id?}
     * 
     * Si la encuesta no es un cuestionario o no está activa → 404 o 403.
     * Si llega paciente_id, intenta cargar datos en SQL Server via Paciente.
     * Si no llega, regresa la estructura con todos los valores en blanco.
     */
    public function cuestionarioParaPaciente(int $encuesta, ?int $paciente_id = null): JsonResponse
    {
        // 1) Cargar la encuesta con secciones + preguntas:
        $enc = Encuesta::with([
            'seccionesEncuesta' => fn($q) => $q->orderBy('orden'),
            'seccionesEncuesta.preguntas' => fn($q) => $q->orderBy('orden'),
            'seccionesEncuesta.preguntas.tipoPregunta'
        ])->findOrFail($encuesta);

        // 2) Verificar que sea cuestionario
        if (! $enc->es_cuestionario) {
            return response()->json(['message' => 'No es un cuestionario válido.'], 400);
        }

        // 3) Verificar vigencia de fechas
        if (! $enc->esta_activa) {
            return response()->json(['message' => 'El cuestionario no está disponible en este momento.'], 403);
        }

        // 4) Obtener todos los mapeos de esta encuesta en un arreglo
        // $mapeos = $enc->mapeosExternos->keyBy('pregunta_id');
        // Ejemplo: [ 12 => PreguntaMapeoExterno{ entidad_externa_id:1, campo_externo_id:2 }, … ]

        // Carga respuestas previas (si existen) desde GEO (MySQL)
        $previas = null;
        if ($paciente_id) {
            // Verificar en GEO si ya hay respuestas_pregunta para este paciente y esta encuesta
            $respCabecera = $enc->encuestasRespondidas()
                ->where('paciente_id', $paciente_id)
                ->latest('created_at')
                ->first();
            if ($respCabecera) {
                // Extraer un array [id_pregunta => valor_unico o array de ids multiple]
                $previas = $respCabecera->respuestasPregunta()
                    ->with('opcionesSeleccionadas')
                    ->get()
                    ->mapWithKeys(function ($r) {
                        if ($r->id_opcion_seleccionada_unica) {
                            return [$r->id_pregunta => $r->id_opcion_seleccionada_unica];
                        }
                        if ($r->opcionesSeleccionadas->isNotEmpty()) {
                            return [
                                $r->id_pregunta => $r->opcionesSeleccionadas
                                    ->pluck('id_opcion_pregunta')
                                    ->toArray()
                            ];
                        }
                        if ($r->valor_texto !== null) {
                            return [$r->id_pregunta => $r->valor_texto];
                        }
                        if ($r->valor_numerico !== null) {
                            return [$r->id_pregunta => $r->valor_numerico];
                        }
                        if ($r->valor_fecha !== null) {
                            return [$r->id_pregunta => $r->valor_fecha];
                        }
                        if ($r->valor_booleano !== null) {
                            return [$r->id_pregunta => (bool)$r->valor_booleano];
                        }
                        return [];
                    })->toArray();
            }
        }

        // 6) Armar la estructura de secciones + preguntas + valor_prefill
        $estructura = [];
        foreach ($enc->seccionesEncuesta as $seccion) {
            $pregs = [];
            foreach ($seccion->preguntas as $preg) {
                $valor = null;
                if ($previas && array_key_exists($preg->id_pregunta, $previas)) {
                    $valor = $previas[$preg->id_pregunta];
                }
                $pregs[] = [
                    'id_pregunta'    => $preg->id_pregunta,
                    'texto'          => $preg->texto_pregunta,
                    'tipo'           => $preg->tipoPregunta->nombre,
                    'orden'          => $preg->orden,
                    'es_obligatoria' => (bool)$preg->es_obligatoria,
                    'valor_prefill'  => $valor,
                    // Más propiedades (preguntaPadre, opciones dinámicas, etc.)
                ];
            }
            $estructura[] = [
                'id_seccion' => $seccion->id_seccion,
                'nombre'     => $seccion->nombre,
                'orden'      => $seccion->orden,
                'preguntas'  => $pregs,
            ];
        }

        // 7) Devolver JSON:
        return response()->json([
            'encuesta' => [
                'id'           => $enc->id,
                'nombre'       => $enc->nombre,
                'descripcion'  => $enc->descripcion,
                'fecha_inicio' => $enc->fecha_inicio?->toDateString(),
                'fecha_fin'    => $enc->fecha_fin?->toDateString(),
            ],
            'secciones' => $estructura,
        ], 200);
    }
}
