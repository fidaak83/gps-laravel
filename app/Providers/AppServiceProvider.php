<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Utils\Codec8Parser;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind(Codec8Parser::class, function ($app) {
            return new Codec8Parser();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
