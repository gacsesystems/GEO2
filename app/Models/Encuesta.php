<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\PreguntaMapeoExterno;

/**
 * @mixin IdeHelperEncuesta
 */
class Encuesta extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $primaryKey = 'id_encuesta';
    protected $fillable = [
        'nombre',
        'descripcion',
        'id_cliente',
        'es_cuestionario',
        'fecha_inicio',
        'fecha_fin',
        'usuario_registro_id',
        'usuario_modificacion_id',
        'usuario_eliminacion_id'
    ];

    protected $casts = [
        'es_cuestionario' => 'boolean',
        'fecha_inicio'    => 'date',
        'fecha_fin'       => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function seccionesEncuesta()
    {
        return $this->hasMany(SeccionEncuesta::class, 'id_encuesta', 'id_encuesta');
    }
    public function encuestasRespondidas()
    {
        return $this->hasMany(EncuestaRespondida::class, 'id_encuesta', 'id_encuesta');
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

    /**
     * Relación con los mapeos externos de pregunta → campo_externo
     */
    public function mapeosExternos(): HasMany
    {
        return $this->hasMany(PreguntaMapeoExterno::class, 'encuesta_id');
    }

    /**
     *  Atributo “computed” para saber si la encuesta/cuestionario está en su periodo de vigencia.
     */
    public function getEstaActivaAttribute(): bool
    {
        if (! $this->es_cuestionario) return true; // Si no es cuestionario, la consideramos “siempre activa” (o puedes cambiar la lógica).

        $hoy = now()->toDateString();

        // Si no definió fecha_inicio, asumimos que ya está activa
        $desde = $this->fecha_inicio ? $this->fecha_inicio->toDateString() : $hoy;
        // Si no definió fecha_fin, asumimos que nunca vence
        $hasta = $this->fecha_fin ? $this->fecha_fin->toDateString() : $hoy;

        return ($hoy >= $desde) && ($hoy <= $hasta);
    }
}
