<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Para la autorización
use App\Models\Cliente;


class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo administradores pueden crear clientes, por ejemplo
        // Verificar que el usuario esté autenticado y tenga el rol de administrador
        if (!Auth::check()) return false;

        // 2) Invocar la policy “create” para el modelo Cliente
        //    Esto llamará a ClientePolicy::create(Auth::user())
        return $this->user()->can('create', Cliente::class);
    }

    public function rules(): array
    {
        return [
            'razon_social' => 'required|string|max:150',
            'alias' => 'required|string|max:50|unique:clientes,alias', // unique en la tabla clientes, columna alias
            'ruta_logo' => 'nullable|string|max:255', // O 'image|mimes:jpg,jpeg,png|max:2048' si subes el archivo directamente
            'activo' => 'sometimes|boolean', // sometimes: si no se proporciona, se asume true
            'limite_encuestas' => 'sometimes|integer|min:0', // si no se proporciona, se asume 0
            'vigencia' => 'nullable|date|after_or_equal:today',
        ];
    }

    public function messages(): array // Mensajes en español
    {
        return [
            'razon_social.required'     => 'La razón social es obligatoria.',
            'alias.required'            => 'El alias es obligatorio.',
            'alias.unique'              => 'Este alias ya ha sido registrado.',
            'alias.max'                 => 'El alias no puede exceder 50 caracteres.',
            'ruta_logo.max'             => 'La ruta del logo no puede exceder 255 caracteres.',
            'activo.boolean'            => 'El campo “activo” debe ser verdadero o falso.',
            'limite_encuestas.integer'  => 'El límite de encuestas debe ser un número entero.',
            'limite_encuestas.min'      => 'El límite de encuestas debe ser al menos 0.',
            'vigencia.date'             => 'La vigencia debe ser una fecha válida.',
            'vigencia.after_or_equal'   => 'La vigencia no puede ser anterior a hoy.',
        ];
    }
}
