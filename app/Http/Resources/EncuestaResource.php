<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EncuestaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_encuesta' => $this->id_encuesta,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'id_cliente' => $this->id_cliente,
            'alias_cliente' => $this->whenLoaded('cliente', $this->cliente?->alias),
            'cantidad_secciones' => $this->when(
                isset($this->secciones_encuesta_count), // Si existe el conteo de secciones, usarlo
                $this->secciones_encuesta_count, // Si no existe, usar el conteo de secciones cargado
                $this->whenLoaded('seccionesEncuesta', $this->seccionesEncuesta->count()) // Si no está cargado, usar el conteo de secciones
            ),
            // Si cargaste previamente “preguntas” en cada sección, reduces en memoria;
            // de lo contrario, puedes exponer un campo null o 0.
            'cantidad_preguntas' => $this->whenLoaded('seccionesEncuesta', function () {
                return $this->seccionesEncuesta->sum(fn($seccion) => $seccion->preguntas->count());
            }),
            // Si cargaste subrecursos “seccionesEncuesta”, los anidas:
            'secciones'          => SeccionEncuestaResource::collection(
                $this->whenLoaded('seccionesEncuesta')
            ),

            // Links para la API
            'links'              => [
                'self'     => route('encuestas.show', $this->id_encuesta),
                // 'disenar'  => route('encuestas.disenar', $this->id_encuesta),
                // 'reportes' => route('encuestas.reportes', $this->id_encuesta),
            ],

            'fecha_registro' => $this->created_at?->toIso8601String(),
            'fecha_modificacion' => $this->updated_at?->toIso8601String(),
        ];
    }
}
