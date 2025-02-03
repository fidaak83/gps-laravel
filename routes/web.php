<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Codec8Controller;
use App\Livewire\Counter;
use App\Livewire\Home;


Route::get('/', function () {
    return view('welcome');
});

 
Route::get('/counter', Counter::class);
Route::get('/home', Home::class);


Route::post('/parse', [Codec8Controller::class, 'parse']);