<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeRequest extends BaseCommand
{

    public $description = 'Create a new request';

    public $signature = 'create:request
        {name? : The name of the model }
        {attributes? : columns with data types}
        {nullables? : nullable columns}
        {uniques? : uniques columns}';

    public function handle(): void
    {
        $modelName = $this->argument('name') ?? $this->askForModelName("Model");
        $attributes = $this->argument('attributes') ?? null;

        if (!$attributes) {
            [$attributes, $uniques, $nullables] = $this->askForModelAttributes(true , true);
        }

        $unique = $this->argument('uniques') ?? ($uniques ?? []);

        $nulls = $this->argument("nullables") ?? ($nullables ?? []);

        $generator = new GeneratorFactory("request");
        $generator->make(fileName: $modelName, attributes: $attributes, nullables: $nulls, uniques: $unique);
        $this->handleCommandLogsAndErrors();
    }
}
