<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTipoPreguntaRequest extends FormRequest
{
  public function authorize(): bool
  {
    // Sólo admin
    return Auth::check() && Auth::user()->esRol('Administrador');
  }

  public function rules(): array
  {
    return [
      'nombre'                => 'required|string|max:100|unique:tipos_pregunta,nombre',
      'descripcion'           => 'nullable|string|max:255',
      'requiere_opciones'      => 'sometimes|boolean',
      'permite_min_max_numerico' => 'sometimes|boolean',
      'permite_min_max_fecha'   => 'sometimes|boolean',
      'es_seleccion_multiple'   => 'sometimes|boolean',
    ];
  }

  public function messages(): array
  {
    return [
      'nombre.required' => 'El nombre del tipo de pregunta es obligatorio.',
      'nombre.unique'   => 'Ya existe un tipo de pregunta con ese nombre.',
      // …otros mensajes si quieres…
    ];
  }
}
