<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\app\Models\CubeTable;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;

class MakeWebController extends BaseCommand
{
    protected CubeTable $tableObject;

    protected $description = 'Create a new web controller';

    protected $signature = 'create:web-controller
        {name : The name of the model }
        {attributes? : the model attributes}
        {relations? : the model relations}
        {nullables? : the nullables attributes}
        {actor? : The actor of the endpoint of this model }';

    protected string $rawColumns = "";

    protected array $additionalRoutes = [];

    public function handle(): void
    {
        $name = $this->argument('name');
        $actor = $this->argument('actor') ?? null;
        $attributes = $this->argument('attributes') ?? [];
        $relations = $this->argument('relations') ?? [];
        $nullables = $this->argument('nullables') ?? [];

        $generator = new GeneratorFactory("web-controller");
        $generator->make(fileName: $name, attributes: $attributes, relations: $relations, nullables: $nullables, actor: $actor);
        $this->handleCommandLogsAndErrors();
    }
}
