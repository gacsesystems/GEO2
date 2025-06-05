<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @mixin IdeHelperPermisos
 */
class Permisos extends Model
{
    use HasFactory;

    protected $table = 'permisos';
    protected $primaryKey = 'id_permiso';

    protected $fillable = [
        'nombre_permiso',
        'descripcion_permiso',
    ];

    /**
     * Los roles que tienen este permiso.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permiso_rol', 'id_permiso', 'id_rol');
    }
}
