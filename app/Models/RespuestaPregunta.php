<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperRespuestaPregunta
 */
class RespuestaPregunta extends Model
{
    protected $table = 'respuestas_pregunta';
    protected $primaryKey = 'id_respuesta_pregunta';

    protected $fillable = [
        'id_encuesta_respondida',
        'id_pregunta',
        'valor_texto',
        'valor_numerico',
        'valor_fecha',
        'valor_booleano',
        'id_opcion_seleccionada_unica'
    ];

    protected $casts = [
        'valor_numerico' => 'decimal:4',
        'valor_fecha' => 'datetime',
        'valor_booleano' => 'boolean'
    ];

    public function encuestaRespondida()
    {
        return $this->belongsTo(EncuestaRespondida::class, 'id_encuesta_respondida', 'id_encuesta_respondida');
    }

    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class, 'id_pregunta', 'id_pregunta');
    }

    public function opcionSeleccionadaUnica()
    {
        return $this->belongsTo(OpcionPregunta::class, 'id_opcion_seleccionada_unica', 'id_opcion_pregunta');
    }

    public function opcionesSeleccionadas()
    {
        return $this->belongsToMany(OpcionPregunta::class, 'respuesta_opcion_seleccionada', 'id_respuesta_pregunta', 'id_opcion_pregunta');
    }
}
