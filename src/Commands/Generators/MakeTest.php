<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;
use Cubeta\CubetaStarter\Traits\RouteBinding;

class MakeTest extends BaseCommand
{
    use RouteBinding;

    public $description = 'Create a new api feature test for a model';

    public $signature = 'create:test
        {name? : The name of the model, e.g. Post }
        {attributes? : model columns, format "field:type,field2:type2,..." }
        {actor? : the actor allowed to use the tested endpoints, or "none" }
        {--force : overwrite the existing test instead of skipping/prompting }';

    public function getHelp(): string
    {
        return <<<HELP
          Generates a Feature test exercising the CRUD api endpoints for a model.

          {$this->argumentFormatsHelp()}

          Examples:
            php artisan create:test Post
            php artisan create:test Post "title:string,body:text" none --force --no-interaction
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

        $actor = $this->argument('actor') ?? ($this->askForGeneratedFileActors("Model"));

        $generator = new GeneratorFactory("test");
        $override = $this->askForOverride();
        $generator->make(fileName: $modelName, attributes: $attributes, actor: $actor , override: $override);
    }
}
