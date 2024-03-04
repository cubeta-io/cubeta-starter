<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeRepository extends BaseCommand
{

    public $description = 'Create a new repository class';

    public $signature = 'create:repository
        {name : The name of the repository }';


    public function handle(): void
    {
        $modelName = $this->argument('name');

        if (!$modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $generator = new GeneratorFactory("repository");
        $generator->make(fileName: $modelName);
        $this->handleCommandLogsAndErrors();
    }
}
