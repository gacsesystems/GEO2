<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id_cliente';

    protected $fillable = [
        'razon_social',
        'alias',
        'ruta_logo',
        'activo',
        'limite_encuestas',
        'vigencia',
        'usuario_registro_id', // Auditoría
        'usuario_modificacion_id', // Auditoría
        'usuario_eliminacion_id' // Auditoría
    ];

    protected $casts = [
        'activo' => 'boolean',
        'vigencia' => 'datetime',
        'limite_encuestas' => 'integer'
    ];

    // public function encuestas()
    // {
    //     return $this->hasMany(Encuesta::class, 'id_cliente', 'id_cliente');
    // }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'id_cliente', 'id_cliente');
    }

    // Relaciones para auditoría (opcional si usas observers/events)
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
