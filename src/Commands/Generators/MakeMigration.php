<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeMigration extends BaseCommand
{
    public $description = 'Create a new migration for a model';

    public $signature = 'create:migration
        {name? : The name of the model, e.g. Post }
        {attributes? : model columns, format "field:type,field2:type2,..." }
        {relations? : model relations, format "relatedModel:relationType,..." }
        {nullables? : nullable columns, format "field,field2,..." }
        {uniques? : unique columns, format "field,field2,..." }
        {--force : overwrite the existing migration instead of skipping/prompting }';

    public function getHelp(): string
    {
        return <<<HELP
          Generates a migration file for a model's table.

          {$this->argumentFormatsHelp()}

          Examples:
            php artisan create:migration Post
            php artisan create:migration Post "title:string,body:text,category_id:key" "comments:hasMany" "" "slug" --force --no-interaction
          HELP;
    }

    public function handle(): void
    {
        $modelName = $this->argument('name') ?? $this->askForModelName("Migration");
        $attributes = $this->argument('attributes') ?? null;

        if (!$attributes) {
            [$attributes, $uniques, $nullables] = $this->askForModelAttributes(true, true);
        } else {
            $attributes = $this->resolveAttributes($attributes);
        }

        $relations = $this->argument('relations') ?? ($this->askForRelations($modelName) ?? []);
        $relations = $this->resolveRelations($relations);

        $unique = $this->resolveList($this->argument('uniques') ?? ($uniques ?? []));

        $nulls = $this->resolveList($this->argument("nullables") ?? ($nullables ?? []));

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
