<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Codec8Controller;

Route::get('/', function () {
    return view('welcome');
});


Route::post('/parse', [Codec8Controller::class, 'parse']);