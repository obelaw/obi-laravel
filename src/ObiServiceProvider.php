<?php

namespace Obelaw\Obi;

use Illuminate\Support\ServiceProvider;
use Obelaw\Obi\Console\Commands\BuildCommand;
use Obelaw\Obi\Console\Commands\ListCommand;
use Obelaw\Obi\Console\Commands\MakeCommand;
use Obelaw\Obi\Console\Commands\PromptCommand;
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
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/obi.php',
            'obi'
        );

        $this->app->singleton('obi', function ($app) {
            return new GeminiService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/obi.php' => config_path('obi.php'),
            ], 'obi-config');
        }

        // Register declaration pools from config
        $pools = config('obi.declaration_pools', [base_path('declarations')]);

        foreach ($pools as $pool) {
            DeclarationPool::addPath($pool);
        }

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->commands([
                ListCommand::class,
                BuildCommand::class,
                MakeCommand::class,
                PromptCommand::class,
            ]);
        }
    }
}
