<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\OpcionPregunta;

class ReordenarOpcionPreguntaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Similar a UpdateOpcionPreguntaRequest
        return true; // Simplificado, autorización real en el controlador/servicio
    }

    public function rules(): array
    {
        /** @var OpcionPregunta $opcion */
        $opcion = $this->route('opcion');
        $maxOrden = $opcion->pregunta->opcionesPregunta()->count();
        return [
            'nuevo_orden' => "required|integer|min:1|max:$maxOrden",
        ];
    }

    public function messages(): array
    {
        /** @var OpcionPregunta $opcion */
        $opcion  = $this->route('opcion');
        $maxOrden = $opcion ? $opcion->pregunta->opcionesPregunta()->count() : 1;

        return [
            'nuevo_orden.required' => 'Debes indicar la nueva posición de la opción.',
            'nuevo_orden.min'      => 'El nuevo orden debe ser al menos 1.',
            'nuevo_orden.max'      => "El nuevo orden no puede exceder $maxOrden.",
        ];
    }
}
