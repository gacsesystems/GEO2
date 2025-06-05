<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UploadLogoClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! Auth::check()) return false; // 1) El usuario debe estar autenticado

        // 2) Obtener la instancia de Cliente mediante Route Model Binding
        $cliente = $this->route('cliente'); // Laravel ya resolverá {cliente} a un App\Models\Cliente

        if (! $cliente) return false;


        // 3) Invocar la policy “update” para Cliente
        //    Esto internamente llamará a ClientePolicy::update(Auth::user(), $cliente)
        return $this->user()->can('update', $cliente);
    }

    public function rules(): array
    {
        return [
            // El campo “logo” debe existir, ser una imagen y cumplir con los límites
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'logo.required'    => 'Debes escoger un archivo de imagen para el logo.',
            'logo.image'       => 'El archivo subido debe ser una imagen.',
            'logo.mimes'       => 'El logo debe ser un archivo JPEG, PNG, JPG, GIF o SVG.',
            'logo.max'         => 'El logo no puede exceder los 2 MB de tamaño.',
        ];
    }
}
