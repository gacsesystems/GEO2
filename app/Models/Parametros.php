<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

/**
 * @mixin IdeHelperParametros
 */
class Parametros extends Model
{
    use HasFactory, Auditable;

    protected $table = 'parametros';
    protected $primaryKey = 'id_parametro';

    protected $fillable = [
        'clave',
        'valor',
        'descripcion',
        'usuario_modificacion_id'
    ];

    public function usuarioModificacion()
    {
        return $this->belongsTo(User::class, 'usuario_modificacion_id');
    }
}
