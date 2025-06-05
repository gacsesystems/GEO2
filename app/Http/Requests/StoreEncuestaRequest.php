<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Cliente; // Para validar límite de encuestas
use App\Models\Encuesta;
use Illuminate\Auth\Access\AuthorizationException;

class StoreEncuestaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user) return false; // Si no está autent  icado, no puede crear

        if ($user->esRol('Administrador')) return true; // Admin puede crear para cualquier cliente (si se provee id_cliente)

        if ($user->esRol('Cliente') && $user->id_cliente) {
            // Validar límite de encuestas para el cliente
            $cliente = Cliente::select('id_cliente', 'limite_encuestas')
                ->withCount('encuestas')  // asume relación encuestas() definida en Cliente
                ->find($user->id_cliente);

            if (!$cliente) return false; // Si el cliente no existe, no puede crear
            // Si hay un límite (> 0), compararlo con el conteo actual
            if ($cliente->limite_encuestas > 0 && $cliente->encuestas_count >= $cliente->limite_encuestas) {
                throw new AuthorizationException('Ha alcanzado el límite de encuestas permitidas para su cuenta.');
            }
            return true;
        }
        return $this->user()->can('create', Encuesta::class); // Para cualquier otro rol, delegar a Policy (puede retornar false)
    }

    public function rules(): array
    {
        $rules = [
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:2000', // O el límite que prefieras
        ];

        // Si el usuario es administrador, el id_cliente es requerido para saber a quién asignarla
        if (Auth::user()?->esRol('Administrador')) {
            $rules['id_cliente'] = 'required|integer|exists:clientes,id_cliente';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nombre.required'     => 'El nombre de la encuesta es obligatorio.',
            'nombre.string'       => 'El nombre debe ser un texto válido.',
            'nombre.max'          => 'El nombre no puede exceder 100 caracteres.',
            'descripcion.string'  => 'La descripción debe ser un texto válido.',
            'descripcion.max' => 'La descripción no puede exceder los 2000 caracteres.',
            'id_cliente.required' => 'Debe especificar un cliente para la encuesta (solo administradores).',
            'id_cliente.exists' => 'El cliente especificado no existe.',
        ];
    }
}
