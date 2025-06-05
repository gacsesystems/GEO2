<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampoExterno extends Model
{
  protected $table = 'campos_externos';

  protected $fillable = [
    'entidad_externa_id',
    'nombre',         // p.ej. "Nombre", "Edad", "Domicilio"
    'tipo',           // p.ej. "string", "integer", "date", "boolean"
    'descripcion',    // opcional
  ];

  /**
   * Relación inversa: cada campo pertenece a una entidad externa
   */
  public function entidad(): BelongsTo
  {
    return $this->belongsTo(EntidadExterna::class, 'entidad_externa_id');
  }
}
