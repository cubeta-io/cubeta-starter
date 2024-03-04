<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeFactory extends BaseCommand
{
    public $description = 'Create a new factory';

    public $signature = 'create:factory
        {name       : The name of the model }
        {attributes? : columns with data types}
        {relations?  : the model relations}
        {uniques? : unique columns}';

    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes') ?? [];
        $relations = $this->argument('relations') ?? [];
        $uniques = $this->argument('uniques') ?? [];

        if (!$modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $generator = new GeneratorFactory("factory");

        $generator->make(fileName: $modelName, attributes: $attributes, relations: $relations, uniques: $uniques);

        $this->handleCommandLogsAndErrors();
    }
}
