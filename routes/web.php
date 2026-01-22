<?php

use Illuminate\Support\Facades\Route;

Route::get('/management/{any}', function () {
    return view('app');  // Your Vue SPA entry point
})->where('any', '.*');

Route::get('/', function() {
    return view('welcome');  // Laravel welcome page
});