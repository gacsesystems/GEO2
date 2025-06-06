<?php

namespace App\Policies;

use App\Models\User;
use App\Models\OpcionPregunta;
use App\Models\Pregunta;
use Illuminate\Auth\Access\HandlesAuthorization;

class OpcionPreguntaPolicy
{
    use HandlesAuthorization;
    /**
     * Antes de cualquier método, permitir al administrador global.
     */
    public function before(User $user, $ability)
    {
        if ($user->esRol('Administrador')) {
            return true;
        }
    }

    /**
     * Solo un usuario con permiso de “update” en la pregunta padre puede ver todas las opciones.
     */
    public function viewAny(User $user, Pregunta $pregunta): bool
    {
        return $user->can('update', $pregunta); // Delegar a PreguntaPolicy@update
    }

    /**
     * Ver una opción específica: quien pueda “update” en la pregunta padre.
     */
    public function view(User $user, OpcionPregunta $opcion): bool
    {
        return $user->can('update', $opcion->pregunta);
    }

    /**
     * Crear: si el usuario puede actualizar la pregunta padre.
     */
    public function create(User $user, Pregunta $pregunta): bool
    {
        // 1) El tipo de pregunta debe permitir opciones
        if (! $pregunta->tipoPregunta?->requiere_opciones) return false;

        // 2) Si es administrador, puede siempre
        if ($user->esRol('Administrador')) return true;

        // 3) Si es cliente, sólo si es dueño de la encuesta que contiene la sección de la pregunta
        $encuesta = $pregunta->seccionEncuesta->encuesta;
        return $user->esRol('Cliente') && $encuesta && $encuesta->id_cliente === $user->id_cliente;
    }

    /**
     * Actualizar esta opción: si el usuario puede actualizar la pregunta padre.
     */
    public function update(User $user, OpcionPregunta $opcion): bool
    {
        return $user->can('update', $opcion->pregunta);
    }

    /**
     * Eliminar (soft delete): si puede actualizar la pregunta padre.
     */
    public function delete(User $user, OpcionPregunta $opcion): bool
    {
        return $user->can('update', $opcion->pregunta);
    }

    /**
     * Reordenar: si puede actualizar la pregunta padre.
     */
    public function reordenar(User $user, OpcionPregunta $opcion): bool
    {
        return $user->can('update', $opcion->pregunta);
    }
}
