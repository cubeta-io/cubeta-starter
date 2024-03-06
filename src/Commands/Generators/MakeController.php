<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeController extends BaseCommand
{
    protected $description = 'Create a new controller';

    protected $signature = 'create:controller
        {name? : The name of the model }
        {actor? : The actor of the endpoint of this model }';

    public function handle(): void
    {
        $modelName = $this->argument("name") ?? $this->askForModelName("Controller");
        $actor = $this->argument('actor') ?? ($this->askForGeneratedFileActors("Controller") ?? null);

        $gen = new GeneratorFactory("api-controller");

        $gen->make(fileName: $modelName, actor: $actor);

        $this->handleCommandLogsAndErrors();
    }
}
