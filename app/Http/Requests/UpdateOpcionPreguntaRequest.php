<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\OpcionPregunta;

class UpdateOpcionPreguntaRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var OpcionPregunta $opcion */
        $opcion = $this->route('opcionPregunta');
        return $opcion && Auth::check() && Auth::user()->can('update', $opcion);
    }

    public function rules(): array
    {
        return [
            'texto_opcion' => 'sometimes|required|string|max:255',
            'valor_opcion' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'texto_opcion.required' => 'El texto de la opción es obligatorio.',
            'texto_opcion.max'      => 'El texto de la opción no puede exceder 255 caracteres.',
            'valor_opcion.max'      => 'El valor de la opción no puede exceder 100 caracteres.',
        ];
    }
}
