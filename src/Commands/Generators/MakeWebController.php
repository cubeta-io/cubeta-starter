<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;
use Cubeta\CubetaStarter\Settings\CubeTable;

class MakeWebController extends BaseCommand
{
    protected CubeTable $tableObject;

    protected $description = 'Create a new web (blade or Inertia/React) controller for a model';

    protected $signature = 'create:web-controller
        {name? : The name of the model, e.g. Post }
        {attributes? : model columns, format "field:type,field2:type2,..." }
        {relations? : model relations, format "relatedModel:relationType,..." }
        {nullables? : nullable columns, format "field,field2,..." }
        {actor? : the actor allowed to use the generated routes, or "none" }
        {--force : overwrite the existing controller instead of skipping/prompting }';

    public function getHelp(): string
    {
        return <<<HELP
          Generates a web controller (and its views/pages) for a model, targeting whichever
          frontend stack is installed (blade or Inertia/React). Requires "web" tooling to be
          installed first via "php artisan cubeta:install web" (or "react-ts").

          {$this->argumentFormatsHelp()}

          Examples:
            php artisan create:web-controller Post
            php artisan create:web-controller Post "title:string,body:text" "" "slug" none --force --no-interaction
          HELP;
    }

    protected string $rawColumns = "";

    protected array $additionalRoutes = [];

    public function handle(): void
    {
        $modelName = $this->argument('name') ?? $this->askForModelName("Model");
        $attributes = $this->argument('attributes') ?? null;

        if (!$attributes) {
            [$attributes, , $nullables] = $this->askForModelAttributes(false, true);
        } else {
            $attributes = $this->resolveAttributes($attributes);
        }

        $relations = $this->argument('relations') ?? ($this->askForRelations($modelName) ?? []);
        $relations = $this->resolveRelations($relations);

        $nulls = $this->resolveList($this->argument("nullables") ?? ($nullables ?? []));

        $actor = $this->argument('actor') ?? ($this->askForGeneratedFileActors("Model"));

        $override = $this->askForOverride();

        $generator = new GeneratorFactory("controller");
        $generator->make(
            fileName: $modelName,
            attributes: $attributes,
            relations: $relations,
            nullables: $nulls,
            actor: $actor,
            generatedFor: ContainerType::WEB,
            override: $override
        );
    }
}
