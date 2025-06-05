<?php

namespace App\Services;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage; // Para manejar archivos de logo
use Illuminate\Support\Facades\DB; // Para manejar transacciones
use Illuminate\Database\QueryException; // Para manejar excepciones de SQL
use Illuminate\Http\UploadedFile;

class ClienteService
{
  /**
   * Obtener todos los clientes.
   * Aplicar filtros o paginación si es necesario más adelante.
   */
  public function obtenerTodos(): Collection
  {
    // En un caso real, podrías querer paginar aquí
    return Cliente::orderBy('razon_social')->get();
  }

  /**
   * Obtener un cliente por su ID.
   */
  public function obtenerPorId(int $idCliente): ?Cliente
  {
    return Cliente::findOrFail($idCliente);
  }

  /**
   * Crear un nuevo cliente.
   * @param array $datos Validados desde el FormRequest.
   * @return Cliente
   * @throws \Exception Si el alias ya existe.
   */
  public function crear(array $datos): Cliente
  {
    // El Trait Auditable debería manejar usuario_registro_id si hay un usuario logueado.
    // Si no, y es un seeder o un proceso sin Auth, se asignaría aquí o quedaría null.
    // $datos['usuario_registro_id'] = Auth::id(); // Solo si el Trait no lo cubre o no hay Auth
    return DB::transaction(function () use ($datos) {
      return Cliente::create($datos);
    });
  }

  /**
   * Actualizar un cliente existente.
   * @param Cliente $cliente El modelo Cliente a actualizar.
   * @param array $datos Validados desde el FormRequest.
   * @return Cliente
   * @throws \Exception Si el nuevo alias ya existe en otro cliente.
   */
  public function actualizar(Cliente $cliente, array $datos): Cliente
  {
    try {
      // Simplemente intentamos el update. Si hay violación de UNIQUE(alias), caerá en el catch.
      $cliente->update($datos);
      return $cliente->fresh(); // Refrescamos para asegurarnos de devolver el modelo exactamente como quedó en BD
    } catch (QueryException $e) {
      if ($e->getCode() === '23000') { // 23000 = código SQLSTATE para violación de clave única en MySQL/InnoDB
        throw new \Exception("El alias '{$datos['alias']}' ya está en uso por otro cliente.");
      }
      throw $e; // Si no es un violation-unique, re-lanzamos la excepción original
    }

    // El Trait Auditable debería manejar usuario_modificacion_id
    // $datos['usuario_modificacion_id'] = Auth::id();
  }

  /**
   * Eliminar (soft delete) un cliente.
   * @param Cliente $cliente
   * @return bool
   */
  public function eliminar(Cliente $cliente): bool
  {
    // El Trait Auditable debería manejar usuario_eliminacion_id
    // $cliente->usuario_eliminacion_id = Auth::id();
    // $cliente->save(); // Para guardar el usuario_eliminacion_id antes del delete
    return $cliente->delete(); // Esto ejecutará el soft delete
  }

  /**
   * Guarda el archivo de logo para el cliente y actualiza la ruta en BD. 
   * 
   * @param  Cliente       $cliente
   * @param  UploadedFile  $file     // el archivo validado desde el form
   * @return string        $path     // la ruta relativa (ej. "logos_clientes/42/logo.png")
   *  */
  public function actualizarLogo(Cliente $cliente, UploadedFile $archivoLogo): string
  {
    // Eliminar logo anterior si existe
    if ($cliente->ruta_logo && Storage::disk('public')->exists($cliente->ruta_logo)) {
      Storage::disk('public')->delete($cliente->ruta_logo);
    }

    // Guardar el nuevo logo
    // El path será algo como 'logos_clientes/1/nombre_aleatorio.jpg'
    $ruta = $archivoLogo->store('logos_clientes/' . $cliente->id_cliente, 'public');

    $cliente->ruta_logo = $ruta;
    // El Trait Auditable debería manejar usuario_modificacion_id
    // $cliente->usuario_modificacion_id = Auth::id();
    $cliente->save();

    return $ruta; // Retornar la ruta (p. ej. "logos_clientes/42/4a6f7c3e9d8b.jpg")
  }
}
