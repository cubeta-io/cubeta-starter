<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeRepository extends BaseCommand
{

    public $description = 'Create a new repository class for a model';

    public $signature = 'create:repository
        {name? : The name of the model related to the created repository, e.g. Post }
        {--force : overwrite the existing repository instead of skipping/prompting }';

    public function getHelp(): string
    {
        return <<<HELP
          Generates a Repository class (Repository pattern, paired with a Service) for the
          given model.

          Examples:
            php artisan create:repository Post
            php artisan create:repository Post --force --no-interaction
          HELP;
    }


    public function handle(): void
    {
        $modelName = $this->argument('name') ?? $this->askForModelName("Repository");

        $generator = new GeneratorFactory("repository");
        $override = $this->askForOverride();
        $generator->make(fileName: $modelName, override: $override);
    }
}
