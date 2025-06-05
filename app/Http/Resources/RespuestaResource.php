<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RespuestaResource extends JsonResource
{
  public function toArray($request): array
  {
    return [
      'id_respuesta'               => $this->id_respuesta_pregunta,
      'id_pregunta'                => $this->id_pregunta,
      'valor_texto'                => $this->valor_texto,
      'valor_numerico'             => $this->valor_numerico,
      'valor_fecha'                => $this->valor_fecha,
      'valor_booleano'             => $this->valor_booleano,
      'id_opcion_seleccionada_unica' => $this->id_opcion_seleccionada_unica,
      'opciones_seleccionadas'     => $this->opcionesSeleccionadas->pluck('id_opcion_pregunta'),
      'created_at'                 => $this->created_at?->format('Y-m-d H:i:s'),
      'updated_at'                 => $this->updated_at?->format('Y-m-d H:i:s'),
    ];
  }
}
