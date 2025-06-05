<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\SeccionEncuesta;
use Illuminate\Auth\Access\AuthorizationException;

class UpdateSeccionEncuestaRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = $this->user();

        if (! $user) return false;

        /** @var SeccionEncuesta $seccion */
        $seccion = $this->route('seccion'); // Recupera la sección padre a través de route model binding

        if (! $seccion instanceof SeccionEncuesta) return false; // Verifica que la sección sea una instancia de SeccionEncuesta

        if (! $user->can('update', $seccion)) {
            throw new AuthorizationException('No autorizado para actualizar esta sección.');
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'sometimes|required|string|max:100', // 'sometimes' permite que el campo sea opcional en el request
            'descripcion' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la sección es obligatorio.',
            'nombre.string'   => 'El nombre debe ser un texto válido.',
            'nombre.max'      => 'El nombre no puede exceder 100 caracteres.',
            'descripcion.string' => 'La descripción debe ser un texto válido.',
        ];
    }
}
