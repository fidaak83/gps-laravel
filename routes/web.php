<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Codec8Controller;
use App\Livewire\Counter;
use App\Livewire\Home;
use App\Livewire\Vehiclesmap;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', Vehiclesmap::class);


 
Route::get('/counter', Counter::class);
Route::get('/home2', Home::class);


Route::post('/parse', [Codec8Controller::class, 'parse']);