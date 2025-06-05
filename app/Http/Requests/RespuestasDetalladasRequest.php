<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RespuestasDetalladasRequest extends FormRequest
{
  public function authorize(): bool
  {
    // La autorización real se hará en el controller vía Policy
    return true;
  }

  public function rules(): array
  {
    return [
      'fecha_desde' => ['nullable', 'date_format:Y-m-d'],
      'fecha_hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:fecha_desde'],
      'correo'      => ['nullable', 'string', 'email', 'max:255'],
    ];
  }
}
