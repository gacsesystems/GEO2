<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Pregunta; // Para validar id_pregunta_padre
use Illuminate\Validation\Rule;
use Illuminate\Auth\Access\AuthorizationException;

class UpdatePreguntaRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = $this->user();
        if (! $user) return false;

        // Obtener la pregunta vía route model binding
        $pregunta = $this->route('pregunta');
        if (! $pregunta instanceof Pregunta) return false;

        // Verificar permiso via PreguntaPolicy@update
        if (! $user->can('update', $pregunta)) {
            throw new AuthorizationException('No autorizado para actualizar esta pregunta.');
        }

        return true;
    }

    public function rules(): array
    {
        /** @var Pregunta $pregunta */
        $pregunta = $this->route('pregunta');
        $seccion = $pregunta->seccionEncuesta; // A partir de la pregunta obtenemos su sección padre

        return [
            'texto_pregunta'   => ['sometimes', 'required', 'string', 'max:500'],
            'id_tipo_pregunta' => ['sometimes', 'required', 'integer', Rule::exists('tipos_pregunta', 'id_tipo_pregunta'),],
            'es_obligatoria'   => ['sometimes', 'boolean'], // Marca de obligatorio (opcional en el payload)
            // Si el frontend indica que el tipo requiere rangos numéricos:
            'numero_minimo'    => ['nullable', 'integer', 'min:0', Rule::requiredIf(fn() => $this->input('tipo_requiere_min_max_numerico') === true),],
            'numero_maximo'    => ['nullable', 'integer', 'min:0', 'gte:numero_minimo', Rule::requiredIf(fn() => $this->input('tipo_requiere_min_max_numerico') === true),],
            // Si el frontend indica que el tipo requiere rangos de fechas:
            'fecha_minima'     => ['nullable', 'date', Rule::requiredIf(fn() => $this->input('tipo_requiere_min_max_fecha') === true),],
            'fecha_maxima'     => ['nullable', 'date', 'after_or_equal:fecha_minima', Rule::requiredIf(fn() => $this->input('tipo_requiere_min_max_fecha') === true),],
            // Si el tipo requiere rango de horas (se asume la bandera enviada:
            // 'tipo_requiere_rango_hora' => true/false)
            'hora_minima'      => ['nullable', 'date_format:H:i:s', Rule::requiredIf(fn() => $this->input('tipo_requiere_rango_hora') === true),],
            'hora_maxima'      => ['nullable', 'date_format:H:i:s', 'after_or_equal:hora_minima', Rule::requiredIf(fn() => $this->input('tipo_requiere_rango_hora') === true),],

            'texto_ayuda'      => ['nullable', 'string', 'max:255'], // Texto de ayuda libre
            // Validación de pregunta padre (si se pretende reasignar)
            'id_pregunta_padre' => [
                'nullable',
                'integer',
                // 1) Debe existir en la tabla “preguntas” y
                //    pertenecer a la misma encuesta (mismo id_encuesta),
                //    y NO puede ser sí misma
                Rule::exists('preguntas', 'id_pregunta')->where(
                    fn($query) =>
                    $query->whereIn('id_seccion', function ($sub) use ($seccion) {
                        $sub->select('id_seccion')
                            ->from('secciones_encuesta')
                            ->where('id_encuesta', $seccion->id_encuesta);
                    })->where('id_pregunta', '!=', $pregunta->id_pregunta)
                ),
            ],
            'valor_condicion_padre' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn() => $this->filled('id_pregunta_padre')),
            ],
            // Validación de la opción condicional, solo si el padre es de tipo “opción”
            'id_opcion_condicion_padre' => [
                'nullable',
                'integer',
                // 1) Requerido si “id_pregunta_padre” está presente y esa pregunta padre
                //    también requiere opciones (flag enviado: tipo_requiere_opciones_padre)
                Rule::requiredIf(
                    fn() =>
                    $this->filled('id_pregunta_padre')
                        && $this->input('tipo_requiere_opciones_padre') === true
                ),
                // 2) Debe existir en “opciones_pregunta” y pertenecer a la pregunta padre
                Rule::exists('opciones_pregunta', 'id_opcion_pregunta')->where(
                    fn($query) =>
                    $query->where('id_pregunta', $this->input('id_pregunta_padre'))
                ),
            ],
            // Validación de array de opciones si el tipo las requiere
            'opciones' => ['sometimes', 'array', Rule::requiredIf(fn() => $this->input('tipo_requiere_opciones') === true),],
            'opciones.*.texto_opcion' => [
                'required_with:opciones',
                'string',
                'max:255',
            ],
            'opciones.*.valor_opcion' => ['nullable', 'string', 'max:100'],
        ];
    }

    // protected function prepareForValidation()
    // {
    //     // Determinar el tipo de pregunta actual o el que se está intentando establecer
    //     $tipoPreguntaId = $this->id_tipo_pregunta ?? $this->route('pregunta')?->id_tipo_pregunta;
    //     $tipoPregunta = $tipoPreguntaId ? TipoPregunta::find($tipoPreguntaId) : null;

    //     $this->merge([
    //         'tipo_requiere_min_max_numerico_update' => $tipoPregunta && $tipoPregunta->permite_min_max_numerico && ($this->filled('numero_minimo') || $this->filled('numero_maximo')),
    //         'tipo_requiere_min_max_fecha_update' => $tipoPregunta && $tipoPregunta->permite_min_max_fecha && ($this->filled('fecha_minima') || $this->filled('fecha_maxima')),
    //     ]);

    //     if ($this->id_pregunta_padre) {
    //         $preguntaPadre = Pregunta::with('tipoPregunta')->find($this->id_pregunta_padre);
    //         if ($preguntaPadre && $preguntaPadre->tipoPregunta && $preguntaPadre->tipoPregunta->requiere_opciones) {
    //             $this->merge(['valor_condicion_padre_requiere_opcion_update' => true]);
    //         }
    //     }
    // }

    public function messages(): array
    {
        return [
            'texto_pregunta.required'          => 'El texto de la pregunta es obligatorio.',
            'texto_pregunta.max'               => 'El texto no puede exceder 500 caracteres.',
            'id_tipo_pregunta.required'        => 'Debe seleccionar un tipo de pregunta.',
            'id_tipo_pregunta.exists'          => 'El tipo de pregunta seleccionado no existe.',
            'numero_minimo.required_if'        => 'Debe indicar el número mínimo para este tipo de pregunta.',
            'numero_maximo.required_if'        => 'Debe indicar el número máximo para este tipo de pregunta.',
            'numero_maximo.gte'                => 'El número máximo debe ser mayor o igual al mínimo.',
            'fecha_minima.required_if'         => 'Debe indicar la fecha mínima para este tipo de pregunta.',
            'fecha_maxima.required_if'         => 'Debe indicar la fecha máxima para este tipo de pregunta.',
            'fecha_maxima.after_or_equal'      => 'La fecha máxima debe ser posterior o igual a la fecha mínima.',
            'hora_minima.required_if'          => 'Debe indicar la hora mínima para este tipo de pregunta.',
            'hora_maxima.required_if'          => 'Debe indicar la hora máxima para este tipo de pregunta.',
            'hora_maxima.after_or_equal'       => 'La hora máxima debe ser posterior o igual a la hora mínima.',
            'valor_condicion_padre.required_if' => 'Debe indicar un valor de condición si hay pregunta padre.',
            'id_opcion_condicion_padre.required_if' => 'Debe seleccionar una opción de condición para la pregunta padre.',
            'id_opcion_condicion_padre.exists'      => 'La opción seleccionada no corresponde a la pregunta padre.',
            'opciones.required_if'             => 'Este tipo de pregunta requiere que definas al menos una opción.',
            'opciones.*.texto_opcion.required_with' => 'Cada opción debe tener un texto descriptivo.',
            'opciones.*.texto_opcion.max'       => 'El texto de la opción no puede exceder 255 caracteres.',
        ];
    }
}
