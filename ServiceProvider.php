<?php

namespace LaravelReady\Packager;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use LaravelReady\Packager\Services\PackagerService;
use LaravelReady\Packager\Console\Commands\NewCommand;
use LaravelReady\Packager\Console\Commands\ListPackagesCommand;
use LaravelReady\Packager\Console\Commands\MakeCommand;
use LaravelReady\Packager\Console\Commands\TouchCommand;
use LaravelReady\Packager\Console\Commands\TessssCommand;

final class PackagerServiceProvider extends BaseServiceProvider
{
    public function boot(Router $router) : void
    {
        $this->bootPublishes();
        $this->loadCommands();
    }
    public function register() : void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/packager.php', 'packager');
        // register package service
        $this->app->singleton('packager', function () {
            return new PackagerService();
        });
    }
    /**
     * Boot publishes
     */
    private function bootPublishes() : void
    {
        // package configs
        $this->publishes(paths: [__DIR__ . '/../config/packager.php' => $this->app->configPath('packager.php')], groups: 'packager-config');
    }
    /**
     * Load package commands
     */
    private function loadCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([NewCommand::class, MakeCommand::class, ListPackagesCommand::class, TouchCommand::class]);
        }
    }
}
