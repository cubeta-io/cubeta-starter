<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;
use Cubeta\CubetaStarter\Traits\RouteBinding;

class MakeTest extends BaseCommand
{
    use RouteBinding;

    public $description = 'Create a new feature test';

    public $signature = 'create:test
        {name? : The name of the model }
        {attributes? : model attributes}
        {actor? : The actor of the endpoint }
        {--force}';

    public function handle(): void
    {
        $modelName = $this->argument('name') ?? $this->askForModelName("Model");
        $attributes = $this->argument('attributes') ?? null;

        if (!$attributes) {
            [$attributes, ,] = $this->askForModelAttributes(true);
        }

        $actor = $this->argument('actor') ?? ($this->askForGeneratedFileActors("Model"));

        $generator = new GeneratorFactory("test");
        $override = $this->askForOverride();
        $generator->make(fileName: $modelName, attributes: $attributes, actor: $actor , override: $override);
    }
}
