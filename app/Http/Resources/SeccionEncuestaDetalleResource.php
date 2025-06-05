<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeccionEncuestaDetalleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_seccion' => $this->id_seccion,
            // No necesitamos id_encuesta aquí si ya está en el nivel superior de EncuestaDetalleResource
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'orden' => $this->orden,
            'preguntas' => PreguntaResource::collection($this->whenLoaded('preguntas')), // Aquí cargaremos las preguntas con su propio resource
        ];
    }
}
