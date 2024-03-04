<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeResource extends BaseCommand
{
    public $description = 'Create a new resource';

    public $signature = 'create:resource
        {name : The name of the model }
        {attributes? : columns with data types}
        {relations? : the model relations}';

    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes') ?? [];
        $relations = $this->argument('relations') ?? [];

        if (!$modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $generator = new GeneratorFactory("resource");
        $generator->make(fileName: $modelName, attributes: $attributes, relations: $relations);
        $this->handleCommandLogsAndErrors();
    }
}
