<?php

namespace App\Policies;

use App\Models\ParametroSistema;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ParametroSistemaPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->esRol('administrador')) {
            return true;
        }
        return null; // Denegar a no admins por defecto si no hay métodos más específicos
    }

    public function viewAny(User $user): bool
    {
        return false;
    } // Cubierto por before
    public function view(User $user, ParametroSistema $parametroSistema): bool
    {
        return false;
    } // Cubierto por before
    public function create(User $user): bool
    {
        return false;
    } // Los parámetros se crean vía seeders o una UI muy restringida
    public function update(User $user, ParametroSistema $parametroSistema): bool
    {
        return false;
    } // Cubierto por before
    public function delete(User $user, ParametroSistema $parametroSistema): bool
    {
        return false;
    } // No se eliminan parámetros usualmente
}
