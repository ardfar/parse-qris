<?php

namespace Ardfar\ParseQris;

use Illuminate\Support\ServiceProvider;

class QrisServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('qris-parser', function () {
            return new QrisParser();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish configuration if needed in the future
        // $this->publishes([
        //     __DIR__.'/../config/qris.php' => config_path('qris.php'),
        // ], 'config');
    }
}