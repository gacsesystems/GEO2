<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use \Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Middleware\ValidatePostSize;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $m) {
        // $m->use([
        //     HandleCors::class, // 1) Registra CORS de Fruitcake a nivel global
        //     // 2) Registrar los middleware “imprescindibles” que antes estaban en app/Http/Kernel.php
        //     TrustProxies::class, // a) TrustProxies (para detectar correctamente IPs al usar proxys/reverse proxies)
        //     ValidatePostSize::class, // b) ValidatePostSize (limita tamaño máximo de POST según configuración)
        //     PreventRequestsDuringMaintenance::class, // c) PreventRequestsDuringMaintenance (mostrar página de “mantenimiento” si está activa)
        //     ConvertEmptyStringsToNull::class, // d) ConvertEmptyStringsToNull (convierte campos vacíos a null automáticamente)
        // ]);

        //
        // 1) Registrar CORS como middleware GLOBAL
        //    Para Laravel 12, se utiliza appendToGroup o prependToGroup en 
        //    lugar de un método “add” directo.
        //
        // Agrega HandleCors al grupo “web” y al grupo “api”:
        $m->prependToGroup('web', HandleCors::class);
        $m->prependToGroup('api', HandleCors::class);

        //
        // 2) Otros middleware globales (mantente como los tenías)
        //
        $m->prependToGroup('web', TrustProxies::class);
        $m->prependToGroup('web', ValidatePostSize::class);
        $m->prependToGroup('web', PreventRequestsDuringMaintenance::class);
        $m->prependToGroup('web', ConvertEmptyStringsToNull::class);

        //    statefulApi() == añade \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful
        //    a las rutas que usen el middleware “api” (antes se hacía en el Kernel).
        $m->statefulApi(); // Para que se pueda usar el estado de la sesión en las API
        // $m->throttleApi(); // Para limitar el número de peticiones a la API
        // $m->validateCsrfTokens(); // Para validar los tokens CSRF
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
