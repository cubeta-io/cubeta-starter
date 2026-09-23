<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeSeeder extends BaseCommand
{

    public $description = 'Create a new seeder for a model';

    public $signature = 'create:seeder
        {name? : The name of the model, e.g. Post }
        {--force : overwrite the existing seeder instead of skipping/prompting }';

    public function getHelp(): string
    {
        return <<<HELP
          Generates a Seeder class that uses the model's factory to seed the database.

          Examples:
            php artisan create:seeder Post
            php artisan create:seeder Post --force --no-interaction
          HELP;
    }

    public function handle(): void
    {
        $modelName =  $this->argument('name') ?? $this->askForModelName("Model");;
        $override = $this->askForOverride();

        $generator = new GeneratorFactory("seeder");
        $generator->make(fileName: $modelName, override: $override);
    }
}
