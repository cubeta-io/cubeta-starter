<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeService extends BaseCommand
{
    public $description = 'Create a new service class for a model';

    public $signature = 'create:service
        {name? : The name of the model related to the created service, e.g. Post }
        {--force : overwrite the existing service instead of skipping/prompting }';

    public function getHelp(): string
    {
        return <<<HELP
          Generates a Service class (Repository pattern, paired with a Repository) for the
          given model.

          Examples:
            php artisan create:service Post
            php artisan create:service Post --force --no-interaction
          HELP;
    }

    public function handle(): void
    {
        $modelName = $this->argument('name') ?? $this->askForModelName("Model");
        $override = $this->askForOverride();

        $generator = new GeneratorFactory("service");
        $generator->make(fileName: $modelName, override: $override);
    }
}
