<?php

namespace App\Services;

use App\Models\TipoPregunta;
use Illuminate\Database\Eloquent\Collection;

class TipoPreguntaService
{
  public function all(): Collection
  {
    return TipoPregunta::all();
  }

  public function create(array $datos): TipoPregunta
  {
    return TipoPregunta::create($datos);
  }

  public function update(TipoPregunta $tipoPregunta, array $datos): TipoPregunta
  {
    $tipoPregunta->update($datos);
    return $tipoPregunta->refresh();
  }

  public function delete(TipoPregunta $tipoPregunta): bool
  {
    return $tipoPregunta->delete();
  }
}
