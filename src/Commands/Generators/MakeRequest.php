<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeRequest extends BaseCommand
{

    public $description = 'Create a new request';

    public $signature = 'create:request
        {name? : The name of the model }
        {attributes? : columns with data types}
        {nullables? : nullable columns}
        {uniques? : uniques columns}
        {container? : web or api}';

    public function handle(): void
    {
        $modelName = $this->argument('name') ?? $this->askForModelName("Model");
        $attributes = $this->argument('attributes') ?? null;
        $container = $this->argument('container') ?? ($this->askForContainer() ?? ContainerType::API);

        if (!$attributes) {
            [$attributes, $uniques, $nullables] = $this->askForModelAttributes(true, true);
        }

        $unique = $this->argument('uniques') ?? ($uniques ?? []);

        $nulls = $this->argument("nullables") ?? ($nullables ?? []);

        $generator = new GeneratorFactory("request");
        $generator->make(
            fileName: $modelName,
            attributes: $attributes,
            nullables: $nulls,
            uniques: $unique,
            generatedFor: $container
        );
        $this->handleCommandLogsAndErrors();
    }
}
