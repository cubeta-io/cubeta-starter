<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeResource extends BaseCommand
{
    public $description = 'Create a new resource';

    public $signature = 'create:resource
        {name? : The name of the model }
        {attributes? : columns with data types}
        {relations? : the model relations}
        {container? : web or api}
        {--force}';

    public function handle(): void
    {
        $modelName = $this->argument('name') ?? $this->askForModelName("Model");
        $attributes = $this->argument('attributes') ?? null;

        if (!$attributes) {
            [$attributes, ,] = $this->askForModelAttributes(true);
        }

        $relations = $this->argument('relations') ?? ($this->askForRelations($modelName) ?? []);

        $override = $this->askForOverride();

        $generator = new GeneratorFactory("resource");
        $generator->make(fileName: $modelName, attributes: $attributes, relations: $relations, override: $override);
    }
}
