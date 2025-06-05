<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

/**
 * @mixin IdeHelperSeccionEncuesta
 */
class SeccionEncuesta extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'secciones_encuesta';
    protected $primaryKey = 'id_seccion';
    protected $fillable = [
        'nombre',
        'descripcion',
        'orden',
        'id_encuesta',
        'usuario_registro_id',
        'usuario_modificacion_id',
        'usuario_eliminacion_id'
    ];

    protected $casts = [
        'orden' => 'integer'
    ];

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class, 'id_encuesta', 'id_encuesta');
    }

    public function preguntas()
    {
        return $this->hasMany(Pregunta::class, 'id_seccion', 'id_seccion')->orderBy('orden');
    }

    // Relaciones para auditoría
    public function usuarioRegistro()
    {
        return $this->belongsTo(User::class, 'usuario_registro_id');
    }
    public function usuarioModificacion()
    {
        return $this->belongsTo(User::class, 'usuario_modificacion_id');
    }
    public function usuarioEliminacion()
    {
        return $this->belongsTo(User::class, 'usuario_eliminacion_id');
    }
}
