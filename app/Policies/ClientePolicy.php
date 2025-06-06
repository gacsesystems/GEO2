<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ClientePolicy
{
    /**
     * Un administrador puede realizar cualquier acción sobre Clientes.
     * Este método se ejecutará antes que cualquier otro método de la policy.
     * Si devuelve true, se concede el permiso. Si devuelve false, se deniega.
     * Si devuelve null, la policy continuará con el método específico.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->esRol('Administrador'))  return true;

        return null; // Continuar con otros métodos para no administradores
    }

    /**
     * Determina si el usuario puede ver cualquier cliente (para index/listar).
     */
    public function viewAny(User $user): bool
    {
        // Ejemplo: solo administradores pueden listar todos los clientes
        return $user->esRol('Administrador');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Cliente $cliente): bool
    {
        // Permitir si es Admin ó si el usuario es “dueño” del cliente (por ejemplo, 
        // si dentro de Cliente tienes user_id que apunta al User propietario).
        if ($user->esRol('Administrador') || $user->id === $cliente->user_id) return true;

        throw new AuthorizationException('No autorizado para ver este cliente.'); // Aquí lanzas la excepción con tu texto:
    }

    /**
     * Determina si el usuario puede crear un nuevo cliente.
     */
    public function create(User $user): bool
    {
        if ($user->esRol('Administrador')) return true; // Solo admin puede crear

        throw new AuthorizationException('No autorizado para crear un cliente.');
    }

    /**
     * Determina si el usuario puede actualizar un cliente.
     */
    public function update(User $user, Cliente $cliente): bool
    {
        if ($user->esRol('Administrador') || $user->id === $cliente->user_id) return true;

        throw new AuthorizationException('No autorizado para actualizar este cliente.');
    }

    /**
     * Determina si el usuario puede eliminar (soft delete) un cliente.
     */
    public function delete(User $user, Cliente $cliente): bool
    {
        if ($user->esRol('Administrador')) return true;

        throw new AuthorizationException('No autorizado para eliminar este cliente.');
    }

    /**
     * Determina
     */
    public function restore(User $user, Cliente $cliente): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cliente $cliente): bool
    {
        return false;
    }
}
