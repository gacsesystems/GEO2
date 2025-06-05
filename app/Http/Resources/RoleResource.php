<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *   schema="RoleResource",
 *   title="Role Resource",
 *   description="Representación de un rol del sistema",
 *   @OA\Property(property="id_rol", type="integer", example=1),
 *   @OA\Property(property="nombre_rol", type="string", example="Administrador"),
 *   @OA\Property(property="descripcion_rol", type="string", nullable=true, example="Acceso total al sistema.")
 * )
 */
class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_rol' => $this->id_rol,
            'nombre_rol' => $this->nombre_rol,
            'descripcion_rol' => $this->descripcion_rol,
        ];
    }
}
