<?php

namespace App\DTOs\Reportes;

use Carbon\Carbon;


class RespuestaDetalladaDto
{
  public function __construct(
    // Información de la Encuesta y Cliente
    public int $id_encuesta,
    public string $nombre_encuesta,
    public ?string $alias_cliente, // Puede ser null si el cliente fue eliminado
    public ?string $razon_social_cliente, // Puede ser null

    // Información de la Respuesta General
    public int $id_encuesta_respondida,
    public ?string $correo_respuesta_encuesta,
    public Carbon $fecha_inicial_respuesta, // Cuándo se empezó a responder
    public ?Carbon $fecha_final_respuesta, // Cuándo se terminó
    public ?string $tiempo_transcurrido, // Ej. "00:05:30"

    // Información de la Sección
    public int $id_seccion,
    public string $nombre_seccion,
    public int $orden_seccion,

    // Información de la Pregunta
    public int $id_pregunta,
    public string $texto_pregunta,
    public int $orden_pregunta_en_seccion,
    public string $nombre_tipo_pregunta,
    public int $id_tipo_pregunta,

    // La Respuesta Específica
    public int $id_respuesta_pregunta_detalle, // El ID del registro en respuestas_pregunta
    public ?string $valor_respuesta_texto_formateado, // La respuesta como un string legible (ya procesada)
    public Carbon $fecha_registro_respuesta_detalle // Cuándo se guardó esta respuesta específica
  ) {}

  /**
   * Opcional: Método estático para crear desde un modelo Eloquent o un array.
   * Esto es útil si el servicio construye estos DTOs.
   *
   * @param array $data
   * @return self
   */
  public static function fromArray(array $data): self
  {
    return new self(
      id_encuesta: $data['id_encuesta'],
      nombre_encuesta: $data['nombre_encuesta'],
      alias_cliente: $data['alias_cliente'] ?? null,
      razon_social_cliente: $data['razon_social_cliente'] ?? null,
      id_encuesta_respondida: $data['id_encuesta_respondida'],
      correo_respuesta_encuesta: $data['correo_respuesta_encuesta'] ?? null,
      fecha_inicial_respuesta: Carbon::parse($data['fecha_inicial_respuesta']),
      fecha_final_respuesta: isset($data['fecha_final_respuesta']) ? Carbon::parse($data['fecha_final_respuesta']) : null,
      tiempo_transcurrido: $data['tiempo_transcurrido'] ?? null,
      id_seccion: $data['id_seccion'],
      nombre_seccion: $data['nombre_seccion'],
      orden_seccion: $data['orden_seccion'],
      id_pregunta: $data['id_pregunta'],
      texto_pregunta: $data['texto_pregunta'],
      orden_pregunta_en_seccion: $data['orden_pregunta_en_seccion'],
      nombre_tipo_pregunta: $data['nombre_tipo_pregunta'],
      id_tipo_pregunta: $data['id_tipo_pregunta'],
      id_respuesta_pregunta_detalle: $data['id_respuesta_pregunta_detalle'],
      valor_respuesta_texto_formateado: $data['valor_respuesta_texto_formateado'] ?? null,
      fecha_registro_respuesta_detalle: Carbon::parse($data['fecha_registro_respuesta_detalle'])
    );
  }

  /**
   * Opcional: Convertir a array para la respuesta API.
   * Si usas API Resources, esto no es estrictamente necesario aquí,
   * pero puede ser útil para consistencia o si no usas API Resources para estos DTOs.
   */
  public function toArray(): array
  {
    return [
      'id_encuesta' => $this->id_encuesta,
      'nombre_encuesta' => $this->nombre_encuesta,
      'alias_cliente' => $this->alias_cliente,
      'razon_social_cliente' => $this->razon_social_cliente,
      'id_encuesta_respondida' => $this->id_encuesta_respondida,
      'correo_respuesta_encuesta' => $this->correo_respuesta_encuesta,
      'fecha_inicial_respuesta' => $this->fecha_inicial_respuesta->toIso8601String(),
      'fecha_final_respuesta' => $this->fecha_final_respuesta?->toIso8601String(),
      'tiempo_transcurrido' => $this->tiempo_transcurrido,
      'id_seccion' => $this->id_seccion,
      'nombre_seccion' => $this->nombre_seccion,
      'orden_seccion' => $this->orden_seccion,
      'id_pregunta' => $this->id_pregunta,
      'texto_pregunta' => $this->texto_pregunta,
      'orden_pregunta_en_seccion' => $this->orden_pregunta_en_seccion,
      'nombre_tipo_pregunta' => $this->nombre_tipo_pregunta,
      'id_tipo_pregunta' => $this->id_tipo_pregunta,
      'id_respuesta_pregunta_detalle' => $this->id_respuesta_pregunta_detalle,
      'valor_respuesta_texto_formateado' => $this->valor_respuesta_texto_formateado,
      'fecha_registro_respuesta_detalle' => $this->fecha_registro_respuesta_detalle->toIso8601String(),
    ];
  }
}
