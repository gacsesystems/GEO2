<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Pregunta;

class StoreOpcionPreguntaRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Pregunta|null $pregunta */
        $pregunta = $this->route('pregunta');

        if (! $pregunta instanceof Pregunta) return false; // Si no hay Pregunta por binding, no autorizamos

        return $this->user()?->can('create', $pregunta); // Laravel disparará 403 si devuelve false
    }

    public function rules(): array
    {
        return [
            'texto_opcion' => 'required|string|max:255',
            'valor_opcion' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'texto_opcion.required' => 'El texto de la opción es obligatorio.',
            'texto_opcion.max' => 'El texto de la opción no puede exceder los 255 caracteres.',
            'valor_opcion.max' => 'El valor de la opción no puede exceder los 100 caracteres.',
        ];
    }

    // Si quieres personalizar la respuesta cuando la autorización falla debido al tipo de pregunta:
    // protected function failedAuthorization()
    // {
    //     /** @var Pregunta $pregunta */
    //     $pregunta = $this->route('pregunta');
    //     if ($pregunta && !$pregunta->tipoPregunta?->requiere_opciones) {
    //         throw \Illuminate\Validation\ValidationException::withMessages([
    //             'id_tipo_pregunta' => ["El tipo de pregunta actual ('{$pregunta->tipoPregunta?->nombre}') no permite añadir opciones."],
    //         ])->status(422); // Unprocessable Entity
    //     }
    //     parent::failedAuthorization(); // Lanza la excepción 403 por defecto
    // }
}
