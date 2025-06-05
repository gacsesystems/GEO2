<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PreguntaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_pregunta' => $this->id_pregunta,
            'id_seccion' => $this->id_seccion,
            'texto_pregunta' => $this->texto_pregunta,
            'id_tipo_pregunta' => $this->id_tipo_pregunta,
            'tipo_pregunta_info' => new TipoPreguntaResource($this->whenLoaded('tipoPregunta')),
            'orden' => $this->orden,
            'es_obligatoria' => (bool) $this->es_obligatoria,
            'numero_minimo' => $this->numero_minimo,
            'numero_maximo' => $this->numero_maximo,
            'fecha_minima' => optional($this->fecha_minima)->toDateString(), // Formato YYYY-MM-DD
            'fecha_maxima' => optional($this->fecha_maxima)->toDateString(),
            'hora_minima' => $this->hora_minima,
            'hora_maxima' => $this->hora_maxima,
            'texto_ayuda' => $this->texto_ayuda,
            'id_pregunta_padre' => $this->id_pregunta_padre,
            'pregunta_padre_texto' => $this->whenLoaded('preguntaPadre', $this->preguntaPadre?->texto_pregunta),
            'valor_condicion_padre' => $this->valor_condicion_padre,
            'id_opcion_condicion_padre' => $this->id_opcion_condicion_padre,
            'opcion_condicion_padre_texto' => $this->whenLoaded('opcionCondicionPadre', $this->opcionCondicionPadre?->texto_opcion),
            'opciones_pregunta' => OpcionPreguntaResource::collection($this->whenLoaded('opcionesPregunta')),
            'created_at' => $this->created_at?->toIso8601String(), // Formato ISO 8601 osea 2025-06-03T12:00:00.000000Z
            'updated_at' => $this->updated_at?->toIso8601String(), // Formato ISO 8601 osea 2025-06-03T12:00:00.000000Z
        ];
    }
}
