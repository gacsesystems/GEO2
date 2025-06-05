<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EncuestaController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\SeccionEncuestaController;
use App\Http\Controllers\PreguntaController;
use App\Http\Controllers\OpcionPreguntaController;
use App\Http\Controllers\RespuestasController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\TipoPreguntaController;
// ————————————————————————————————————————————————————————————————————
// 1) CSRF + registro / login (sin auth)
// ————————————————————————————————————————————————————————————————————
/** 
 *  Al llamar a esta ruta, Sanctum devolverá las cookies 
 *  laravel_session y XSRF-TOKEN para que el SPA maneje CSRF. 
 */
Route::get('/sanctum/csrf-cookie', function () {
    return response()->noContent();
});
// Nota: Cuando tu SPA llame a /sanctum/csrf-cookie, Laravel devolverá 204 No Content junto con las cookies laravel_session y XSRF-TOKEN. Después, tu SPA “recogerá” ese XSRF-TOKEN y lo colocará automáticamente en el encabezado X-XSRF-TOKEN para cada petición subsiguiente (si usas axios.defaults.withCredentials = true).

/** 
 *  Opcional: si permites que cualquiera se “autoregistre” vía API. 
 *  Si no usas este endpoint, bórralo. 
 */
Route::post('/register', [AuthController::class, 'register'])->name('api.register'); // opcional si quieres permitir self-registration

Route::post('/login', [AuthController::class, 'login'])->name('api.login'); //Sirve para iniciar sesión

// ————————————————————————————————————————————————————————————————————
// 2) RUTAS PROTEGIDAS (auth:sanctum) 
// ————————————————————————————————————————————————————————————————————
Route::middleware(['auth:sanctum', 'verified'])->group(function () { // Hasta que se verifique el correo electrónico
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout'); //Sirve para cerrar sesión
    Route::get('/me', [AuthController::class, 'me'])->name('api.me'); //Sirve para obtener el usuario autenticado

    // — Usuarios (CRUD) — 
    // apiResource registrará index, store, show, update, destroy
    Route::apiResource('usuarios', UsuarioController::class);

    // — Roles (sólo listado para llenar selects, etc.) — 
    Route::get('/roles', [RolController::class, 'index'])->name('roles.index');

    // — Clientes (CRUD + upload logo) — 
    Route::apiResource('clientes', ClienteController::class);
    Route::post('clientes/{cliente}/logo', [ClienteController::class, 'subirLogo'])->name('clientes.subirLogo');

    // — Reportes de encuestas —
    // (Sólo Admin/Cliente con auth puede verlos)
    Route::prefix('reportes/encuestas/{encuesta}')->name('reportes.encuestas.')->group(function () {
        // Respuestas detalladas (JSON)
        Route::get('respuestas-detalladas', [ReportesController::class, 'respuestasDetalladas'])->name('respuestasDetalladas');
        // Resumen agregado por pregunta (JSON)
        Route::get('resumen-por-pregunta', [ReportesController::class, 'resumenPorPregunta'])->name('resumenPorPregunta');
        // Exportar respuestas detalladas a CSV/Excel
        Route::get('exportar/respuestas-detalladas/csv', [ReportesController::class, 'exportarRespuestasDetalladasCsv'])->name('exportarRespuestasDetalladasCsv');
        // Exportar resumen por pregunta a CSV/Excel
        Route::get('exportar/resumen-por-pregunta/csv', [ReportesController::class, 'exportarResumenPreguntaCsv'])->name('exportarResumenPreguntaCsv');

        // Exportar respuestas detalladas a Excel
        // Route::get('exportar/respuestas-detalladas/excel', [ReportesController::class, 'exportarRespuestasDetalladasExcel'])->name('exportarRespuestasDetalladasExcel');
    });

    // — Rutas de “OPCIONES” (anidadas con shallow) — 
    //  Usamos apiResource anidado + shallow() para que show/update/destroy sean “/opciones/{opcion}”
    Route::apiResource('encuestas.secciones.preguntas.opciones', OpcionPreguntaController::class)->shallow();

    // Crear múltiples opciones en bloque
    Route::post('encuestas/{encuesta}/secciones/{seccionEncuesta}/preguntas/{pregunta}/opciones/bulk', [OpcionPreguntaController::class, 'storeBulk'])->name('preguntas.opciones.storeBulk');

    // Reordenar una única opción (end-point “shallow”)
    Route::post('opciones/{opcionPregunta}/reordenar', [OpcionPreguntaController::class, 'reordenar'])->name('preguntas.opciones.reordenar');

    // Tipos de pregunta
    Route::apiResource('tipos-pregunta', TipoPreguntaController::class);

    // Route::prefix('reportes/encuestas/{encuesta}')->name('reportes.encuestas.')->group(function () {
    //   Route::get('respuestas-detalladas', [ReportesController::class, 'respuestasDetalladas'])
    //     ->name('respuestasDetalladas');
    //   Route::get('exportar/resumen-por-pregunta/csv', [ReportesController::class, 'exportarResumenPorPreguntaCsv'])
    //     ->name('exportarResumenPorPreguntaCsv');
    // });
    // Reenviar enlace de verificación
    Route::post('/email/resend-verification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'El correo electrónico ya ha sido verificado.'], 200);
        }
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Se ha enviado un nuevo enlace de verificación.'], 200);
    })->name('api.verification.resend');
});

// ————————————————————————————————————————————————————————————————————
// 3) RUTAS “ENCUESTAS” (algunas públicas, otras protected)
// ————————————————————————————————————————————————————————————————————
Route::prefix('encuestas')->group(function () {
    // —– Endpoints públicos (no requieren auth) —–
    Route::get('publica/{idEncuesta}', [EncuestaController::class, 'publica'])->name('encuestas.publica');

    Route::get('publica/code/{codigoUrl}', [EncuestaController::class, 'publicaPorCodigo'])->name('encuestas.publicaPorCodigo');

    // —– Endpoints protegidos (auth:sanctum + políticas) —–
    Route::middleware('auth:sanctum')->group(function () {
        // Listar encuestas de un cliente (Admin)
        Route::get('por-cliente/{cliente}', [EncuestaController::class, 'porCliente'])->name('encuestas.porCliente');

        // Detalle completo (Admin + cliente dueño)
        Route::get('{encuesta}/detalle-completo', [EncuestaController::class, 'detalleCompleto'])->name('encuestas.detalleCompleto');

        // Generar URL cifrada (Admin + dueño)
        Route::post('{encuesta}/generar-url', [EncuestaController::class, 'generarUrlCifrada'])->name('encuestas.generarUrl');

        // — CRUD “básico” de encuestas — 
        // — Al usar scoped(), Laravel “resolverá” por id_encuesta o la clave que indiques en el modelo. 
        Route::apiResource('/', EncuestaController::class)
            ->parameters(['' => 'encuesta'])
            // Esto mapea “/encuestas/{encuesta}” a {encuesta}
            ->names([
                'index'   => 'encuestas.index',
                'store'   => 'encuestas.store',
                'show'    => 'encuestas.show',
                'update'  => 'encuestas.update',
                'destroy' => 'encuestas.destroy',
            ])
            ->scoped(); // “scoped()” permite bindear por columnas no estrictamente “id” si fuera necesario.

        // e) Anidar las rutas de SecciónEncuesta bajo cada {encuesta}
        Route::prefix('{encuesta}/secciones')->group(function () {
            // Listar todas las secciones de una encuesta
            Route::get('/', [SeccionEncuestaController::class, 'index'])->name('secciones.index');
            // Crear nueva sección para esa encuesta
            Route::post('/', [SeccionEncuestaController::class, 'store'])->name('secciones.store');
            // Mostrar una sección específica
            Route::get('{seccionEncuesta}', [SeccionEncuestaController::class, 'show'])->name('secciones.show');
            // Actualizar una sección
            Route::put('{seccionEncuesta}', [SeccionEncuestaController::class, 'update'])->name('secciones.update');
            // Eliminar (soft delete) una sección
            Route::delete('{seccionEncuesta}', [SeccionEncuestaController::class, 'destroy'])->name('secciones.destroy');
            // Reordenar la sección a una nueva posición
            // (por ejemplo: POST /encuestas/{encuesta}/secciones/{seccionEncuesta}/reordenar/3)
            Route::post('{seccionEncuesta}/reordenar/{nuevoOrden}', [SeccionEncuestaController::class, 'reordenar'])->name('secciones.reordenar');

            // — RUTAS ANIDADAS “PREGUNTAS” bajo cada {seccionEncuesta} — 
            Route::prefix('{seccionEncuesta}/preguntas')->group(function () {
                Route::get('/', [PreguntaController::class, 'index'])->name('preguntas.index');
                Route::post('/', [PreguntaController::class, 'store'])->name('preguntas.store');
                Route::get('{pregunta}', [PreguntaController::class, 'show'])->name('preguntas.show');
                Route::put('{pregunta}', [PreguntaController::class, 'update'])->name('preguntas.update');
                Route::delete('{pregunta}', [PreguntaController::class, 'destroy'])->name('preguntas.destroy');
                Route::post('{pregunta}/reordenar', [PreguntaController::class, 'reordenar'])->name('preguntas.reordenar');
            });
        });
    });

    // —– Respuestas a una encuesta (público para “store”; index requiere auth) —–
    // 1) Cualquiera puede enviar respuestas a “/api/encuestas/{encuesta}/respuestas”
    Route::post('{encuesta}/respuestas', [RespuestasController::class, 'store'])->name('respuestas.store');

    // 2) Sólo Admin/Cliente (auth:sanctum) puede ver el listado de respuestas
    Route::get('{encuesta}/respuestas', [RespuestasController::class, 'index'])->middleware('auth:sanctum')->name('respuestas.index');

    // 3) Para Admin/Cliente → obtener detalle de una encuesta_respondida:
    Route::get('{encuesta}/respuestas/{respondida}', [RespuestasController::class, 'showDetalle'])->middleware('auth:sanctum')->name('respuestas.showDetalle');

    // —– (Opcional) Reporte público/privado extra que no HAYA quedado en el grupo “reportes” de arriba —–
    // Ejemplo de endpoint extra que devuelva JSON/CSV sin usar el prefijo anterior:
    // Route::get(
    //    '{encuesta}/respuestas-detalladas-publico',
    //    [ReportesController::class, 'respuestasDetalladasPublico']
    // );
});

// ============================================================================
// 8) Endpoints públicos de “Cuestionario de paciente” (mobile‐first):
//    GET /api/cuestionarios/{encuesta}/{paciente_id?}
//    → Si el paciente_id no existe o no se envía, el cuestionario se mostrará 
//      con todos los valores en blanco (nuevo registro).
//    → Si existe paciente_id, intenta pre‐llenar con los valores que haya en SQL Server.
//    → Solo si es es_cuestionario=1 y esta_activa == true.
// ============================================================================

Route::get('cuestionarios/{encuesta}/{paciente_id?}', [EncuestaController::class, 'cuestionarioParaPaciente'])->name('cuestionarios.paraPaciente');

// ————————————————————————————————————————————————————————————————————
// 4) Endpoints públicos de “Respuestas” (para mostrar una respuesta específica)
// ————————————————————————————————————————————————————————————————————
Route::get('/api/encuestas/{encuesta}/respuestas/{respondida}', [RespuestasController::class, 'show'])->name('respuestas.show');
