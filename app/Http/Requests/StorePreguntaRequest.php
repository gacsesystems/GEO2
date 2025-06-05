<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\SeccionEncuesta;
use Illuminate\Validation\Rule; // Para reglas más complejas como exists con where
use Illuminate\Auth\Access\AuthorizationException;

class StorePreguntaRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = $this->user();
        if (! $user) return false;

        // Obtener la sección de la ruta
        $seccion = $this->route('seccion');
        if (! $seccion instanceof SeccionEncuesta) return false;

        // Verificar permiso via PreguntaPolicy@create
        if (! $user->can('create', $seccion)) {
            throw new AuthorizationException('No autorizado para crear preguntas en esta sección.');
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        /** @var SeccionEncuesta $seccion */
        $seccion = $this->route('seccion');
        $idEncuesta = $seccion->id_encuesta;

        return [
            'texto_pregunta' => ['required', 'string', 'max:500'],
            'id_tipo_pregunta' => ['required', 'integer', Rule::exists('tipos_pregunta', 'id_tipo_pregunta')],
            'es_obligatoria' => 'sometimes|boolean', // 'sometimes' significa que si no se envía, no se valida (y el modelo/BD puede tener un default) // Marca de pregunta obligatoria solo si se envía

            // --- Validaciones condicionales según propiedades del tipo ---
            // Para saber si el tipo requiere min/max numérico o fecha,
            // asumimos que el frontend envía estas banderas en el payload:
            //   'tipo_requiere_min_max_numerico' => true|false
            //   'tipo_requiere_min_max_fecha'   => true|false
            //   'tipo_requiere_opciones'        => true|false
            'numero_minimo' => ['nullable', 'integer', Rule::requiredIf(fn() => $this->input('tipo_requiere_min_max_numerico') === true),],
            'numero_maximo' => ['nullable', 'integer', 'gte:numero_minimo', Rule::requiredIf(fn() => $this->input('tipo_requiere_min_max_numerico') === true),],
            'fecha_minima' => ['nullable', 'date', Rule::requiredIf(fn() => $this->input('tipo_requiere_min_max_fecha') === true),],
            'fecha_maxima' => ['nullable', 'date', 'after_or_equal:fecha_minima', Rule::requiredIf(fn() => $this->input('tipo_requiere_min_max_fecha') === true),],
            // Rango de horas (si se requieren): formato HH:MM o HH:MM:SS
            'hora_minima' => ['nullable', 'date_format:H:i:s', 'before_or_equal:hora_maxima'],
            'hora_maxima' => ['nullable', 'date_format:H:i:s', 'after_or_equal:hora_minima'],
            'texto_ayuda' => ['nullable', 'string', 'max:255'], // Texto de ayuda opcional

            // --- Validación de pregunta padre (si es subpregunta) ---
            'id_pregunta_padre' => [
                'nullable',
                'integer',
                // Debe existir y pertenecer a alguna pregunta de la misma encuesta:
                Rule::exists('preguntas', 'id_pregunta')->where(
                    fn($query) =>
                    $query->whereIn('id_seccion', function ($subQuery) use ($idEncuesta) {
                        $subQuery->select('id_seccion')
                            ->from('secciones_encuesta')
                            ->where('id_encuesta', $idEncuesta);
                    })
                ),
            ],
            // Si Id pregunta padre está presente, el valor de condición debe venir
            'valor_condicion_padre' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn() => $this->filled('id_pregunta_padre')),],
            // Si la pregunta padre es de tipo “opción”, validar opción condicional
            'id_opcion_condicion_padre' => [
                'nullable',
                'integer',
                Rule::requiredIf(fn() => $this->filled('id_pregunta_padre') && $this->input('tipo_requiere_opciones') === true),
                Rule::exists('opciones_pregunta', 'id_opcion_pregunta')->where(
                    fn($query) =>
                    $query->where('id_pregunta', $this->input('id_pregunta_padre'))
                ),
            ],

            // --- Validación de opciones si el tipo lo requiere ---
            'opciones' => ['sometimes', 'array', Rule::requiredIf(fn() => $this->input('tipo_requiere_opciones') === true),],
            'opciones.*.texto_opcion' => ['required_with:opciones', 'string', 'max:255',],
            'opciones.*.valor_opcion' => ['nullable', 'string', 'max:100'],
        ];
    }

    // /**
    //  * Prepare the data for validation.
    //  *
    //  * @return void
    //  */
    // protected function prepareForValidation()
    // {
    //     $tipoPregunta = $this->id_tipo_pregunta ? TipoPregunta::find($this->id_tipo_pregunta) : null;

    //     $this->merge([
    //         'tipo_requiere_opciones' => $tipoPregunta && $tipoPregunta->requiere_opciones,
    //         'tipo_requiere_min_max_numerico' => $tipoPregunta && $tipoPregunta->permite_min_max_numerico,
    //         'tipo_requiere_min_max_fecha' => $tipoPregunta && $tipoPregunta->permite_min_max_fecha,
    //         'es_obligatoria' => $this->boolean('es_obligatoria'), // Asegurar que sea booleano
    //     ]);

    //     if ($this->filled('id_pregunta_padre')) {
    //         $preguntaPadre = Pregunta::with('tipoPregunta')->find($this->input('id_pregunta_padre'));
    //         if ($preguntaPadre && $preguntaPadre->tipoPregunta && $preguntaPadre->tipoPregunta->requiere_opciones) {
    //             $this->merge(['valor_condicion_padre_requiere_opcion' => true]);
    //         } else {
    //             $this->merge(['valor_condicion_padre_requiere_opcion' => false]);
    //         }
    //     } else {
    //         $this->merge(['valor_condicion_padre_requiere_opcion' => false]);
    //     }
    // }

    public function messages(): array
    {
        return [
            'texto_pregunta.required' => 'El texto de la pregunta es obligatorio.',
            'texto_pregunta.max' => 'El texto de la pregunta no puede tener más de 500 caracteres.',
            'id_tipo_pregunta.required' => 'El tipo de pregunta es obligatorio.',
            'id_tipo_pregunta.exists'       => 'El tipo de pregunta seleccionado no existe.',
            'numero_minimo.required_if' => 'El número mínimo es requerido para este tipo de pregunta.',
            'numero_maximo.required_if' => 'El número máximo es requerido para este tipo de pregunta.',
            'numero_maximo.gte' => 'El número máximo debe ser mayor o igual al mínimo.',
            'fecha_minima.required_if' => 'La fecha mínima es requerida para este tipo de pregunta.',
            'fecha_maxima.required_if' => 'La fecha máxima es requerida para este tipo de pregunta.',
            'fecha_maxima.after_or_equal' => 'La fecha máxima debe ser igual o posterior a la fecha mínima.',
            'hora_maxima.after_or_equal' => 'La hora máxima debe ser igual o posterior a la hora mínima.',
            'id_pregunta_padre.exists' => 'La pregunta padre seleccionada no es válida o no pertenece a la misma encuesta.',
            'valor_condicion_padre.required_with' => 'El valor de condición es obligatorio si se especifica una pregunta padre.',
            'id_opcion_condicion_padre.required_if' => 'La opción de condición padre es obligatoria si la pregunta padre es de un tipo que requiere opciones y se ha especificado un valor de condición.',
            'id_opcion_condicion_padre.exists' => 'La opción de condición padre seleccionada no pertenece a la pregunta padre especificada.',
            'opciones.required_if' => 'Este tipo de pregunta requiere que se definan opciones.',
            'opciones.*.texto_opcion.required_with' => 'El texto de cada opción es obligatorio cuando se envían opciones.',
        ];
    }
}
