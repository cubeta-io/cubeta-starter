<?php

namespace Cubeta\CubetaStarter\Commands\Generators;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;
use Cubeta\CubetaStarter\Settings\CubeTable;

class MakeWebController extends BaseCommand
{
    protected CubeTable $tableObject;

    protected $description = 'Create a new web controller';

    protected $signature = 'create:web-controller
        {name? : The name of the model }
        {attributes? : the model attributes}
        {relations? : the model relations}
        {nullables? : the nullables attributes}
        {actor? : The actor of the endpoint of this model }';

    protected string $rawColumns = "";

    protected array $additionalRoutes = [];

    public function handle(): void
    {
        $modelName = $this->argument('name') ?? $this->askForModelName("Model");
        $attributes = $this->argument('attributes') ?? null;

        if (!$attributes) {
            [$attributes, , $nullables] = $this->askForModelAttributes(false, true);
        }

        $relations = $this->argument('relations') ?? ($this->askForRelations($modelName) ?? []);

        $nulls = $this->argument("nullables") ?? ($nullables ?? []);

        $actor = $this->argument('actor') ?? ($this->askForGeneratedFileActors("Model"));

        $generator = new GeneratorFactory("controller");
        $generator->make(fileName: $modelName, attributes: $attributes, relations: $relations, nullables: $nulls, actor: $actor, generatedFor: ContainerType::WEB);
        $this->handleCommandLogsAndErrors();
    }
}
