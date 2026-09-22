<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Enums\ContainerType;
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
        } else {
            $attributes = $this->resolveAttributes($attributes);
        }

        $relations = $this->argument('relations') ?? ($this->askForRelations($modelName) ?? []);
        $relations = $this->resolveRelations($relations);

        $container = $this->argument('container') ?? ContainerType::API;

        $override = $this->askForOverride();

        $generator = new GeneratorFactory("resource");
        $generator->make(fileName: $modelName, attributes: $attributes, relations: $relations, generatedFor: $container, override: $override);
    }
}
