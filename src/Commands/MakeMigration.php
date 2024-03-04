<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeMigration extends BaseCommand
{
    public $description = 'Create a new migration';

    public $signature = 'create:migration
        {name : The name of the model }
        {attributes? : columns with data types}
        {relations?  : related models}
        {nullables? : nullable columns}
        {uniques? : uniques columns}';

    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes') ?? [];
        $relations = $this->argument('relations') ?? [];
        $nullables = $this->argument("nullables") ?? [];
        $uniques = $this->argument('uniques') ?? [];

        $generator = new GeneratorFactory("migration");
        $generator->make(fileName: $modelName, attributes: $attributes, relations: $relations, nullables: $nullables, uniques: $uniques);
        $this->handleCommandLogsAndErrors();
    }
}
