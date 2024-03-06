<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeRepository extends BaseCommand
{

    public $description = 'Create a new repository class';

    public $signature = 'create:repository
        {name? : The name of the model related to the created repository }';


    public function handle(): void
    {
        $modelName = $this->argument('name') ?? $this->askForModelName("Repository");

        $generator = new GeneratorFactory("repository");
        $generator->make(fileName: $modelName);
        $this->handleCommandLogsAndErrors();
    }
}
