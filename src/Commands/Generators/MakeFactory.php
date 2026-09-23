<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeFactory extends BaseCommand
{
    public $description = 'Create a new model factory';

    public $signature = 'create:factory
        {name? : The name of the model, e.g. Post }
        {attributes? : model columns, format "field:type,field2:type2,..." }
        {relations? : model relations, format "relatedModel:relationType,..." }
        {uniques? : unique columns, format "field,field2,..." }
        {--force : overwrite the existing factory instead of skipping/prompting }';

    public function getHelp(): string
    {
        return <<<HELP
          Generates a model Factory producing fake data for the given attributes.

          {$this->argumentFormatsHelp()}

          Examples:
            php artisan create:factory Post
            php artisan create:factory Post "title:string,body:text" "comments:hasMany" "slug" --force --no-interaction
          HELP;
    }

    public function handle(): void
    {
        $modelName = $this->argument('name') ?? $this->askForModelName("Factory");
        $attributes = $this->argument('attributes') ?? null;

        if (!$attributes) {
            [$attributes, $uniques] = $this->askForModelAttributes(true);
        } else {
            $attributes = $this->resolveAttributes($attributes);
        }

        $relations = $this->argument('relations') ?? ($this->askForRelations($modelName) ?? []);
        $relations = $this->resolveRelations($relations);
        $uniques = $this->resolveList($this->argument('uniques') ?? ($uniques ?? []));

        $generator = new GeneratorFactory("factory");
        $override = $this->askForOverride();

        $generator->make(fileName: $modelName, attributes: $attributes, relations: $relations, uniques: $uniques, override: $override);
    }
}
