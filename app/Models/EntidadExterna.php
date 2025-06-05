<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntidadExterna extends Model
{
  protected $table = 'entidades_externas';

  protected $fillable = [
    'clave',       // p.ej. "HPREG05", "HPAREAS", "HPMEDICOS", etc.
    'descripcion', // texto legible
  ];

  /**
   * Relaciona a todos los campos que pertenecen a esta entidad externa
   */
  public function campos(): HasMany
  {
    return $this->hasMany(CampoExterno::class, 'entidad_externa_id');
  }
}
