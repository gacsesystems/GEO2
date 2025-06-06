<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->esRol('Administrador'); // Solo administradores
    }

    public function rules(): array
    {
        $rolClienteId = Role::where('nombre_rol', 'Cliente')->value('id_rol');

        return [
            'nombre_completo'         => 'required|string|max:150',
            'email'                   => 'required|email|max:80|unique:usuarios,email',
            'password'                => 'required|string|min:8|confirmed',
            'id_rol'                  => 'required|exists:roles,id_rol',
            'id_cliente'              => [
                'nullable',
                'required_if:id_rol,' . $rolClienteId,
                'exists:clientes,id_cliente'
            ],
            'activo'                  => 'sometimes|boolean',
        ];
    }


    public function messages(): array
    {
        return [
            'nombre_completo.required' => 'El nombre completo es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'id_rol.required' => 'El rol es obligatorio.',
            'id_rol.exists' => 'El rol seleccionado no es válido.',
            'id_cliente.required_if' => 'El campo "cliente" es obligatorio cuando el rol es Cliente.',
            'id_cliente.exists' => 'El cliente seleccionado no es válido.',
        ];
    }
}
