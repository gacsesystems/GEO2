<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\SeccionEncuesta;

class ReordenarSeccionEncuestaRequest extends FormRequest
{
  public function authorize(): bool
  {
    $seccion = $this->route('seccion_encuesta'); // Laravel inyectó SeccionEncuesta $seccion vía route model binding anidado

    // Llama a SeccionEncuestaPolicy::reordenar(User $user, SeccionEncuesta $seccion)
    return $this->user()->can('reordenar', $seccion);
  }

  public function rules(): array
  {
    /** @var SeccionEncuesta $seccion */
    $seccion = $this->route('seccion_encuesta');
    // Cuenta cuántas secciones hay en esa encuesta (soft‐deleted no cuentan por defecto)
    $maxOrden = $seccion->encuesta()
      ->first()            // carga la encuesta padre
      ->seccionesEncuesta() // relación “hasMany”
      ->count();

    return [
      'nuevo_orden' => "required|integer|min:1|max:$maxOrden",
    ];
  }

  public function messages(): array
  {
    return [
      'nuevo_orden.required' => 'El nuevo orden es obligatorio.',
      'nuevo_orden.integer' => 'El nuevo orden debe ser un número.',
      'nuevo_orden.min' => 'El nuevo orden debe ser al menos 1.',
      'nuevo_orden.max' => 'El nuevo orden no puede ser mayor que el número total de secciones.',
    ];
  }
}
