<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeService extends BaseCommand
{
    public $description = 'Create a new service class';

    public $signature = 'create:service
        {name? : The name of the service }';

    public function handle(): void
    {
        $modelName =  $this->argument('name') ?? $this->askForModelName("Model");

        $generator = new GeneratorFactory("service");
        $generator->make(fileName: $modelName);
        $this->handleCommandLogsAndErrors();
    }
}
