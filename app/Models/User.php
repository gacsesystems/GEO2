<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use App\Notifications\MiNotificacionDeVerificacionDeEmail;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Auditable;


    protected $table = 'usuarios';
    protected $primaryKey = 'id'; // si tu PK en migración es id

    /**
     * Los campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'nombre_completo',
        'email',
        'password',
        'id_cliente',
        'id_rol',
        'activo',
    ];

    /**
     * Los campos que se deben ocultar para la serialización.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'activo' => 'boolean',
    ];

    /**
     * Envia la notificación de verificación de email.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new MiNotificacionDeVerificacionDeEmail); // Usa tu notificación personalizada
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function role() // Un usuario tiene un rol
    {
        return $this->belongsTo(Role::class, 'id_rol', 'id_rol');
    }

    public function esRol(string $roleName): bool
    {
        return optional($this->role)->nombre_rol === $roleName;
    }

    /** Verifica permisos */
    public function tienePermiso(string $perm): bool
    {
        return $this->role
            ? $this->role->permisos->pluck('nombre_permiso')->contains($perm)
            : false;
    }
}
