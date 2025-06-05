<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

/**
 * @mixin IdeHelperPregunta
 */
class Pregunta extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $primaryKey = 'id_pregunta';
    protected $fillable = [
        'id_seccion',
        'texto_pregunta',
        'id_tipo_pregunta',
        'orden',
        'es_obligatoria',
        'numero_minimo',
        'numero_maximo',
        'fecha_minima',
        'fecha_maxima',
        'hora_minima',  // Laravel maneja 'H:i:s'
        'hora_maxima',
        'texto_ayuda',
        'id_pregunta_padre',
        'valor_condicion_padre',
        'id_opcion_condicion_padre',
        'usuario_registro_id', // Auditoría
        'usuario_modificacion_id', // Auditoría
        'usuario_eliminacion_id' // Auditoría
    ];

    protected $casts = [
        'orden' => 'integer',
        'es_obligatoria' => 'boolean',
        'numero_minimo' => 'integer',
        'numero_maximo' => 'integer',
        'fecha_minima' => 'date:Y-m-d', // Especificar formato si es necesario
        'fecha_maxima' => 'date:Y-m-d',
        // 'hora_minima' => 'datetime:H:i:s', // Laravel puede manejar time como string 'H:i:s'
        // 'hora_maxima' => 'datetime:H:i:s', // o puedes usar un mutador/accesor si es necesario
    ];

    public function seccionEncuesta()
    {
        return $this->belongsTo(SeccionEncuesta::class, 'id_seccion', 'id_seccion');
    }

    public function tipoPregunta()
    {
        return $this->belongsTo(TipoPregunta::class, 'id_tipo_pregunta', 'id_tipo_pregunta');
    }

    public function preguntaPadre()
    {
        return $this->belongsTo(Pregunta::class, 'id_pregunta_padre', 'id_pregunta');
    }

    public function opcionCondicionPadre()
    {
        return $this->belongsTo(OpcionPregunta::class, 'id_opcion_condicion_padre', 'id_opcion_pregunta');
    }

    public function opcionesPregunta()
    {
        return $this->hasMany(OpcionPregunta::class, 'id_pregunta', 'id_pregunta')->orderBy('orden');
    }

    public function respuestasPregunta()
    {
        return $this->hasMany(RespuestaPregunta::class, 'id_pregunta', 'id_pregunta');
    }

    // Relaciones para auditoría (si no están ya en el Trait o para acceso directo)
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
