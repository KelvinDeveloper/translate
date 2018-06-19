<?php

namespace Translate\Providers;

use Illuminate\Support\ServiceProvider;

class TranslateProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        include __DIR__.'/../Http/routes.php';
        $this->app->make('Translate\Http\Controllers\TranslateController');
        $this->publishes([
            __DIR__ . '/../migrations' => $this->app->databasePath() . '/migrations'
        ], 'migrations');
        $this->commands([
            \Translate\Console\Translate::class
        ]);
        include __DIR__ . './../helpers/helpers.php';
    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/translate.php', 'translate');
    }
}