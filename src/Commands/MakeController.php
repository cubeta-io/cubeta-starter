<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeController extends BaseCommand
{
    protected $description = 'Create a new controller';

    protected $signature = 'create:controller
        {name : The name of the model }
        {actor? : The actor of the endpoint of this model }';


    public function handle(): void
    {
        $modelName = $this->argument('name') ?? null;
        $actor = $this->argument('actor') ?? null;

        $gen = new GeneratorFactory("api-controller");

        $gen->make(fileName: $modelName, actor: $actor);

        $this->handleCommandLogsAndErrors();
    }
}
