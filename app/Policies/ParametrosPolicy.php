<?php

namespace App\Policies;

use App\Models\Parametros;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ParametrosPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->esRol('Administrador')) {
            return true;
        }
        return null; // Denegar a no admins por defecto si no hay métodos más específicos
    }

    public function viewAny(User $user): bool
    {
        return false;
    } // Cubierto por before
    public function view(User $user, Parametros $parametros): bool
    {
        return false;
    } // Cubierto por before
    public function create(User $user): bool
    {
        return false;
    } // Los parámetros se crean vía seeders o una UI muy restringida
    public function update(User $user, Parametros $parametros): bool
    {
        return false;
    } // Cubierto por before
    public function delete(User $user, Parametros $parametros): bool
    {
        return false;
    } // No se eliminan parámetros usualmente
}
