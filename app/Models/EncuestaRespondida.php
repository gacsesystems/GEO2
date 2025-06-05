<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperEncuestaRespondida
 */
class EncuestaRespondida extends Model
{
    protected $table = 'encuestas_respondidas';
    protected $primaryKey = 'id_encuesta_respondida';

    protected $fillable = [
        'id_encuesta',
        'correo_respuesta',
        'id_usuario_respuesta',
        'fecha_inicio_respuesta',
        'fecha_fin_respuesta',
        'metadatos'
    ];

    protected $casts = [
        'fecha_inicio_respuesta' => 'datetime',
        'fecha_fin_respuesta' => 'datetime',
        'metadatos' => 'json'
    ];

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class, 'id_encuesta', 'id_encuesta');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario_respuesta', 'id');
    }

    public function respuestas()
    {
        return $this->hasMany(RespuestaPregunta::class, 'id_encuesta_respondida', 'id_encuesta_respondida');
    }
}
