<?php

namespace App\Policies;

use App\Models\Pregunta;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Models\SeccionEncuesta;


class PreguntaPolicy
{
    /**
     * Verifica si el usuario puede modificar la encuesta padre de la pregunta
     */
    protected function puedeModificarEncuestaPadre(User $user, Pregunta $pregunta): bool
    {
        return $user->can('update', $pregunta->seccionEncuesta->encuesta);
    }

    /**
     * Determina si el usuario puede ver la lista de preguntas de una sección.
     * Recibe la sección padre para delegar a SeccionEncuestaPolicy@view.
     */
    public function viewAny(User $user, SeccionEncuesta $seccion): bool
    {
        return $user->can('view', $seccion);
    }

    /**
     * Determina si el usuario puede ver una pregunta específica.
     */
    public function view(User $user, Pregunta $pregunta): bool
    {
        return $this->puedeModificarEncuestaPadre($user, $pregunta);
    }

    /**
     * Determina si el usuario puede crear una pregunta en una sección dada.
     * Recibe la sección padre para delegar a SeccionEncuestaPolicy@update.
     */
    public function create(User $user, SeccionEncuesta $seccion): bool
    {
        return $user->can('update', $seccion); // Delegar a SeccionEncuestaPolicy@update
    }

    /**
     * Determina si el usuario puede actualizar la pregunta.
     */
    public function update(User $user, Pregunta $pregunta): bool
    {
        return $this->puedeModificarEncuestaPadre($user, $pregunta);
    }

    /**
     * Determina si el usuario puede eliminar (soft delete) la pregunta.
     */
    public function delete(User $user, Pregunta $pregunta): bool
    {
        return $this->puedeModificarEncuestaPadre($user, $pregunta);
    }

    /**
     * Determina si el usuario puede reordenar la pregunta dentro de la sección.
     */
    public function reordenar(User $user, Pregunta $pregunta): bool
    {
        return $this->puedeModificarEncuestaPadre($user, $pregunta);
    }
}
