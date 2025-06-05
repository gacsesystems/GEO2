<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulkOpcionPreguntaRequest extends FormRequest
{
  public function authorize(): bool
  {
    // Aquí puedes chequear “¿el usuario puede crear opciones en esta pregunta?”
    // Por simplicidad, devolvemos true y delegamos la autorización real al controller o Policy.
    return true;
  }

  public function rules(): array
  {
    return [
      'opciones'              => 'required|array|min:1',
      'opciones.*.texto_opcion' => 'required|string|max:255',
      'opciones.*.valor_opcion' => 'nullable|string|max:100',
      // Si quieres validar “orden” proveniente del front, podrías añadir:
      // 'opciones.*.orden'    => 'nullable|integer|min:1',
    ];
  }
}
