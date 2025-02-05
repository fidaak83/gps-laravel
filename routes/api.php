<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Codec8Controller;
use App\Http\Controllers\GpsData;

Route::get('/test',  [GpsData::class, 'store']);

Route::post('/test',  [GpsData::class, 'store']);

