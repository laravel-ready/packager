@php
    echo '<?php'
@endphp

namespace {{ $FULL_NAMESPACE }};

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

@if($SETUP_CONSOLE)
use {{ $FULL_NAMESPACE }}\Console\Commands\ExampleCommand;@endif

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
        @if($SETUP_CONSOLE)
        $this->loadCommands();
        @endif @if($SETUP_ROUTES)
        $this->loadRoutes();@endif
    }

    /**
     * Register any application services
     *
     * @return void
     */
    public function register(): void
    {@if ($SETUP_CONFIG)
        // package config file
        $this->mergeConfigFrom(__DIR__ . '/../config/{{ $PACKAGE_SLUG }}.php', '{{ $PACKAGE_SLUG }}');
@endif
    }

    /**
     * Publishes resources on boot
     *
     * @return void
     */
    private function bootPublishes(): void
    {@if ($SETUP_CONFIG)
        // package configs
        $this->publishes([
            __DIR__ . '/../config/{{ $PACKAGE_SLUG }}.php' => $this->app->configPath('{{ $PACKAGE_SLUG }}.php'),
        ], '{{ $PACKAGE_SLUG }}-config');
@endif @if ($SETUP_DATABASE)
        // migrations
        $migrationsPath = __DIR__ . '/../database/migrations/';

        $this->publishes([
            $migrationsPath => database_path('migrations/{{ $VENDOR_SLUG }}/{{ $PACKAGE_SLUG }}')
        ], '{{ $PACKAGE_SLUG }}-migrations');

        $this->loadMigrationsFrom($migrationsPath);@endif
    }
@if($SETUP_CONSOLE)
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
@endif
@if($SETUP_ROUTES)
    /**
     * Load pacakge-specific routes
     *
     * @return void
     */
    private function loadRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }@endif

}
