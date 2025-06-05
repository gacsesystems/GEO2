<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage; // Para generar URL completa del logo

class ClienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_cliente' => $this->id_cliente,
            'razon_social' => $this->razon_social,
            'alias' => $this->alias,
            'ruta_logo_url' => $this->ruta_logo ? Storage::disk('public')->url($this->ruta_logo) : null,
            'ruta_logo_relativa' => $this->ruta_logo, // También puedes enviar la relativa si el front la necesita
            'activo' => (bool) $this->activo,
            'limite_encuestas' => $this->limite_encuestas,
            'vigencia' => $this->vigencia ? $this->vigencia->toIso8601String() : null,
            'fecha_registro' => $this->created_at->toIso8601String(),
            'fecha_modificacion' => $this->updated_at->toIso8601String(), // La fecha se devuelve así: 2025-05-30T15:30:00.000000Z
            // Opcional: incluir información de auditoría si es relevante para el consumidor de la API
            // 'usuario_registro_id' => $this->usuario_registro_id,
            // 'usuario_modificacion_id' => $this->usuario_modificacion_id,
        ];
    }
}
