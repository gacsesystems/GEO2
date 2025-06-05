<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TipoPreguntaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id_tipo_pregunta'   => $this->id_tipo_pregunta,
            'nombre'             => $this->nombre,
            'descripcion'        => $this->descripcion,
            'requiere_opciones'  => (bool) $this->requiere_opciones,
            'permite_min_max_numerico'  => (bool) $this->permite_min_max_numerico,
            'permite_min_max_fecha'     => (bool) $this->permite_min_max_fecha,
            'es_seleccion_multiple'     => (bool) $this->es_seleccion_multiple,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }
}
