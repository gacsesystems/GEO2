<?php

namespace App\Policies;

use App\Models\Encuesta;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class EncuestaPolicy
{
  /**
   * Determina si el usuario puede ver la lista de encuestas.
   *
   * @param  User  $user
   * @return bool
   */
  public function viewAny(User $user): bool
  {
    // Administradores y Clientes pueden listar encuestas.
    return $user->esRol('Administrador') || $user->esRol('Cliente');
  }

  /**
   * Determina si el usuario puede ver una encuesta en particular.
   *
   * @param  User     $user
   * @param  Encuesta $encuesta
   * @return bool
   */
  public function view(User $user, Encuesta $encuesta): bool
  {
    if ($user->esRol('Administrador')) return true;


    // Si es cliente, solo puede ver si pertenece a su cliente
    if ($user->esRol('Cliente') && $user->id_cliente === $encuesta->idCliente) {
      return true;
    }

    throw new AuthorizationException('No autorizado para ver esta encuesta.');
  }

  /**
   * Determina si el usuario puede crear una encuesta.
   *
   * @param  User  $user
   * @return bool
   */
  public function create(User $user): bool
  {
    // Administrador o Cliente puede crear para cualquier cliente.
    if ($user->esRol('Administrador') || $user->esRol('Cliente')) return true;

    throw new AuthorizationException('No autorizado para crear encuestas.');
  }

  /**
   * Determina si el usuario puede actualizar una encuesta existente.
   *
   * @param  User     $user
   * @param  Encuesta $encuesta
   * @return bool
   */
  public function update(User $user, Encuesta $encuesta): bool
  {
    if ($user->esRol('Administrador')) return true;

    if ($user->esRol('Cliente') && $user->id_cliente === $encuesta->idCliente) {
      return true;
    }

    throw new AuthorizationException('No autorizado para editar esta encuesta.');
  }

  /**
   * Determina si el usuario puede eliminar (soft delete) una encuesta.
   *
   * @param  User     $user
   * @param  Encuesta $encuesta
   * @return bool
   */
  public function delete(User $user, Encuesta $encuesta): bool
  {
    if ($user->esRol('Administrador')) return true;


    if ($user->esRol('Cliente') && $user->id_cliente === $encuesta->idCliente) {
      return true;
    }

    throw new AuthorizationException('No autorizado para eliminar esta encuesta.');
  }

  public function viewReport(User $user, Encuesta $encuesta): bool
  {
    if ($user->esRol('Administrador')) return true;

    return $user->esRol('Cliente') && $encuesta->id_cliente === $user->id_cliente;
  }

  // Opcional: restore() / forceDelete() si implementas SoftDeletes.
}
