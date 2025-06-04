<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $m) {
        //    statefulApi() == añade \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful
        //    a las rutas que usen el middleware “api” (antes se hacía en el Kernel).
        // $m->statefulApi(); // Para que se pueda usar el estado de la sesión en las API
        // $m->throttleApi(); // Para limitar el número de peticiones a la API
        // $m->validateCsrfTokens(); // Para validar los tokens CSRF
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
