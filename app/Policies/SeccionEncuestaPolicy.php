<?php

namespace App\Policies;

use App\Models\SeccionEncuesta;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Encuesta;

class SeccionEncuestaPolicy
{
    use HandlesAuthorization;

    // No necesitamos 'before' aquí si delegamos a la EncuestaPolicy

    /**
     * Verifica si el usuario puede modificar la encuesta padre de la sección.
     */
    protected function puedeModificarEncuestaPadre(User $user, SeccionEncuesta $seccion): bool
    {
        return $user->can('update', $seccion->encuesta); // Reutiliza la lógica de EncuestaPolicy
    }

    /**
     * Determina si el usuario puede ver el listado de secciones de una encuesta.
     * Aquí se recibe la instancia de Encuesta para aplicar la política 'view'.
     */
    public function viewAny(User $user, Encuesta $encuesta): bool // Se pasa la encuesta padre
    {
        return $user->can('view', $encuesta); // Si puede ver la encuesta, puede ver sus secciones
    }

    /**
     * Determina si el usuario puede ver una sección específica.
     */
    public function view(User $user, SeccionEncuesta $seccion): bool
    {
        return $this->puedeModificarEncuestaPadre($user, $seccion);
    }

    /**
     * Determina si el usuario puede crear una nueva sección en la encuesta dada.
     * Recibe la encuesta padre para delegar a EncuestaPolicy@update.
     */
    public function create(User $user, Encuesta $encuesta): bool // Se pasa la encuesta padre
    {
        return $user->can('update', $encuesta); // Si puede actualizar la encuesta, puede añadirle secciones
    }

    public function update(User $user, SeccionEncuesta $seccion): bool
    {
        return $this->puedeModificarEncuestaPadre($user, $seccion);
    }

    public function delete(User $user, SeccionEncuesta $seccion): bool
    {
        return $this->puedeModificarEncuestaPadre($user, $seccion);
    }

    public function reordenar(User $user, SeccionEncuesta $seccion): bool
    {
        return $this->puedeModificarEncuestaPadre($user, $seccion);
    }
}
