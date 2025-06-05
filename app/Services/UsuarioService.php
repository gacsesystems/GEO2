<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth; // Para verificar permisos del actor

class UsuarioService
{
  public function obtenerTodos(): Collection
  {
    // Considerar paginación para grandes cantidades de usuarios
    return User::with(['role', 'cliente'])->orderBy('nombre_completo')->get();
  }

  // public function obtenerPorId(int $idUsuario): ?User
  // {
  //   return User::with(['role', 'cliente'])->find($idUsuario);
  // }

  /**
   * Crear un nuevo usuario.
   * @param array $datos Validados (nombre_completo, email, password, id_rol, id_cliente, activo).
   * @return User
   * @throws \Exception
   */
  public function crear(array $datos): User
  {
    // La unicidad del email ya se valida en el FormRequest

    $datos['password'] = Hash::make($datos['password']);
    $datos['activo']   = $datos['activo'] ?? true;
    if (!isset($datos['email_verified_at']) && $datos['activo']) {
      $datos['email_verified_at'] = now();
    }
    return User::create($datos);
  }

  /**
   * Actualizar un usuario existente.
   * @param User $usuario El modelo User a actualizar.
   * @param array $datos Validados.
   * @return User
   * @throws \Exception
   */
  public function actualizar(User $usuario, array $datos): User
  {
    if (isset($datos['email']) && $datos['email'] !== $usuario->email) {
      $usuario->email            = $datos['email'];
      $usuario->email_verified_at = null;
    }
    if (!empty($datos['password'])) {
      $usuario->password = Hash::make($datos['password']);
    }
    if (isset($datos['nombre_completo'])) {
      $usuario->nombre_completo = $datos['nombre_completo'];
    }
    if (isset($datos['id_rol'])) {
      $usuario->id_rol = $datos['id_rol'];
    }
    if (array_key_exists('id_cliente', $datos)) {
      $usuario->id_cliente = $datos['id_cliente'];
    }
    if (isset($datos['activo'])) {
      $usuario->activo = $datos['activo'];
      if ($datos['activo'] && !$usuario->email_verified_at) {
        $usuario->email_verified_at = now();
      }
    }
    $usuario->save();
    return $usuario->fresh(['role', 'cliente']);
  }

  /**
   * Eliminar (soft delete) un usuario.
   */
  public function eliminar(User $usuario): bool
  {
    if (Auth::id() === $usuario->id) {
      throw new \Exception("No puedes eliminar tu propia cuenta de esta forma.");
    }
    return $usuario->delete(); // Soft delete
  }
}
