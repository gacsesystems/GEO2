<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role; // Tu modelo Role
use App\Http\Resources\RoleResource; // Crearemos este resource
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Tag(
 *   name="Roles",
 *   description="Listado de roles disponibles en el sistema"
 * )
 */
class RolController extends Controller
{
    public function __construct()
    {
        // Proteger, ya que es información del sistema para construir UIs de admin
        $this->middleware('auth:sanctum');
    }

    /**
     * @OA\Get(
     *      path="/roles",
     *      operationId="getRolesList",
     *      tags={"Configuración del Sistema"},
     *      summary="Obtener lista de todos los roles",
     *      description="Devuelve una lista de todos los roles disponibles en el sistema.",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Operación exitosa",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/RoleResource")
     *          )
     *      ),
     *      @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(): AnonymousResourceCollection
    {
        // Por ahora, cualquier usuario autenticado puede ver los roles para los selects,
        // pero podrías restringirlo a administradores si es necesario.
        // if (!auth()->user()->esRol('administrador')) {
        //     abort(403, 'No autorizado.');
        // }
        $roles = Role::orderBy('nombre_rol')->get();
        return RoleResource::collection($roles);
    }
}
