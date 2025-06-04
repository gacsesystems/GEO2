<?php

use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return view('welcome'); // O el nombre de tu vista principal de React
})->where('any', '.*');
