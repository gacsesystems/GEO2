<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpcionPreguntaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_opcion_pregunta' => $this->id_opcion_pregunta,
            'id_pregunta' => $this->id_pregunta,
            'texto_opcion' => $this->texto_opcion,
            'valor_opcion' => $this->valor_opcion,
            'orden' => $this->orden,
            // 'created_at'    => $this->created_at?->toDateTimeString(),
            // 'updated_at'    => $this->updated_at?->toDateTimeString(),
            // Si quieres incluir flags de auditoría o softDeletes:
            // 'deleted_at' => $this->deleted_at?->toDateTimeString(),
        ];
    }
}
