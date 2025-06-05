<?php

namespace App\DTOs\Reportes;

class ResumenPreguntaDto
{
  public function __construct(
    // Información de la Pregunta
    public int $id_pregunta,
    public string $texto_pregunta,
    public int $orden_pregunta_en_seccion,
    public string $nombre_tipo_pregunta,
    public int $id_tipo_pregunta,
    public int $id_seccion,
    public string $nombre_seccion,
    public int $orden_seccion,
    public int $total_respuestas_para_pregunta, // Cuántas veces se respondió esta pregunta en el rango

    // Datos Agregados (varían según el tipo de pregunta)
    /** @var array<ResumenOpcionDto>|null $resumen_opciones */
    public ?array $resumen_opciones = null, // Para preguntas de tipo opción/lista

    public ?float $valor_promedio = null,    // Para preguntas numéricas/valoración
    public ?float $valor_minimo = null,      // Para preguntas numéricas/valoración
    public ?float $valor_maximo = null,      // Para preguntas numéricas/valoración
    public ?int $conteo_nulos_o_no_aplica = 0 // Respuestas vacías o no aplicables para esta pregunta

    // Podrías añadir más campos como desviación estándar, mediana, etc.
  ) {}

  public static function fromArray(array $data): self
  {
    $resumenOpciones = null;
    if (!empty($data['resumen_opciones']) && is_array($data['resumen_opciones'])) {
      $resumenOpciones = array_map(fn($op) => ResumenOpcionDto::fromArray($op), $data['resumen_opciones']);
    }

    return new self(
      id_pregunta: $data['id_pregunta'],
      texto_pregunta: $data['texto_pregunta'],
      orden_pregunta_en_seccion: $data['orden_pregunta_en_seccion'],
      nombre_tipo_pregunta: $data['nombre_tipo_pregunta'],
      id_tipo_pregunta: $data['id_tipo_pregunta'],
      id_seccion: $data['id_seccion'],
      nombre_seccion: $data['nombre_seccion'],
      orden_seccion: $data['orden_seccion'],
      total_respuestas_para_pregunta: $data['total_respuestas_para_pregunta'],
      resumen_opciones: $resumenOpciones,
      valor_promedio: $data['valor_promedio'] ?? null,
      valor_minimo: $data['valor_minimo'] ?? null,
      valor_maximo: $data['valor_maximo'] ?? null,
      conteo_nulos_o_no_aplica: $data['conteo_nulos_o_no_aplica'] ?? 0
    );
  }

  public function toArray(): array // Similar al anterior, para consistencia
  {
    return [
      'id_pregunta' => $this->id_pregunta,
      'texto_pregunta' => $this->texto_pregunta,
      'orden_pregunta_en_seccion' => $this->orden_pregunta_en_seccion,
      'nombre_tipo_pregunta' => $this->nombre_tipo_pregunta,
      'id_tipo_pregunta' => $this->id_tipo_pregunta,
      'id_seccion' => $this->id_seccion,
      'nombre_seccion' => $this->nombre_seccion,
      'orden_seccion' => $this->orden_seccion,
      'total_respuestas_para_pregunta' => $this->total_respuestas_para_pregunta,
      'resumen_opciones' => $this->resumen_opciones ? array_map(fn($op) => $op->toArray(), $this->resumen_opciones) : null,
      'valor_promedio' => $this->valor_promedio,
      'valor_minimo' => $this->valor_minimo,
      'valor_maximo' => $this->valor_maximo,
      'conteo_nulos_o_no_aplica' => $this->conteo_nulos_o_no_aplica,
    ];
  }
}
