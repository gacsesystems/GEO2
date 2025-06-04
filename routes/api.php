<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;

// Index de clientes
Route::get('/clientes', [ClienteController::class, 'index']);
// Store de clientes
Route::post('/clientes', [ClienteController::class, 'store']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
