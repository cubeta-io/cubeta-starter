<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeRepository extends BaseCommand
{

    public $description = 'Create a new repository class';

    public $signature = 'create:repository
        {name? : The name of the model related to the created repository }
        {--force}';


    public function handle(): void
    {
        $modelName = $this->argument('name') ?? $this->askForModelName("Repository");

        $generator = new GeneratorFactory("repository");
        $override = $this->askForOverride();
        $generator->make(fileName: $modelName, override: $override);
    }
}
