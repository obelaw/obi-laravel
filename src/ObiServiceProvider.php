<?php

namespace Obelaw\Obi;

use Illuminate\Support\ServiceProvider;
use Obelaw\Obi\Console\Commands\DeclarationBuildCommand;
use Obelaw\Obi\Console\Commands\DeclarationListCommand;
use Obelaw\Obi\Console\Commands\DeclarationMakeCommand;
use Obelaw\Obi\DeclarationPool;
use Obelaw\Obi\Services\GeminiService;

class ObiServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('obi', GeminiService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Register the default declarations path
        DeclarationPool::addPath(base_path('declarations'));

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->commands([
                DeclarationMakeCommand::class,
                DeclarationListCommand::class,
                DeclarationBuildCommand::class,
            ]);
        }
    }
}