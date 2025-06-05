<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEncuestaRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!$this->user()) return false;  // Si no está autenticado, no puede actualizar

        $encuesta = $this->route('encuesta'); // Obtener la encuesta del route model binding
        return $this->user()->can('update', $encuesta); // Delegar al policy correspondiente
    }

    public function rules(): array
    {
        return [
            'nombre' => 'sometimes|required|string|max:100',
            'descripcion' => 'nullable|string|max:2000',
            // id_cliente no se actualiza usualmente, se maneja al crear.
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la encuesta es obligatorio.',
            'nombre.string'   => 'El nombre debe ser un texto válido.',
            'nombre.max'      => 'El nombre no puede exceder 100 caracteres.',
            'descripcion.string' => 'La descripción debe ser un texto válido.',
            'descripcion.max' => 'La descripción no puede exceder los 2000 caracteres.',
        ];
    }
}
