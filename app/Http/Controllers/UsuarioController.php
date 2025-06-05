<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UsuarioService;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


/**
 * @OA\Tag(
 *   name="Usuarios",
 *   description="CRUD de usuarios del sistema"
 * )
 */
class UsuarioController extends Controller
{
    public function __construct(private UsuarioService $usuarioService)
    {
        $this->middleware('auth:sanctum');
        // Puedes aplicar policies en el método __construct si lo prefieres:
        $this->middleware('can:viewAny,App\Models\User')->only('index');
        $this->middleware('can:create,App\Models\User')->only('store');
        $this->middleware('can:view,usuario')->only('show');
        $this->middleware('can:update,usuario')->only('update');
        $this->middleware('can:delete,usuario')->only('destroy');
    }

    /**
     * @OA\Get(
     *   path="/api/usuarios",
     *   operationId="getUsuariosList",
     *   tags={"Usuarios"},
     *   summary="Listar todos los usuarios (solo Admin)",
     *   description="Devuelve todos los usuarios registrados.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200,
     *     description="Lista de usuarios",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/UserResource")
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso")
     * )
     */
    public function index(): AnonymousResourceCollection
    {
        $usuarios = $this->usuarioService->obtenerTodos();
        return UserResource::collection($usuarios);
    }

    /**
     * @OA\Post(
     *   path="/api/usuarios",
     *   operationId="storeUsuario",
     *   tags={"Usuarios"},
     *   summary="Crear un usuario (solo Admin)",
     *   description="Añade un nuevo usuario al sistema.",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/StoreUsuarioRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Usuario creado",
     *     @OA\JsonContent(ref="#/components/schemas/UserResource")
     *   ),
     *   @OA\Response(response=400, description="Error al crear usuario"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso")
     * )
     */
    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        $datos = $request->validated();
        $usuario = $this->usuarioService->crear($datos);
        return response()->json(new UserResource($usuario->load(['role', 'cliente'])), 201);
    }

    /**
     * @OA\Get(
     *   path="/api/usuarios/{usuario}",
     *   operationId="showUsuario",
     *   tags={"Usuarios"},
     *   summary="Mostrar un usuario",
     *   description="Un admin puede ver cualquier usuario; un usuario ve su propio perfil.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="usuario",
     *     in="path",
     *     description="ID del usuario",
     *     required=true,
     *     @OA\Schema(type="integer", example=3)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Usuario encontrado",
     *     @OA\JsonContent(ref="#/components/schemas/UserResource")
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Usuario no encontrado")
     * )
     */
    public function show(User $usuario): UserResource
    {
        return new UserResource($usuario->load(['role', 'cliente']));
    }

    /**
     * @OA\Put(
     *   path="/api/usuarios/{usuario}",
     *   operationId="updateUsuario",
     *   tags={"Usuarios"},
     *   summary="Actualizar un usuario (solo Admin)",
     *   description="Modifica datos del usuario especificado.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="usuario",
     *     in="path",
     *     description="ID del usuario",
     *     required=true,
     *     @OA\Schema(type="integer", example=3)
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/UpdateUsuarioRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Usuario actualizado",
     *     @OA\JsonContent(ref="#/components/schemas/UserResource")
     *   ),
     *   @OA\Response(response=400, description="Error al actualizar usuario"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Usuario no encontrado")
     * )
     */
    public function update(UpdateUsuarioRequest $request, User $usuario): JsonResponse
    {
        $datos = $request->validated();
        $usuarioActualizado = $this->usuarioService->actualizar($usuario, $datos);
        return response()->json(new UserResource($usuarioActualizado), 200);
    }

    /**
     * @OA\Delete(
     *   path="/api/usuarios/{usuario}",
     *   operationId="destroyUsuario",
     *   tags={"Usuarios"},
     *   summary="Eliminar un usuario (solo Admin)",
     *   description="Realiza un soft delete del usuario especificado.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="usuario",
     *     in="path",
     *     description="ID del usuario",
     *     required=true,
     *     @OA\Schema(type="integer", example=3)
     *   ),
     *   @OA\Response(response=204, description="Usuario eliminado"),
     *   @OA\Response(response=400, description="Error al eliminar usuario"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso"),
     *   @OA\Response(response=404, description="Usuario no encontrado")
     * )
     */
    public function destroy(User $usuario): JsonResponse
    {
        $this->usuarioService->eliminar($usuario);
        return response()->json(null, 204);
    }
}
