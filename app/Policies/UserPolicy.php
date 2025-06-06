<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization; // O Response en Laravel 10+

class UserPolicy
{
    use HandlesAuthorization; // O puedes retornar Response directamente en Laravel 10+

    /**
     * Determinar si el usuario puede ver cualquier modelo.
     * Solo Administradores.
     */
    public function viewAny(User $user): bool
    {
        return $user->esRol('Administrador');
    }

    /**
     * Determinar si el usuario puede ver el modelo.
     * Administradores pueden ver cualquiera. Un usuario puede ver su propio perfil.
     */
    public function view(User $user, User $model): bool // $model es el usuario que se quiere ver
    {
        if ($user->esRol('Administrador')) {
            return true;
        }
        return $user->id === $model->id;
    }

    /**
     * Determinar si el usuario puede crear modelos.
     * Solo Administradores.
     */
    public function create(User $user): bool
    {
        return $user->esRol('Administrador');
    }

    /**
     * Determinar si el usuario puede actualizar el modelo.
     * Solo Administradores. (Un usuario podría actualizar su propio perfil a través de otra ruta/policy).
     */
    public function update(User $user, User $model): bool
    {
        // Un admin no puede editarse a sí mismo a través de este flujo general de "gestión de usuarios"
        // para evitar auto-bloqueo de rol o desactivación.
        // Si el admin quiere editar su propio perfil, debería haber una ruta/controlador específico.
        // if ($user->id === $model->id && $user->esRol('Administrador')) {
        //     return false; // Evitar que un admin se modifique a sí mismo en el CRUD de usuarios
        // }
        return $user->esRol('Administrador');
    }

    /**
     * Determinar si el usuario puede eliminar el modelo.
     * Solo Administradores, y no pueden eliminarse a sí mismos.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false; // No se puede auto-eliminar
        }
        return $user->esRol('Administrador');
    }

    /**
     * Determinar si el usuario puede restaurar el modelo.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->esRol('Administrador');
    }

    /**
     * Determinar si el usuario puede eliminar permanentemente el modelo.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->esRol('Administrador');
    }
}
