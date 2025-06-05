<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateTipoPreguntaRequest extends FormRequest
{
  public function authorize(): bool
  {
    return Auth::check() && Auth::user()->esRol('administrador');
  }

  public function rules(): array
  {
    $tipoId = $this->route('tipoPregunta')->id_tipo_pregunta;
    return [
      'nombre'        => "required|string|max:100|unique:tipo_pregunta,nombre,$tipoId,id_tipo_pregunta",
      'descripcion'   => 'nullable|string|max:255',
      'requiere_opciones'       => 'sometimes|boolean',
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
    ];
  }
}
