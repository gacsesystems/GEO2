<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperRole
 */
class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $primaryKey = 'id_rol';

    protected $fillable = [
        'nombre_rol',
        'descripcion_rol',
    ];

    /**
     * Los permisos que pertenecen a este rol.
     */
    public function permisos()
    {
        return $this->belongsToMany(Permisos::class, 'permiso_rol', 'id_rol', 'id_permiso');
    }

    /**
     * Los usuarios que tienen este rol.
     */
    public function usuarios()
    {
        return $this->hasMany(User::class, 'id_rol', 'id_rol');
    }
}
