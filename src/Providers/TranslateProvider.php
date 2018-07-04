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
        $this->app->register(\Dedicated\GoogleTranslate\GoogleTranslateProvider::class);
        $this->app->register(\Aws\Laravel\AwsServiceProvider::class);
        $this->publishes([
            __DIR__ . '/../migrations' => $this->app->databasePath() . '/migrations'
        ], 'migrations');
        $this->publishes([
            __DIR__ . '/../views' => $this->app->basePath() . '/resources/views'
        ], 'views');
        $this->publishes([
            __DIR__ . '/../config' => $this->app->basePath() . '/config'
        ], 'config');
        $this->commands([
            \Translate\Console\TranslateAuto::class,
            \Translate\Console\TranslateSync::class,
            \Translate\Console\TranslateUpdate::class,
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
        $this->mergeConfigFrom(__DIR__ . '/../config/google-translate.php', 'google-translate');
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('AWS', 'Aws\Laravel\AwsFacade');
    }
}