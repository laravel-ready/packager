<?php

namespace LaravelReady\Packager\Supports;

use Illuminate\View\Factory;
use Illuminate\Events\Dispatcher;
use Illuminate\View\FileViewFinder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Compilers\BladeCompiler;
use LaravelReady\Packager\Exceptions\FileNotFoundException;

class BladeSupport
{
    private Filesystem $file;

    public function __construct()
    {
        $this->file = new Filesystem();
    }

    /**
     * Render template
     *
     * @param string $templatePath
     * @param array $data
     *
     * @return string|null
     * @throws \LaravelReady\Packager\Exceptions\FileNotFoundException
     */
    public function renderTemplate(string $templatePath, array $data = []): string
    {
        if (!$this->file->exists($templatePath)) {
            throw new FileNotFoundException("Template file not found: {$templatePath}");
        }

        $engineResolver = new EngineResolver();
        $bladeCompiler = new BladeCompiler(new Filesystem(), __DIR__ . '/../../cache');
        $engineResolver->register('blade', function () use ($bladeCompiler) {
            return new CompilerEngine($bladeCompiler);
        });

        $engineResolver->register('php', function () {
            return new PhpEngine();
        });

        $eventDispatcher = new Dispatcher();

        $viewFactory = new Factory(
            $engineResolver,
            new FileViewFinder(
                new Filesystem(),
                ['resources/views']
            ),
            $eventDispatcher
        );

        $view = $viewFactory->file($templatePath, $data);

        return $view->render();
    }
}
