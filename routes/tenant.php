<?php

declare (strict_types = 1);

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

use App\Livewire\Counter;
use App\Livewire\Home;
use App\Livewire\VehicleLocationTracker;
use App\Livewire\Vehiclesmap;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        $users = User::get();
        dd($users->toArray());
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });
    Route::get('/login', function(){
        return view('auth.login');
    })->name('login');
    Route::get('/home', Vehiclesmap::class)->name('gpsmap');
    Route::get('/counter', Counter::class)->name('counter');
    Route::get('/vehicle', VehicleLocationTracker::class);
});
