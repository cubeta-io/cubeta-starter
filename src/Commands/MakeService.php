<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeService extends BaseCommand
{
    public $description = 'Create a new service class';

    public $signature = 'create:service
        {name : The name of the service }';

    public function handle(): void
    {
        $modelName = $this->argument('name');


        if (!$modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $generator = new GeneratorFactory("service");
        $generator->make(fileName: $modelName);
        $this->handleCommandLogsAndErrors();
    }
}
