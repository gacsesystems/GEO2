<?php

namespace App\DTOs\Reportes;

class ResumenOpcionDto
{
    public function __construct(
        public int $id_opcion_pregunta,
        public string $texto_opcion,
        public int $conteo,
        public float $porcentaje // Porcentaje respecto al total de respuestas para esa pregunta
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_opcion_pregunta: $data['id_opcion_pregunta'],
            texto_opcion: $data['texto_opcion'],
            conteo: $data['conteo'],
            porcentaje: $data['porcentaje']
        );
    }

    public function toArray(): array
    {
         return [
            'id_opcion_pregunta' => $this->id_opcion_pregunta,
            'texto_opcion' => $this->texto_opcion,
            'conteo' => $this->conteo,
            'porcentaje' => round($this->porcentaje, 2), // Redondear para la presentación
        ];
    }
}