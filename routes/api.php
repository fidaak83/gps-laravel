<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Codec8Controller;


Route::get('/test', function () {
    return response()->json(['message' => 'API is working in Laravel 11+']);
});

Route::post('/parse', [Codec8Controller::class, 'parse']);

