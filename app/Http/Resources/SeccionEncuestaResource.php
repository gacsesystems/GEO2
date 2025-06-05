<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeccionEncuestaResource extends JsonResource
{
    /**
     * Transformar la sección en un arreglo JSON.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_seccion' => $this->id_seccion,
            'id_encuesta' => $this->id_encuesta,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'orden' => $this->orden,
            // 'id_encuesta'  => $this->id_encuesta, // Para detalle
            'cantidad_preguntas' => $this->whenLoaded('preguntas', $this->preguntas->count()),
            'preguntas' => PreguntaResource::collection($this->whenLoaded('preguntas')), // Para detalle
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
