<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeMigration extends BaseCommand
{
    public $description = 'Create a new migration';

    public $signature = 'create:migration
        {name? : The name of the model }
        {attributes? : columns with data types}
        {relations?  : related models}
        {nullables? : nullable columns}
        {uniques? : uniques columns}
        {--force}';

    public function handle(): void
    {
        $modelName = $this->argument('name') ?? $this->askForModelName("Migration");
        $attributes = $this->argument('attributes') ?? null;

        if (!$attributes) {
            [$attributes, $uniques, $nullables] = $this->askForModelAttributes(true, true);
        }

        $relations = $this->argument('relations') ?? ($this->askForRelations($modelName) ?? []);

        $unique = $this->argument('uniques') ?? ($uniques ?? []);

        $nulls = $this->argument("nullables") ?? ($nullables ?? []);

        $generator = new GeneratorFactory("migration");

        $override = $this->askForOverride();

        $generator->make(
            fileName: $modelName,
            attributes: $attributes,
            relations: $relations,
            nullables: $nulls,
            uniques: $unique,
            override: $override
        );
    }
}
