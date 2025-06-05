<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre_completo' => $this->nombre_completo,
            'email' => $this->email,
            'activo' => (bool) $this->activo,
            'id_cliente' => $this->id_cliente,
            'cliente' => $this->whenLoaded('cliente', fn() => new ClienteResource($this->cliente)), // Cargar recurso de cliente
            'id_rol' => $this->id_rol,
            'rol' => $this->whenLoaded('role', $this->role?->nombre_rol), // Nombre del rol
            // 'rol_detalle' => $this->whenLoaded('role', fn() => new RoleResource($this->role)), // Si tuvieras RoleResource
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
