<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResumenPorPreguntaRequest extends FormRequest
{
  public function authorize(): bool
  {
    // Permitir que el controller llame a $this->authorize('viewReport', $encuesta)
    return true;
  }

  public function rules(): array
  {
    return [
      'fecha_desde' => ['nullable', 'date_format:Y-m-d'],
      'fecha_hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:fecha_desde'],
    ];
  }
}
