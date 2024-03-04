<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeRequest extends BaseCommand
{

    public $description = 'Create a new request';

    public $signature = 'create:request
        {name : The name of the model }
        {attributes? : columns with data types}
        {nullables? : nullable columns}
        {uniques? : uniques columns}';

    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes') ?? [];
        $nullables = $this->argument('nullables') ?? [];
        $uniques = $this->argument('uniques') ?? [];

        if (!$modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $generator = new GeneratorFactory("request");
        $generator->make(fileName: $modelName, attributes: $attributes, nullables: $nullables, uniques: $uniques);
        $this->handleCommandLogsAndErrors();
    }
}
