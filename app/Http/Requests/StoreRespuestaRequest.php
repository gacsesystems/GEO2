<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRespuestaRequest extends FormRequest
{
  public function authorize(): bool
  {
    // Si permites que cualquiera responda, retorna true.
    // Si sólo clientes autenticados pueden responder, cambia a:
    // return Auth::check() && Auth::user()->esRol('Cliente');
    return true;
  }

  public function rules(): array
  {
    return [
      'id_encuesta'               => ['required', 'integer', 'exists:encuestas,id_encuesta'],
      'paciente_id'               => ['nullable', 'integer'],
      'correo_respuesta'          => ['nullable', 'email', 'max:255'],
      'fecha_inicio_respuesta'    => ['nullable', 'date_format:Y-m-d H:i:s'],
      'fecha_fin_respuesta'       => ['nullable', 'date_format:Y-m-d H:i:s'],
      'respuestas'                => ['required', 'array', 'min:1'],
      'respuestas.*.id_pregunta'          => ['required', 'integer', Rule::exists('preguntas', 'id_pregunta'),],
      'respuestas.*.valor_respuesta'      => ['nullable'], // Permitimos string, num, fecha, booleano según tipo
      'respuestas.*.valor_texto'  => 'sometimes|nullable|string|max:4000',
      'respuestas.*.valor_numerico' => 'sometimes|nullable|numeric',
      'respuestas.*.valor_fecha'    => 'sometimes|nullable|date',
      'respuestas.*.valor_booleano' => 'sometimes|nullable|boolean',
      'respuestas.*.id_opcion_seleccionada_unica' => [
        'sometimes',
        'nullable',
        'integer',
        Rule::exists('opciones_pregunta', 'id_opcion_pregunta'),
      ],
      'respuestas.*.ids_opciones_seleccionadas' => ['nullable', 'array'],
      'respuestas.*.ids_opciones_seleccionadas.*' => [
        'integer',
        Rule::exists('opciones_pregunta', 'id_opcion_pregunta')
      ],
      // Si deseas validar por separado campos numéricos, booleanos, etc.  
      // puedes usar reglas condicionales en el RespuestasService.
    ];
  }

  public function messages(): array
  {
    return [
      'id_encuesta.required' => 'Debe indicar a qué encuesta responden.',
      'id_encuesta.exists'   => 'La encuesta indicada no existe.',
      'respuestas.required'  => 'Debe enviar al menos una respuesta.',
      'respuestas.*.id_pregunta.exists' => 'Alguna de las preguntas no pertenece a esta encuesta.',
      'respuestas.*.ids_opciones_seleccionadas.*.exists' => 'Alguna opción seleccionada no es válida.',
    ];
  }
}
