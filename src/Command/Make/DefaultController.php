<?php

namespace LaravelReady\Packager\Command\Make;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use LaravelReady\Packager\Exceptions\ClassNameException;
use LaravelReady\Packager\Exceptions\StubException;
use LaravelReady\Packager\Services\PackagerService;
use LaravelReady\Packager\Supports\StrSupport;

use Minicli\Input;
use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    /**
     * @var PackagerService
     */
    private PackagerService $packagerService;

    /**
     * Command options
     */
    private array $makeCommandList = [
        'ct' => 'controller',
        'cm' => 'command',
        'mi' => 'migration',
        'mo' => 'model',
        're' => 'request',
        'se' => 'service',
        'mw' => 'middleware',
    ];

    private array $makeMigrationList = [
        'create', 'update', 'delete',
    ];

    /**
     * @throws ClassNameException
     * @throws FileNotFoundException
     * @throws StubException
     * @throws \Exception
     */
    public function handle(): void
    {
        $this->packagerService = new PackagerService();

        $command = $this->getMakeCommand();

        if ($command === null) {
            $this->getPrinter()->error(' Command not found. ', true);
            $this->getPrinter()->newline();

            $commandList = array_map(function ($key) {
                return "-{$key} | --{$this->makeCommandList[$key]}";
            }, array_keys($this->makeCommandList));

            $commandList = implode("\n", $commandList);

            $this->getPrinter()->out("Make commands list:", 'bold');
            $this->getPrinter()->newline();

            $this->getPrinter()->out($commandList, 'bold');
            $this->getPrinter()->newline();
            $this->getPrinter()->newline();

            return;
        }

        if (!empty($command['value'])) {
            $result = false;

            if ($command['name'] === 'migration') {
                $type = $this->hasParam('--type') && in_array($this->getParam('--type'), $this->makeMigrationList)
                    ? $this->getParam('--type')
                    : 'create';

                $result = $this->packagerService->makeMigration($command['value'], $type ?? 'create');
            } else {
                $result = $this->packagerService->make($command['name'], $command['value']);
            }

            if ($result === true) {
                $this->getPrinter()->success("Make {$command['name']}: \"{$command['value']}\" created successfully.");
            } else if ($result === false) {
                $this->getPrinter()->error("Make {$command['name']}: \"{$command['value']}\" failed. Please retry.");
            } else if ($result === null) {
                $this->getPrinter()->display("Make {$command['name']}: \"{$command['value']}\" already exists.");
            }
        }
    }

    private function getMakeCommand(): array|null
    {
        $command = null;

        $params = $this->getParams();
        $makeCommandKeys = array_keys($this->makeCommandList);
        $makeCommandValues = array_values($this->makeCommandList);

        foreach ($params as $key => $param) {
            if (Str::length($key) === 3) {
                $_key = explode('-', $key);

                if (in_array($_key[1], $makeCommandKeys)) {
                    $command = [
                        'name' => $this->makeCommandList[$_key[1]],
                        'value' => $param,
                    ];

                    break;
                }
            } else if (Str::startsWith($key, '--')) {
                $_key = Str::replace('-', '', $key);

                if (in_array($_key, $makeCommandValues)) {
                    $commandKey = $makeCommandKeys[array_search($_key, $makeCommandValues)];

                    $command = [
                        'name' => $this->makeCommandList[$commandKey],
                        'value' => $param,
                    ];

                    break;
                }
            }
        }

        return $command;
    }
}