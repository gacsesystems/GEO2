<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use App\Models\Cliente;
use App\Policies\ClientePolicy;
use App\Models\Encuesta;
use App\Policies\EncuestaPolicy;
use App\Models\SeccionEncuesta;
use App\Policies\SeccionEncuestaPolicy;
use App\Models\Pregunta;
use App\Policies\PreguntaPolicy;
use App\Models\OpcionPregunta;
use App\Policies\OpcionPreguntaPolicy;
use App\Models\Parametros;
use App\Policies\ParametrosPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
  protected $policies = [
    // Aquí asocias el modelo Cliente con ClientePolicy
    User::class => UserPolicy::class,
    Cliente::class => ClientePolicy::class,
    Encuesta::class => EncuestaPolicy::class,
    SeccionEncuesta::class => SeccionEncuestaPolicy::class,
    Pregunta::class => PreguntaPolicy::class,
    OpcionPregunta::class => OpcionPreguntaPolicy::class,
    Parametros::class => ParametrosPolicy::class,
  ];

  public function boot(): void
  {
    $this->registerPolicies();

    // Opcional: si quieres una regla global para administradores “full access”:
    Gate::before(function ($user, $ability) {
      if ($user->esRol('administrador')) {
        return true;
      }
    });

    // Aquí puedes definir Gates si necesitas lógica de autorización más granular
    // que no se ajuste bien a una Policy basada en modelo.
    // Gate::define('ver-panel-administracion', function (User $user) {
    //     return $user->esRol('administrador');
    // });

    Gate::define('viewSwaggerUI', function ($user = null) {
      if (app()->environment('local')) return true; // Si es local, permitir siempre

      // Solo usuarios autenticados que sean administradores pueden ver Swagger
      // Si $user es null (no autenticado), esto devolverá false.
      return $user && $user->esRol('administrador');
      // O si quieres permitir a cualquier usuario autenticado:
      // return $user !== null;
      // O si quieres permitirlo en local pero no en producción:
      // return app()->environment('local') || ($user && $user->esRol('administrador'));
    });
  }
}
