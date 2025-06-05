<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Para reglas de unicidad en actualización
use Illuminate\Support\Facades\Auth;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo administradores o quizás el propio cliente (si tuviera un panel)
        if (!Auth::check()) return false;

        // 2) Obtener la instancia de Cliente desde la ruta
        //    (Route Model Binding debe resolver {cliente} a un Cliente)
        $cliente = $this->route('cliente');

        // 3) Invocar la policy directamente
        //    Esto llamará a ClientePolicy::update(Auth::user(), $cliente)
        return $this->user()->can('update', $cliente);
    }

    public function rules(): array
    {
        $clienteId = $this->route('cliente') ? $this->route('cliente')->id_cliente : null; // Obtener ID del cliente de la ruta

        return [
            'razon_social' => 'required|string|max:150',
            'alias' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('clientes', 'alias')->ignore($clienteId, 'id_cliente') // Ignorar el cliente actual al validar unicidad
            ],
            'ruta_logo' => 'nullable|string|max:255',
            'activo' => 'sometimes|boolean',
            'limite_encuestas' => 'sometimes|integer|min:0',
            'vigencia' => 'nullable|date', // Podrías quitar 'after_or_equal:today' si se permite actualizar a fechas pasadas
        ];
    }
    // Puedes añadir messages() aquí también
}
