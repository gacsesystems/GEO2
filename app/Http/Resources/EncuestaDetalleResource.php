<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EncuestaDetalleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_encuesta' => $this->id_encuesta,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'id_cliente' => $this->id_cliente,
            'cliente' => $this->whenLoaded('cliente', fn() => new ClienteResource($this->cliente)), // Usar ClienteResource
            'secciones_encuesta' => SeccionEncuestaDetalleResource::collection($this->whenLoaded('seccionesEncuesta')), // Usaremos un SeccionEncuestaDetalleResource
            'fecha_registro' => $this->created_at?->toIso8601String(),
            'fecha_modificacion' => $this->updated_at?->toIso8601String(),
        ];
    }
}
