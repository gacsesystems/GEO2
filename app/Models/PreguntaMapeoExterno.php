<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreguntaMapeoExterno extends Model
{
  protected $table = 'pregunta_mapeo_externo';

  protected $fillable = [
    'encuesta_id',
    'pregunta_id',
    'entidad_externa_id',
    'campo_externo_id',
  ];

  public function encuesta(): BelongsTo
  {
    return $this->belongsTo(Encuesta::class, 'encuesta_id');
  }

  public function pregunta(): BelongsTo
  {
    return $this->belongsTo(Pregunta::class, 'pregunta_id', 'id_pregunta');
  }

  public function entidad(): BelongsTo
  {
    return $this->belongsTo(EntidadExterna::class, 'entidad_externa_id');
  }

  public function campo(): BelongsTo
  {
    return $this->belongsTo(CampoExterno::class, 'campo_externo_id');
  }
}
