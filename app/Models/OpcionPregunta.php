<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperOpcionPregunta
 */
class OpcionPregunta extends Model
{
    use SoftDeletes;

    protected $table = 'opciones_pregunta';
    protected $primaryKey = 'id_opcion_pregunta';

    protected $fillable = [
        'id_pregunta',
        'texto_opcion',
        'valor_opcion',
        'orden'
    ];

    protected $casts = [
        'orden' => 'integer'
    ];

    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class, 'id_pregunta', 'id_pregunta');
    }
}
