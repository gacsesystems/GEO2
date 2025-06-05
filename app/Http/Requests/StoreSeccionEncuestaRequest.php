<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Encuesta;
use Illuminate\Auth\Access\AuthorizationException;

class StoreSeccionEncuestaRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = $this->user();

        if (! $user) return false;

        /** @var Encuesta $encuesta */
        $encuesta = $this->route('encuesta'); // Recupera la encuesta padre a través de route model binding

        if (! $encuesta instanceof Encuesta) return false;

        if (! $user->can('create', $encuesta)) {
            throw new AuthorizationException('No autorizado para añadir sección a esta encuesta.');
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100',
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
