<?php

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Route;

Route::get('/management/{any}', function () {
    return view('app');  // Your Vue SPA entry point
})->where('any', '.*');

Route::get('/', function() {
    return response()->json(['message' => 'Go to ' .  config('app.url') . '/management/login to access the dashboard.']);
});