<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aquí van las rutas que normalmente usan sesión/cookies en el servidor
| (por ej. páginas Blade o la verificación de e‐mail en un SPA).
|
*/

Route::get('/{any}', function () {
    return view('welcome'); // O el nombre de tu vista principal de React
})->where('any', '.*');

/*
|--------------------------------------------------------------------------
| RUTAS DE VERIFICACIÓN DE EMAIL (MustVerifyEmail)
|--------------------------------------------------------------------------
|
| 1) La ruta “verification.verify” acepta un {id}/{hash} firmado
|    y, si el usuario está autenticado y firma es válida, marca el email
|    como verificado y redirige al front-end (SPA) vía URL en config('app.frontend_url').
|
| 2) La ruta “verification.send” permite reenvío de correo de verificación.
|
*/

// 1) Verificar usuario: GET /email/verify/{id}/{hash}
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    // EmailVerificationRequest ya comprueba:
    //   • que el usuario logueado coincida con {id}
    //   • que el {hash} = sha1($user->email)
    //   • la firma sea válida y no haya expirado (middleware 'signed')

    if ($request->user()->hasVerifiedEmail()) {
        // Si ya está verificado, redirige al SPA con un mensaje
        return redirect(config('app.frontend_url') . '/email-already-verified');
    }

    $request->fulfill(); // Marca email_verified_at + lanza evento Verified
    return redirect(config('app.frontend_url') . '/email-verificado-exitosamente');
})->middleware(['auth:sanctum', 'signed', 'throttle:6,1'])->name('verification.verify');


// 2) Reenviar enlace de verificación: POST /email/verification-notification
Route::post('/email/verification-notification', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return response()->json(['message' => 'El correo ya está verificado.'], 200);
    }

    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Se ha enviado un nuevo enlace de verificación.'], 200);
})->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');
