<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Pregunta; // Para obtener la pregunta y su sección

class ReordenarPreguntaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Similar a UpdatePreguntaRequest, verificar que el usuario puede editar la encuesta/sección
        return true; // Simplificado, la autorización real está en el controlador
    }

    public function rules(): array
    {
        /** @var Pregunta $pregunta */
        $pregunta = $this->route('pregunta');
        $maxOrden = $pregunta->seccionEncuesta->preguntas()->count();
        return ['nuevo_orden' => "required|integer|min:1|max:$maxOrden",];
    }
    public function messages(): array
    {
        return [
            'nuevo_orden.required' => 'Debes indicar la nueva posición de la pregunta.',
            'nuevo_orden.min'      => 'El nuevo orden debe ser al menos 1.',
            'nuevo_orden.max'      => "El nuevo orden no puede exceder {$this->route('pregunta')->seccionEncuesta->preguntas()->count()}.",
        ];
    }
}
