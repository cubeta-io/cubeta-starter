<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeController extends BaseCommand
{
    protected $description = 'Create a new api controller for a model';

    protected $signature = 'create:controller
        {name? : The name of the model, e.g. Post }
        {actor? : the actor allowed to use the generated endpoints, or "none" }
        {--force : overwrite the existing controller instead of skipping/prompting }';

    public function getHelp(): string
    {
        return <<<HELP
          Generates an api CRUD controller (index/store/show/update/destroy/export/import/get-import-example) for a model,
          wired to its repository/service and routes. Requires "api" tooling to be installed
          first via "php artisan cubeta:install api".

          Examples:
            php artisan create:controller Post
            php artisan create:controller Post none --force --no-interaction
          HELP;
    }

    public function handle(): void
    {
        $modelName = $this->argument("name") ?? $this->askForModelName("Controller");

        if (file_exists(base_path('app/Enums/RoleEnum.php')) && class_exists('\App\Enums\RoleEnum')) {
            if ($this->argument('actor')) {
                $actor = $this->argument('actor');
            } else {
                $actor = ($this->askForGeneratedFileActors("Controller") ?? null);
            }
        }

        $override = $this->askForOverride();

        $gen = new GeneratorFactory("controller");

        $gen->make(fileName: $modelName, actor: $actor ?? null, generatedFor: ContainerType::API , override: $override);
    }
}
