<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeFactory extends BaseCommand
{
    public $description = 'Create a new factory';

    public $signature = 'create:factory
        {name?       : The name of the model }
        {attributes? : columns with data types}
        {relations?  : the model relations}
        {uniques? : unique columns} 
        {--force}';

    public function handle(): void
    {
        $modelName = $this->argument('name') ?? $this->askForModelName("Factory");
        $attributes = $this->argument('attributes') ?? null;

        if (!$attributes) {
            [$attributes, $uniques] = $this->askForModelAttributes(true);
        }

        $relations = $this->argument('relations') ?? ($this->askForRelations($modelName) ?? []);
        $uniques = $this->argument('uniques') ?? ($uniques ?? []);

        $generator = new GeneratorFactory("factory");
        $override = $this->askForOverride();

        $generator->make(fileName: $modelName, attributes: $attributes, relations: $relations, uniques: $uniques, override: $override);
    }
}
