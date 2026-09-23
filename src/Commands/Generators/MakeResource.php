<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeResource extends BaseCommand
{
    public $description = 'Create a new api resource for a model';

    public $signature = 'create:resource
        {name? : The name of the model, e.g. Post }
        {attributes? : model columns, format "field:type,field2:type2,..." }
        {relations? : model relations, format "relatedModel:relationType,..." }
        {container? : api, web or both }
        {--force : overwrite the existing resource instead of skipping/prompting }';

    public function getHelp(): string
    {
        return <<<HELP
          Generates an Eloquent API Resource class exposing the given model's attributes
          and relations.

          {$this->argumentFormatsHelp()}

          Examples:
            php artisan create:resource Post
            php artisan create:resource Post "title:string,body:text" "comments:hasMany" api --force --no-interaction
          HELP;
    }

    public function handle(): void
    {
        $modelName = $this->argument('name') ?? $this->askForModelName("Model");
        $attributes = $this->argument('attributes') ?? null;

        if (!$attributes) {
            [$attributes, ,] = $this->askForModelAttributes(true);
        } else {
            $attributes = $this->resolveAttributes($attributes);
        }

        $relations = $this->argument('relations') ?? ($this->askForRelations($modelName) ?? []);
        $relations = $this->resolveRelations($relations);

        $container = $this->argument('container') ?? ContainerType::API;

        $override = $this->askForOverride();

        $generator = new GeneratorFactory("resource");
        $generator->make(fileName: $modelName, attributes: $attributes, relations: $relations, generatedFor: $container, override: $override);
    }
}
