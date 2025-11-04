<?php

namespace Obelaw\Obi;

use Illuminate\Support\ServiceProvider;
use Obelaw\Obi\Console\Commands\DeclarationBuildCommand;
use Obelaw\Obi\Console\Commands\DeclarationListCommand;
use Obelaw\Obi\DeclarationPool;

class ObiServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
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
                DeclarationListCommand::class,
                DeclarationBuildCommand::class,
            ]);
        }
    }
}