<?php

namespace {{ FULL_NAMESPACE }};

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

use {{ FULL_NAMESPACE }}\Services\{{ PACKAGE_NAMESPACE }}Service;{{ CON_SETUP_CONSOLE_START }}
use {{ FULL_NAMESPACE }}\Console\Commands\ExampleCommand;{{ CON_SETUP_CONSOLE_END }}

final class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap of package services
     *
     * @return void
     */
    public function boot(Router $router): void
    {
        $this->bootPublishes();
{{ CON_SETUP_CONSOLE_START }}
        $this->loadCommands();
{{ CON_SETUP_CONSOLE_END }}{{ CON_SETUP_ROUTES_START }}
        $this->loadRoutes();{{ CON_SETUP_ROUTES_END }}
    }

    /**
     * Register any application services
     *
     * @return void
     */
    public function register(): void
    {{{ CON_SETUP_CONFIG_START }}
        // package config file
        $this->mergeConfigFrom(__DIR__ . '/../config/{{ PACKAGE_SLUG }}.php', '{{ PACKAGE_SLUG }}');
{{ CON_SETUP_CONFIG_END }}
    }

    /**
     * Publishes resources on boot
     *
     * @return void
     */
    private function bootPublishes(): void
    {{{ CON_SETUP_CONFIG_START }}
        // package configs
        $this->publishes([
            __DIR__ . '/../config/{{ PACKAGE_SLUG }}.php' => $this->app->configPath('{{ PACKAGE_SLUG }}.php'),
        ], '{{ PACKAGE_SLUG }}-config');
{{ CON_SETUP_CONFIG_END }}{{ CON_SETUP_DATABASE_START }}
        // migrations
        $migrationsPath = __DIR__ . '/../database/migrations/';

        $this->publishes([
            $migrationsPath => database_path('migrations/{{ VENDOR_SLUG }}/{{ PACKAGE_SLUG }}')
        ], '{{ PACKAGE_SLUG }}-migrations');

        $this->loadMigrationsFrom($migrationsPath);{{ CON_SETUP_DATABASE_END }}
    }
{{ CON_SETUP_CONSOLE_START }}
    /**
     * Load package commands
     *
     * @return void
     */
    private function loadCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ExampleCommand::class,
            ]);
        }
    }
{{ CON_SETUP_CONSOLE_END }}{{ CON_SETUP_ROUTES_START }}
    /**
     * Load pacakge-specific routes
     *
     * @return void
     */
    private function loadRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }{{ CON_SETUP_ROUTES_END }}
}
