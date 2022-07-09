<?php

namespace {{ FULL_NAMESPACE }}\Console\Commands;

use Illuminate\Console\Command;

use {{ FULL_NAMESPACE }}\Services\{{ PACKAGE_NAMESPACE }}Service;

class ExampleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '{{ PACKAGE_SLUG }}:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public $myService;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->myService = new {{ PACKAGE_NAMESPACE }}Service();

        $this->askSomething();

        return 0;
    }

    private function askSomething()
    {
        $answer = $this->ask('Say something');

        if (!$answer || trim($answer) == '') {
            $this->askSomething();
        }

        $result = $this->myService->myServiceFunction($answer);

        $this->info("Result: {$result}");
    }
}
