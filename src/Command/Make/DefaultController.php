<?php

namespace LaravelReady\Packager\Command\Make;

use Illuminate\Support\Str;
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
    private $makeCommandList = [
        'ct' => 'controller',
        'cm' => 'command',
        'mi' => 'migration',
        'mo' => 'model',
        're' => 'request',
        'se' => 'service',
        'mw' => 'middleware',
    ];

    private $makeMigrationList = [
        'create', 'update', 'delete',
    ];

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

        if ($command['name'] === 'migration') {
            $type = $this->hasParam('--type') && in_array($this->getParam('--type'), $this->makeMigrationList)
                ? $this->getParam('--type')
                : 'create';

            $result = $this->packagerService->makeMigration($command['value'], $type);
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