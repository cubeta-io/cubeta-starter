<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Generators\GeneratorFactory;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Traits\RouteBinding;

class MakeController extends BaseCommand
{
    use AssistCommand;
    use RouteBinding;

    protected $description = 'Create a new controller';

    protected $signature = 'create:controller
        {name : The name of the model }
        {actor? : The actor of the endpoint of this model }';


    public function handle(): void
    {
        $modelName = $this->argument('name') ?? null;
        $actor = $this->argument('actor') ?? null;

        $gen = GeneratorFactory::make("api-controller");

        $gen->run(fileName: $modelName, actor: $actor);

        $this->handleCommandLogsAndErrors($gen->logs);
    }
}
