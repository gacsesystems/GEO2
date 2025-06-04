<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\AuthController;

/** 
 *  Opcional: si permites que cualquiera se “autoregistre” vía API. 
 *  Si no usas este endpoint, bórralo. 
 */
Route::post('/register', [AuthController::class, 'register'])->name('api.register'); // opcional si quieres permitir self-registration

Route::post('/login', [AuthController::class, 'login'])->name('api.login'); //Sirve para iniciar sesión

Route::middleware(['auth:sanctum', 'verified'])->group(function () { // Hasta que se verifique el correo electrónico
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout'); //Sirve para cerrar sesión
    Route::get('/me', [AuthController::class, 'me'])->name('api.me'); //Sirve para obtener el usuario autenticado
});

// Index de clientes
Route::get('/clientes', [ClienteController::class, 'index']);
// Store de clientes
Route::post('/clientes', [ClienteController::class, 'store']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
