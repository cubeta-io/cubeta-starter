<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeController extends BaseCommand
{
    protected $description = 'Create a new controller';

    protected $signature = 'create:controller
        {name? : The name of the model }
        {actor? : The actor of the endpoint of this model }';

    public function handle(): void
    {
        $modelName = $this->argument("name") ?? $this->askForModelName("Controller");

        if (file_exists(base_path('app/Enums/RolesPermissionEnum.php')) && class_exists('\App\Enums\RolesPermissionEnum')) {
            /** @noinspection PhpFullyQualifiedNameUsageInspection */
            if ($this->argument('actor')) {
                $actor = $this->argument('actor');
            } else {
                $actor = ($this->askForGeneratedFileActors("Controller") ?? null);
            }
        }

        $gen = new GeneratorFactory("controller");

        $gen->make(fileName: $modelName, actor: $actor ?? null, generatedFor: ContainerType::API);

        $this->handleCommandLogsAndErrors();
    }
}
